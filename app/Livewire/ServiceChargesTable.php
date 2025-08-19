<?php

namespace App\Livewire;

use App\Models\ServiceCharge;
use App\Models\User;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Select;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ServiceChargesTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use Tables\Concerns\InteractsWithTable;



    public function table(Table $table): Table
    {
        $query = ServiceCharge::query()
            ->where('business_id', '!=', 1) // Exclude super business
            ->with(['createdBy']);
        
        return $table
            ->query($query)
            ->columns([
                TextColumn::make('business_id')
                    ->label('Business')
                    ->formatStateUsing(function (ServiceCharge $record): string {
                        try {
                            $business = \App\Models\Business::find($record->business_id);
                            return $business ? $business->name : 'Unknown Business';
                        } catch (\Exception $e) {
                            return 'Business #' . $record->business_id;
                        }
                    })
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('lower_bound')
                    ->label('Lower Bound')
                    ->formatStateUsing(fn (ServiceCharge $record): string => $record->lower_bound ? 'UGX ' . number_format($record->lower_bound, 2) : 'N/A')
                    ->sortable(),
                
                TextColumn::make('upper_bound')
                    ->label('Upper Bound')
                    ->formatStateUsing(fn (ServiceCharge $record): string => $record->upper_bound ? 'UGX ' . number_format($record->upper_bound, 2) : 'N/A')
                    ->sortable(),
                
                TextColumn::make('amount')
                    ->label('Charge')
                    ->formatStateUsing(fn (ServiceCharge $record): string => $record->type === 'percentage' ? $record->amount . '%' : 'UGX ' . number_format($record->amount, 2))
                    ->sortable(),
                
                TextColumn::make('type')
                    ->label('Charge Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'percentage' => 'success',
                        'fixed' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->sortable(),
                
                TextColumn::make('is_active')
                    ->label('Status')
                    ->badge()
                    ->color(fn (bool $state): string => $state ? 'success' : 'danger')
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Active' : 'Inactive')
                    ->sortable(),
                
                TextColumn::make('createdBy.name')
                    ->label('Created By')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M d, Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('business_id')
                    ->label('Business')
                    ->options(function () {
                        return \App\Models\Business::where('id', '!=', 1)
                            ->pluck('name', 'id')
                            ->toArray();
                    }),
                
                SelectFilter::make('type')
                    ->label('Charge Type')
                    ->options([
                        'fixed' => 'Fixed',
                        'percentage' => 'Percentage',
                    ]),
                
                SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        '1' => 'Active',
                        '0' => 'Inactive',
                    ]),
            ])
            ->actions([
                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->color('primary')
                    ->url(fn (ServiceCharge $record): string => route('service-charges.show', $record)),
                
                // EditAction::make()
                //     ->label('Edit')
                //     ->icon('heroicon-o-pencil')
                //     ->url(fn (ServiceCharge $record): string => route('service-charges.edit', $record)),
                
                DeleteAction::make()
                    ->label('Delete')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Delete Service Charge')
                    ->modalDescription('Are you sure you want to delete this service charge? This action cannot be undone.')
                    ->modalSubmitActionLabel('Yes, delete service charge')
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Delete Selected')
                        ->requiresConfirmation()
                        ->modalHeading('Delete Selected Service Charges')
                        ->modalDescription('Are you sure you want to delete the selected service charges? This action cannot be undone.')
                        ->modalSubmitActionLabel('Yes, delete selected service charges'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }

    public function makeFilamentTranslatableContentDriver(): ?\Filament\Support\Contracts\TranslatableContentDriver
    {
        return null;
    }



    public function render()
    {
        return view('livewire.service-charges-table');
    }
}
