<?php

namespace App\Livewire;

use App\Models\PackageSales;
use App\Models\Client;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class PackageSalesTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use Tables\Concerns\InteractsWithTable;

    public function table(Table $table): Table
    {
        $user = Auth::user();

        return $table
            ->query(
                PackageSales::query()
                    ->where('business_id', $user->business_id)
                    ->with(['client', 'packageTracking', 'item', 'business', 'branch'])
            )
            ->columns([
                TextColumn::make('date')
                    ->label('Date')
                    ->date('M d, Y')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('name')
                    ->label('Client')
                    ->searchable()
                    ->sortable()
                    ->description(fn (PackageSales $record): string => $record->client->phone_number ?? ''),

                TextColumn::make('invoice_number')
                    ->label('Invoice')
                    ->searchable()
                    ->sortable()
                    ->color('primary'),

                TextColumn::make('pkn')
                    ->label('PKN')
                    ->searchable()
                    ->sortable()
                    ->color('info')
                    ->weight('bold'),

                TextColumn::make('item_name')
                    ->label('Item')
                    ->searchable()
                    ->sortable()
                    ->description(fn (PackageSales $record): string => $record->item->description ?? ''),

                TextColumn::make('qty')
                    ->label('Quantity')
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('amount')
                    ->label('Amount')
                    ->money('UGX')
                    ->sortable()
                    ->color('success')
                    ->weight('bold'),

                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'completed',
                        'warning' => 'pending',
                        'danger' => 'cancelled',
                    ])
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'completed' => 'Completed',
                        'pending' => 'Pending',
                        'cancelled' => 'Cancelled',
                    ]),
                
                SelectFilter::make('client_id')
                    ->label('Client')
                    ->options(Client::where('business_id', $user->business_id)->pluck('name', 'id')->toArray())
                    ->searchable(),

                Filter::make('date_range')
                    ->label('Date Range')
                    ->form([
                        DatePicker::make('start_date')
                            ->label('Start Date'),
                        DatePicker::make('end_date')
                            ->label('End Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['start_date'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $data['end_date'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '<=', $date),
                            );
                    }),

                Filter::make('amount_range')
                    ->label('Amount Range')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('min_amount')
                            ->label('Minimum Amount')
                            ->numeric()
                            ->prefix('UGX'),
                        \Filament\Forms\Components\TextInput::make('max_amount')
                            ->label('Maximum Amount')
                            ->numeric()
                            ->prefix('UGX'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min_amount'],
                                fn (Builder $query, $amount): Builder => $query->where('amount', '>=', $amount),
                            )
                            ->when(
                                $data['max_amount'],
                                fn (Builder $query, $amount): Builder => $query->where('amount', '<=', $amount),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('View')
                    ->url(fn (PackageSales $record): string => route('package-sales.show', $record))
                    ->icon('heroicon-o-eye')
                    ->color('primary'),
                
                Tables\Actions\Action::make('delete')
                    ->label('Delete')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (PackageSales $record): void {
                        $record->delete();
                        \Filament\Notifications\Notification::make()
                            ->title('Package Sale Deleted')
                            ->body('The package sale record has been deleted successfully.')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (): bool => in_array('Delete Package Sales', (array) auth()->user()->permissions)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn (): bool => in_array('Delete Package Sales', (array) auth()->user()->permissions)),
                ]),
            ])
            ->defaultSort('date', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->poll('30s'); // Auto-refresh every 30 seconds
    }

    public function render()
    {
        return view('livewire.package-sales-table');
    }
}
