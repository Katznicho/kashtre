<?php

namespace App\Livewire\BusinessWithdrawalSettings;

use App\Models\BusinessWithdrawalSetting;
use App\Models\Business;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class ListBusinessWithdrawalSettings extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        $query = BusinessWithdrawalSetting::query()->with('business', 'creator')->latest();

        // If user is not from Kashtre, only show their business settings
        if (Auth::check() && Auth::user()->business_id !== 1) {
            $query->where('business_id', Auth::user()->business_id);
        }

        return $table
            ->query($query)
            ->columns([
                Tables\Columns\TextColumn::make('business.name')
                    ->label('Business')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('lower_bound')
                    ->label('Lower Bound')
                    ->money('UGX')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('upper_bound')
                    ->label('Upper Bound')
                    ->money('UGX')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('charge_amount')
                    ->label('Charge')
                    ->formatStateUsing(fn (BusinessWithdrawalSetting $record): string => 
                        $record->charge_type === 'percentage' 
                            ? $record->charge_amount . '%' 
                            : 'UGX ' . number_format($record->charge_amount, 2)
                    )
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('charge_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'fixed' => 'info',
                        'percentage' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->limit(40)
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Created By')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                ...(Auth::check() && Auth::user()->business_id === 1 ? [
                    Tables\Filters\SelectFilter::make('business_id')
                        ->label('Business')
                        ->options(Business::pluck('name', 'id'))
                        ->searchable()
                        ->multiple(),
                ] : []),
                
                Tables\Filters\SelectFilter::make('charge_type')
                    ->label('Charge Type')
                    ->options([
                        'fixed' => 'Fixed',
                        'percentage' => 'Percentage',
                    ]),
                
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->placeholder('All')
                    ->trueLabel('Active')
                    ->falseLabel('Inactive'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn (BusinessWithdrawalSetting $record): string => route('business-withdrawal-settings.show', $record)),
                
                Tables\Actions\EditAction::make()
                    ->url(fn (BusinessWithdrawalSetting $record): string => route('business-withdrawal-settings.edit', $record))
                    ->visible(fn (): bool => in_array('Edit Business Withdrawal Settings', Auth::user()->permissions ?? [])),
            ])
            ->bulkActions([
                // No bulk actions
            ])
            ->headerActions([
                Tables\Actions\Action::make('create')
                    ->label('Create New')
                    ->icon('heroicon-o-plus')
                    ->url(route('business-withdrawal-settings.create'))
                    ->visible(fn (): bool => in_array('Add Business Withdrawal Settings', Auth::user()->permissions ?? [])),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No withdrawal settings found')
            ->emptyStateDescription('Create your first business withdrawal setting to get started.')
            ->emptyStateIcon('heroicon-o-banknotes');
    }

    public function render(): View
    {
        return view('livewire.business-withdrawal-settings.list-business-withdrawal-settings');
    }
}
