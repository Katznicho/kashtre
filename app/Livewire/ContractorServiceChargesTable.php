<?php

namespace App\Livewire;

use App\Models\ContractorServiceCharge;
use App\Models\Business;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\Action;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ContractorServiceChargesTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        $query = ContractorServiceCharge::query()
            ->with(['contractorProfile.user', 'business', 'createdBy'])
            ->latest();

        if (Auth::check() && Auth::user()->business_id !== 1) {
            $query->where('business_id', Auth::user()->business_id);
        }

        return $table
            ->query($query)
            ->columns([
                TextColumn::make('contractorProfile.user.name')
                    ->label('Contractor')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('amount')
                    ->label('Amount')
                    ->formatStateUsing(fn (ContractorServiceCharge $record): string => $record->formatted_amount)
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
                
                TextColumn::make('upper_bound')
                    ->label('Upper Bound')
                    ->formatStateUsing(fn ($state): string => $state ? 'UGX ' . number_format($state, 2) : 'No Limit')
                    ->sortable(),
                
                TextColumn::make('lower_bound')
                    ->label('Lower Bound')
                    ->formatStateUsing(fn ($state): string => $state ? 'UGX ' . number_format($state, 2) : 'No Limit')
                    ->sortable(),
                
                TextColumn::make('is_active')
                    ->label('Status')
                    ->badge()
                    ->color(fn (bool $state): string => $state ? 'success' : 'danger')
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Active' : 'Inactive')
                    ->sortable(),
                
                TextColumn::make('business.name')
                    ->label('Business')
                    ->sortable()
                    ->searchable()
                    ->visible(fn() => Auth::check() && Auth::user()->business_id === 1),
                
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
                
                ...(Auth::check() && Auth::user()->business_id === 1 ? [
                    SelectFilter::make('business_id')
                        ->label('Filter by Business')
                        ->options(Business::where('id', '!=', 1)->pluck('name', 'id'))
                        ->searchable()
                        ->multiple(),
                ] : []),
            ])
            ->actions([
                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->color('primary')
                    ->visible(fn() => in_array('Manage Contractor Service Charges', Auth::user()->permissions))
                    ->url(fn (ContractorServiceCharge $record): string => route('contractor-service-charges.show', $record)),
                
                EditAction::make()
                    ->label('Edit')
                    ->icon('heroicon-o-pencil')
                    ->visible(fn() => in_array('Manage Contractor Service Charges', Auth::user()->permissions))
                    ->form([
                        Forms\Components\Select::make('contractor_profile_id')
                            ->label('Contractor')
                            ->options(function () {
                                $user = Auth::user();
                                $businessId = $user->business_id;
                                
                                return \App\Models\ContractorProfile::where('business_id', $businessId)
                                    ->with('user')
                                    ->get()
                                    ->pluck('user.name', 'id');
                            })
                            ->searchable()
                            ->required(),
                        
                        Forms\Components\TextInput::make('amount')
                            ->label('Amount')
                            ->numeric()
                            ->required()
                            ->minValue(0),
                        
                        Forms\Components\Select::make('type')
                            ->label('Type')
                            ->options([
                                'fixed' => 'Fixed',
                                'percentage' => 'Percentage',
                            ])
                            ->required(),
                        
                        Forms\Components\TextInput::make('upper_bound')
                            ->label('Upper Bound')
                            ->numeric()
                            ->minValue(0),
                        
                        Forms\Components\TextInput::make('lower_bound')
                            ->label('Lower Bound')
                            ->numeric()
                            ->minValue(0),
                        
                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->rows(3),
                        
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ]),
                
                DeleteAction::make()
                    ->label('Delete')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->visible(fn() => in_array('Manage Contractor Service Charges', Auth::user()->permissions))
                    ->requiresConfirmation()
                    ->modalHeading('Delete Contractor Service Charge')
                    ->modalDescription('Are you sure you want to delete this contractor service charge? This action cannot be undone.')
                    ->modalSubmitActionLabel('Yes, delete contractor service charge')
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Delete Selected')
                        ->visible(fn() => in_array('Manage Contractor Service Charges', Auth::user()->permissions))
                        ->requiresConfirmation()
                        ->modalHeading('Delete Selected Contractor Service Charges')
                        ->modalDescription('Are you sure you want to delete the selected contractor service charges? This action cannot be undone.')
                        ->modalSubmitActionLabel('Yes, delete selected contractor service charges'),
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
        return view('livewire.contractor-service-charges-table');
    }
}
