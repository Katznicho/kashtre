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

        $query = ServiceDeliveryQueue::query()
            ->where('status', 'completed')
            ->where('assigned_to', $user->id)
            ->with([
                'client.business',
                'client.branch',
                'invoice',
                'item',
                'servicePoint',
            ])
            ->orderByDesc('completed_at');

        if (Auth::check() && Auth::user()->business_id !== 1) {
            $query->where('business_id', Auth::user()->business_id);
        } else {
            $query->where('business_id', '!=', 1);
        }

        if (!empty($user->branch_id)) {
            $query->where('branch_id', $user->branch_id);
        }

        return $table
            ->query($query)
            ->columns([
                Tables\Columns\TextColumn::make('completed_at')
                    ->label('Completed At')
                    ->dateTime('M d, Y g:i A')
                    ->sortable(),

                Tables\Columns\TextColumn::make('item_name')
                    ->label('Item')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('Qty')
                    ->state(fn (ServiceDeliveryQueue $record) => $record->quantity ?? 1)
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Price')
                    ->money('UGX', true)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('invoice.invoice_number')
                    ->label('Invoice')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('servicePoint.name')
                    ->label('Service Point')
                    ->searchable()
                    ->toggleable(),

                ...(Auth::check() && Auth::user()->business_id === 1 ? [
                    Tables\Columns\TextColumn::make('client.business.name')
                        ->label('Business')
                        ->sortable(),
                    Tables\Columns\TextColumn::make('client.branch.name')
                        ->label('Branch')
                        ->sortable(),
                ] : []),
            ])
            ->filters([
                Tables\Filters\Filter::make('completion_date')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('completed_at')
                            ->label('Completion Date'),
                    ])
                    ->query(function ($query, array $data) {
                        if (!empty($data['completed_at'])) {
                            $query->whereDate('completed_at', $data['completed_at']);
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
                    ->label('Client')
                    ->url(fn (ServiceDeliveryQueue $record): string => route('clients.show', $record->client_id))
                    ->icon('heroicon-m-user')
                    ->visible(fn (ServiceDeliveryQueue $record) => !empty($record->client_id)),
                Tables\Actions\Action::make('invoice')
                    ->label('Invoice')
                    ->icon('heroicon-m-receipt-percent')
                    ->url(fn (ServiceDeliveryQueue $record): string => route('invoices.show', $record->invoice_id))
                    ->openUrlInNewTab()
                    ->visible(fn (ServiceDeliveryQueue $record) => !empty($record->invoice_id)),
            ])
            ->defaultSort('completed_at', 'desc')
            ->emptyStateHeading('No completed transactions found')
            ->emptyStateDescription('Completed items assigned to you will appear here once processed.')
            ->emptyStateIcon('heroicon-o-check-circle');
    }

    public function render(): View
    {
        return view('livewire.clients.list-completed-clients');
    }
}

