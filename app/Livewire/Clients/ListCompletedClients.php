<?php

namespace App\Livewire\Clients;

use App\Models\ServiceDeliveryQueue;
use App\Models\Client;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Livewire\Component;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class ListCompletedClients extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        $user = Auth::user();

        // Get unique clients who have completed items assigned to this user
        $query = Client::query()
            ->distinct()
            ->whereHas('serviceDeliveryQueues', function ($q) use ($user) {
                $q->where('status', 'completed')
                  ->where('assigned_to', $user->id);
            })
            ->with(['business', 'branch'])
            ->orderBy('created_at', 'desc');

        // Restrict by business
        if (Auth::check() && Auth::user()->business_id !== 1) {
            $query->where('business_id', Auth::user()->business_id);
        } else {
            // Kashtre can see all
            $query->where('business_id', '!=', 1);
        }

        return $table
            ->query($query)
            ->columns([
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
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('completed_items_count')
                    ->label('Completed Items')
                    ->getStateUsing(function (Client $record) {
                        return ServiceDeliveryQueue::where('client_id', $record->id)
                            ->where('status', 'completed')
                            ->where('assigned_to', Auth::id())
                            ->count();
                    })
                    ->badge()
                    ->color('success'),

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
                // Date filter for completion date
                Tables\Filters\Filter::make('completion_date')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('completed_at')
                            ->label('Completion Date'),
                    ])
                    ->query(function ($query, array $data) {
                        if (!empty($data['completed_at'])) {
                            $query->whereHas('serviceDeliveryQueues', function ($q) use ($data) {
                                $q->where('status', 'completed')
                                  ->where('assigned_to', Auth::id())
                                  ->whereDate('completed_at', $data['completed_at']);
                            });
                        }
                    })
                    ->indicateUsing(function (array $data): ?string {
                        return !empty($data['completed_at'])
                            ? 'Completed: ' . \Carbon\Carbon::parse($data['completed_at'])->format('M d, Y')
                            : null;
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('View')
                    ->url(fn (Client $record): string => route('clients.completed-items', $record))
                    ->color('primary'),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No completed clients found')
            ->emptyStateDescription('You have not completed any items for clients yet.')
            ->emptyStateIcon('heroicon-o-check-circle');
    }

    public function render(): View
    {
        return view('livewire.clients.list-completed-clients');
    }
}

