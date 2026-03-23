<?php

namespace App\Livewire\Visits;

use App\Models\VisitArchive;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ListVisitArchives extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public string $recordType = 'snapshot';
    public string $selectedDate;
    public int $selectedBranchId = 0;

    public function mount(string $recordType, string $selectedDate, int $selectedBranchId): void
    {
        $this->recordType = $recordType;
        $this->selectedDate = $selectedDate;
        $this->selectedBranchId = $selectedBranchId;
    }

    public function table(Table $table): Table
    {
        $user = Auth::user();

        $businessId = (int) ($user->business_id ?? 0);
        $allowedBranches = (array) ($user->allowed_branches ?? []);

        $query = VisitArchive::query()
            ->with(['business', 'branch'])
            ->where('record_type', $this->recordType)
            ;

        // Business scoping (mimic your Daily Visits behavior)
        if ($businessId === 1) {
            $query->where('business_id', '!=', 1);
        } else {
            $query->where('business_id', $businessId);

            // Branch filter: use the branch id passed from controller (from the request),
            // but still ensure it's within allowed branches when provided.
            if ($this->selectedBranchId) {
                $query->where('branch_id', $this->selectedBranchId);
            } elseif (!empty($allowedBranches)) {
                $query->whereIn('branch_id', $allowedBranches);
            }
        }

        $columns = [
            Tables\Columns\TextColumn::make('archived_at_date')
                ->label('Date')
                ->getStateUsing(fn (VisitArchive $record) => $record->archived_at)
                ->dateTime('M d, Y')
                ->sortable(),

            Tables\Columns\TextColumn::make('archived_at_time')
                ->label('Time')
                ->getStateUsing(fn (VisitArchive $record) => $record->archived_at)
                ->dateTime('H:i:s')
                ->sortable(),

            Tables\Columns\TextColumn::make('client_name')
                ->label('Client Name')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('client_age')
                ->label('Age')
                ->sortable(),

            Tables\Columns\TextColumn::make('visit_id')
                ->label('Visit ID')
                ->searchable()
                ->sortable(),
        ];

        if ($businessId === 1) {
            $columns = array_merge($columns, [
                Tables\Columns\TextColumn::make('business.name')
                    ->label('Business')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Branch')
                    ->sortable()
                    ->searchable(),
            ]);
        }

        return $table
            ->query($query->orderBy('archived_at', 'desc'))
            ->columns($columns)
            ->defaultSort('archived_at', 'desc')
            ->filters([
                // Quick date presets
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
                            ->placeholder('All dates'),
                    ])
                    ->query(function ($query, array $data) {
                        // Default to the date coming from controller (usually today) if no preset selected.
                        if (empty($data['preset'])) {
                            return $query->whereDate('archived_at', \Carbon\Carbon::parse($this->selectedDate)->toDateString());
                        }

                        return match ($data['preset']) {
                            'yesterday' => $query->whereDate('archived_at', now()->subDay()->toDateString()),
                            'this_week' => $query->whereBetween('archived_at', [now()->startOfWeek(), now()->endOfWeek()]),
                            'this_month' => $query->whereBetween('archived_at', [now()->startOfMonth(), now()->endOfMonth()]),
                            'today' => $query->whereDate('archived_at', now()->toDateString()),
                            default => $query->whereDate('archived_at', \Carbon\Carbon::parse($this->selectedDate)->toDateString()),
                        };
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (empty($data['preset'])) {
                            return null;
                        }
                        return match ($data['preset']) {
                            'yesterday' => 'Date: Yesterday',
                            'this_week' => 'Date: This Week',
                            'this_month' => 'Date: This Month',
                            'today' => 'Date: Today',
                            default => null,
                        };
                    }),

                // Specific date filter
                Tables\Filters\Filter::make('date')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('archived_date')
                            ->label('Date'),
                    ])
                    ->query(function ($query, array $data) {
                        if (!empty($data['archived_date'])) {
                            $query->whereDate('archived_at', $data['archived_date']);
                        }
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (!empty($data['archived_date'])) {
                            $selectedDate = \Carbon\Carbon::parse($data['archived_date']);
                            $today = now()->toDateString();
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
                            $query->whereBetween('archived_at', [\Carbon\Carbon::parse($from)->startOfDay(), \Carbon\Carbon::parse($to)->endOfDay()]);
                        } elseif ($from) {
                            $query->where('archived_at', '>=', \Carbon\Carbon::parse($from)->startOfDay());
                        } elseif ($to) {
                            $query->where('archived_at', '<=', \Carbon\Carbon::parse($to)->endOfDay());
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

                // Business filter (only for Kashtre)
                ...(Auth::check() && (int) Auth::user()->business_id === 1 ? [
                    Tables\Filters\SelectFilter::make('business_id')
                        ->label('Business')
                        ->options(\App\Models\Business::pluck('name', 'id')->all())
                        ->searchable(),
                ] : []),

                // Branch filter (only for non-Kashtre)
                ...(Auth::check() && (int) Auth::user()->business_id !== 1 ? [
                    Tables\Filters\SelectFilter::make('branch_id')
                        ->label('Branch')
                        ->options(function () {
                            $allowed = (array) (Auth::user()->allowed_branches ?? []);
                            return \App\Models\Branch::whereIn('id', $allowed)->pluck('name', 'id')->all();
                        })
                        ->searchable(),
                ] : []),
            ])
            ->paginated([25, 50, 100])
            ->emptyStateHeading('No records found')
            ->emptyStateDescription('No archive records found for the selected filters.');
    }

    public function render(): View
    {
        return view('livewire.visits.list-visit-archives');
    }
}

