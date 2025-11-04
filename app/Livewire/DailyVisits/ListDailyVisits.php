<?php

namespace App\Livewire\DailyVisits;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Business;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Livewire\Component;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class ListDailyVisits extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        $user = Auth::user();
        $currentBranchId = optional($user->current_branch)->id;
        $allowedBranches = (array) ($user->allowed_branches ?? []);

        $query = Client::query()
            ->with(['business', 'branch'])
            ->latest();

        // Restrict by business
        if (Auth::check() && Auth::user()->business_id !== 1) {
            $query->where('business_id', Auth::user()->business_id);
            // Default branch constraint: user's current branch, or allowed branches fallback
            if (!empty($currentBranchId)) {
                $query->where('branch_id', $currentBranchId);
            } elseif (!empty($allowedBranches)) {
                $query->whereIn('branch_id', $allowedBranches);
            }
        } else {
            // Kashtre can see all
            $query->where('business_id', '!=', 1);
        }

        return $table
            ->query($query)
            ->columns([
                Tables\Columns\TextColumn::make('created_at_date')
                    ->label('Date')
                    ->getStateUsing(fn (Client $record) => $record->created_at)
                    ->dateTime('M d, Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at_time')
                    ->label('Time')
                    ->getStateUsing(fn (Client $record) => $record->created_at)
                    ->dateTime('H:i:s')
                    ->sortable(),

                Tables\Columns\TextColumn::make('client_id')
                    ->label('Client ID')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('full_name')
                    ->label('Client Name')
                    ->searchable(['surname', 'first_name', 'other_names'])
                    ->formatStateUsing(fn (Client $record): string => $record->full_name),

                Tables\Columns\TextColumn::make('phone_number')
                    ->label('Phone')
                    ->toggleable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('visit_id')
                    ->label('Visit ID')
                    ->searchable(),

                ...(Auth::check() && Auth::user()->business_id === 1 ? [
                    Tables\Columns\TextColumn::make('business.name')
                        ->label('Business')
                        ->sortable()
                        ->searchable(),
                    Tables\Columns\TextColumn::make('branch.name')
                        ->label('Branch')
                        ->sortable()
                        ->searchable(),
                ] : []),
            ])
            ->filters([
                ...(Auth::check() && Auth::user()->business_id === 1 ? [
                    Tables\Filters\SelectFilter::make('business_id')
                        ->label('Business')
                        ->options(Business::pluck('name', 'id')->all())
                        ->searchable(),
                ] : []),

                // Branch filter for non-Kashtre users
                ...(Auth::check() && Auth::user()->business_id !== 1 ? [
                    Tables\Filters\SelectFilter::make('branch_id')
                        ->label('Branch')
                        ->options(function () {
                            $user = Auth::user();
                            $allowed = (array) ($user->allowed_branches ?? []);
                            return \App\Models\Branch::whereIn('id', $allowed)->pluck('name', 'id')->all();
                        })
                        ->searchable(),
                ] : []),

                // Quick date presets (removable default)
                Tables\Filters\Filter::make('quick_date')
                    ->form([
                        \Filament\Forms\Components\Select::make('preset')
                            ->label('Quick Date')
                            ->options([
                                'today' => 'Today',
                                'yesterday' => 'Yesterday',
                                'this_week' => 'This Week',
                                'this_month' => 'This Month',
                            ])
                            ->placeholder('All dates')
                    ])
                    ->query(function ($query, array $data) {
                        // Default to today if no preset selected (but don't show as active filter badge)
                        if (empty($data['preset'])) {
                            return $query->whereDate('created_at', now()->toDateString());
                        }
                        return match ($data['preset']) {
                            'yesterday' => $query->whereDate('created_at', now()->subDay()->toDateString()),
                            'this_week' => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]),
                            'this_month' => $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]),
                            'today' => $query->whereDate('created_at', now()->toDateString()),
                            default => $query->whereDate('created_at', now()->toDateString()), // Default to today
                        };
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (empty($data['preset'])) {
                            return null; // Don't show indicator when using default (today)
                        }
                        return match ($data['preset']) {
                            'yesterday' => 'Date: Yesterday',
                            'this_week' => 'Date: This Week',
                            'this_month' => 'Date: This Month',
                            'today' => 'Date: Today',
                            default => null,
                        };
                    }),

                // Specific date filter (only applies when explicitly selected)
                Tables\Filters\Filter::make('date')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('created_at')
                            ->label('Date'),
                    ])
                    ->query(function ($query, array $data) {
                        if (!empty($data['created_at'])) {
                            $query->whereDate('created_at', $data['created_at']);
                        }
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (!empty($data['created_at'])) {
                            $selectedDate = \Carbon\Carbon::parse($data['created_at']);
                            $today = now()->toDateString();
                            // Only show if it's different from today (to avoid duplicate with quick_date)
                            if ($selectedDate->toDateString() !== $today) {
                                return 'Date: ' . $selectedDate->format('M d, Y');
                            }
                        }
                        return null;
                    }),

                // Date range filter
                Tables\Filters\Filter::make('date_range')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from')->label('From'),
                        \Filament\Forms\Components\DatePicker::make('to')->label('To'),
                    ])
                    ->query(function ($query, array $data) {
                        $from = $data['from'] ?? null;
                        $to = $data['to'] ?? null;
                        if ($from && $to) {
                            $query->whereBetween('created_at', [\Carbon\Carbon::parse($from)->startOfDay(), \Carbon\Carbon::parse($to)->endOfDay()]);
                        } elseif ($from) {
                            $query->where('created_at', '>=', \Carbon\Carbon::parse($from)->startOfDay());
                        } elseif ($to) {
                            $query->where('created_at', '<=', \Carbon\Carbon::parse($to)->endOfDay());
                        }
                    })
                    ->indicateUsing(function (array $data): ?string {
                        $from = $data['from'] ?? null;
                        $to = $data['to'] ?? null;
                        if ($from && $to) {
                            return 'Range: ' . \Carbon\Carbon::parse($from)->format('M d, Y') . ' → ' . \Carbon\Carbon::parse($to)->format('M d, Y');
                        }
                        if ($from) {
                            return 'From: ' . \Carbon\Carbon::parse($from)->format('M d, Y');
                        }
                        if ($to) {
                            return 'To: ' . \Carbon\Carbon::parse($to)->format('M d, Y');
                        }
                        return null;
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('details')
                    ->label('Details')
                    ->url(fn (Client $record): string => route('pos.item-selection', $record))
                    ->color('success'),
                Tables\Actions\Action::make('view')
                    ->label('View')
                    ->url(fn (Client $record): string => route('clients.show', $record))
                    ->color('primary'),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No visits found')
            ->emptyStateDescription('No client visits recorded for the selected date.')
            ->emptyStateIcon('heroicon-o-calendar');
    }

    public function render(): View
    {
        return view('livewire.daily-visits.list-daily-visits');
    }
}


