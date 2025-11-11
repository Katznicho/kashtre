<?php

namespace App\Livewire\Sales;

use App\Models\Branch;
use App\Models\Business;
use App\Models\Client;
use App\Models\Sale;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ListSales extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        $query = Sale::query()
            ->with([
                'business',
                'branch',
                'servicePoint',
                'invoice',
                'client',
                'processedByUser',
            ])
            ->orderByDesc('status_changed_at');

        return $table
            ->query($query)
            ->columns([
                Tables\Columns\TextColumn::make('status_changed_at')
                    ->label('Updated')
                    ->dateTime('M d, Y g:i A')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'partially_done',
                        'success' => 'completed',
                    ])
                    ->formatStateUsing(fn (string $state) => $state === 'completed' ? 'Completed' : 'Partially Done'),

                Tables\Columns\TextColumn::make('item_name')
                    ->label('Item')
                    ->searchable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('Qty')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('UGX', true)
                    ->sortable(),

                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('Invoice')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('client_name')
                    ->label('Client Name')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('business.name')
                    ->label('Business')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Branch')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('processedByUser.name')
                    ->label('Processed By')
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('date')
                    ->form([
                        DatePicker::make('from')->label('From'),
                        DatePicker::make('to')->label('To'),
                    ])
                    ->query(function ($query, array $data) {
                        if ($data['from'] ?? null) {
                            $query->whereDate('status_changed_at', '>=', $data['from']);
                        }
                        if ($data['to'] ?? null) {
                            $query->whereDate('status_changed_at', '<=', $data['to']);
                        }
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['from'] ?? null) {
                            $indicators[] = 'From: ' . $data['from'];
                        }
                        if ($data['to'] ?? null) {
                            $indicators[] = 'To: ' . $data['to'];
                        }
                        return $indicators;
                    }),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'partially_done' => 'Partially Done',
                        'completed' => 'Completed',
                    ]),
                Tables\Filters\SelectFilter::make('business_id')
                    ->label('Business')
                    ->options(fn () => Business::orderBy('name')->pluck('name', 'id')->all())
                    ->searchable(),
                Tables\Filters\SelectFilter::make('branch_id')
                    ->label('Branch')
                    ->options(fn () => Branch::orderBy('name')->pluck('name', 'id')->all())
                    ->searchable(),
                Tables\Filters\SelectFilter::make('client_id')
                    ->label('Client')
                    ->options(function () {
                        return Client::orderBy('surname')
                            ->orderBy('first_name')
                            ->get()
                            ->mapWithKeys(fn (Client $client) => [$client->id => $client->full_name ?? $client->name ?? "Client #{$client->id}"])
                            ->all();
                    })
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\Action::make('invoice')
                    ->label('Invoice')
                    ->icon('heroicon-m-receipt-percent')
                    ->url(fn (Sale $record): string => route('invoices.show', $record->invoice_id))
                    ->openUrlInNewTab()
                    ->visible(fn (Sale $record): bool => !empty($record->invoice_id)),

                Tables\Actions\Action::make('client')
                    ->label('Client')
                    ->icon('heroicon-m-user')
                    ->url(fn (Sale $record): string => route('clients.show', $record->client_id))
                    ->visible(fn (Sale $record): bool => !empty($record->client_id)),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('export')
                    ->label('Export Selected')
                    ->icon('heroicon-m-arrow-down-tray')
                    ->action(fn () => null)
                    ->requiresConfirmation()
                    ->deselectRecordsAfterCompletion()
                    ->disabled(), // Placeholder for future export functionality
            ])
            ->emptyStateIcon('heroicon-o-chart-bar')
            ->emptyStateHeading('No sales recorded yet')
            ->emptyStateDescription('Completed or partially done items will appear here automatically.');
    }

    public function render(): View
    {
        if (Auth::user()->business_id !== 1) {
            abort(403);
        }

        return view('livewire.sales.list-sales');
    }
}

