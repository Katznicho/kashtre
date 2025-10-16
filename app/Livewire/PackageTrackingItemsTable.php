<?php

namespace App\Livewire;

use App\Models\PackageTracking;
use App\Models\PackageTrackingItem;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class PackageTrackingItemsTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use Tables\Concerns\InteractsWithTable;

    public PackageTracking $packageTracking;

    public function mount(PackageTracking $packageTracking)
    {
        $this->packageTracking = $packageTracking;
    }

    public function table(Table $table): Table
    {
        $query = PackageTrackingItem::query()
            ->where('package_tracking_id', $this->packageTracking->id)
            ->with(['includedItem']);
        
        return $table
            ->query($query)
            ->columns([
                TextColumn::make('includedItem.name')
                    ->label('Item Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->formatStateUsing(function ($record) {
                        return $record->includedItem->name ?? 'Unknown';
                    }),
                
                TextColumn::make('includedItem.description')
                    ->label('Description')
                    ->searchable()
                    ->limit(50)
                    ->formatStateUsing(function ($record) {
                        return $record->includedItem->description ?? 'No description';
                    }),
                
                
                TextColumn::make('total_quantity')
                    ->label('Total Qty')
                    ->sortable()
                    ->formatStateUsing(function ($record) {
                        return $record->total_quantity;
                    }),
                
                TextColumn::make('used_quantity')
                    ->label('Used Qty')
                    ->sortable()
                    ->formatStateUsing(function ($record) {
                        return $record->used_quantity;
                    }),
                
                TextColumn::make('remaining_quantity')
                    ->label('Remaining')
                    ->sortable()
                    ->formatStateUsing(function ($record) {
                        return $record->remaining_quantity;
                    })
                    ->color(fn (int $state): string => $state > 0 ? 'success' : 'danger'),
                
                TextColumn::make('item_price')
                    ->label('Price')
                    ->money('UGX')
                    ->sortable(),
                
                TextColumn::make('usage_percentage')
                    ->label('Usage %')
                    ->formatStateUsing(function ($record) {
                        return number_format($record->usage_percentage, 1) . '%';
                    })
                    ->color(fn (float $state): string => $state > 80 ? 'danger' : ($state > 50 ? 'warning' : 'success')),
                
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M d, Y')
                    ->sortable(),
            ])
            ->filters([
                
                Tables\Filters\Filter::make('remaining_quantity')
                    ->label('Remaining Quantity')
                    ->form([
                        \Filament\Forms\Components\Select::make('filter')
                            ->label('Filter by Remaining')
                            ->options([
                                'available' => 'Available (Remaining > 0)',
                                'exhausted' => 'Exhausted (Remaining = 0)',
                                'low_stock' => 'Low Stock (Remaining < 5)',
                            ])
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['filter'] ?? null) {
                            'available' => $query->where('remaining_quantity', '>', 0),
                            'exhausted' => $query->where('remaining_quantity', '=', 0),
                            'low_stock' => $query->where('remaining_quantity', '<', 5)->where('remaining_quantity', '>', 0),
                            default => $query,
                        };
                    })
            ])
            ->actions([
                Action::make('use_quantity')
                    ->label('Use Quantity')
                    ->icon('heroicon-o-minus')
                    ->color('warning')
                    ->visible(fn (PackageTrackingItem $record): bool => $record->remaining_quantity > 0)
                    ->form([
                        \Filament\Forms\Components\TextInput::make('quantity')
                            ->label('Quantity to Use')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(fn (PackageTrackingItem $record): int => $record->remaining_quantity)
                            ->default(1),
                    ])
                    ->action(function (PackageTrackingItem $record, array $data): void {
                        $quantity = (int) $data['quantity'];
                        if ($record->useQuantity($quantity)) {
                            \Filament\Notifications\Notification::make()
                                ->title('Quantity Used Successfully')
                                ->body("Used {$quantity} units of {$record->includedItem->name}")
                                ->success()
                                ->send();
                        } else {
                            \Filament\Notifications\Notification::make()
                                ->title('Failed to Use Quantity')
                                ->body('Insufficient remaining quantity')
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    BulkAction::make('use_quantity_bulk')
                        ->label('Use Quantity (Selected)')
                        ->icon('heroicon-o-minus')
                        ->color('warning')
                        ->form([
                            \Filament\Forms\Components\TextInput::make('quantity')
                                ->label('Quantity to Use')
                                ->numeric()
                                ->required()
                                ->minValue(1)
                                ->default(1),
                        ])
                        ->action(function (array $records, array $data): void {
                            $quantity = (int) $data['quantity'];
                            $successCount = 0;
                            
                            foreach ($records as $record) {
                                if ($record->useQuantity($quantity)) {
                                    $successCount++;
                                }
                            }
                            
                            \Filament\Notifications\Notification::make()
                                ->title('Bulk Quantity Update')
                                ->body("Successfully used {$quantity} units for {$successCount} items")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50]);
    }

    public function makeFilamentTranslatableContentDriver(): ?\Filament\Support\Contracts\TranslatableContentDriver
    {
        return null;
    }

    public function render()
    {
        return view('livewire.package-tracking-items-table');
    }
}
