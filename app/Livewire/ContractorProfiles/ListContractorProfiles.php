<?php

namespace App\Livewire\ContractorProfiles;

use App\Models\ContractorProfile;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListContractorProfiles extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        $query = ContractorProfile::with(['business', 'user'])
            ->withTrashed() // Include soft-deleted records
            ->orderBy('created_at', 'desc');
        
        // Filter by business: super admin (business_id == 1) can see all, others see only their business
        if (Auth::user()->business_id && Auth::user()->business_id != 1) {
            $query->where('business_id', Auth::user()->business_id);
        }
        
        return $table
            ->query($query)
            ->defaultPaginationPageOption(50)
            ->paginationPageOptions([10, 25, 50, 100])
            ->columns([
                Tables\Columns\TextColumn::make('business.name')
                    ->label('Business')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->description(fn (ContractorProfile $record): string => $record->user->email ?? '')
                    ->searchable()
                    ->sortable(),
                // Tables\Columns\TextColumn::make('uuid')
                //     ->label('UUID')
                //     ->searchable(),
                Tables\Columns\TextColumn::make('bank_name')
                    ->label('Bank Name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('account_name')
                    ->label('Bank A/C Name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('account_number')
                    ->label('Bank A/C No.')
                    ->searchable(),
                Tables\Columns\TextColumn::make('account_balance')
                    ->label('Account Balance')
                    ->money('UGX')
                    ->sortable(),
                Tables\Columns\TextColumn::make('kashtre_account_number')
                    ->label('Kashtre A/C No.')
                    ->searchable(),
                Tables\Columns\TextColumn::make('signing_qualifications')
                    ->label('Qualifications')
                    ->searchable(),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
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
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->visible(fn() => in_array('View Contractor Profile', Auth::user()->permissions))
                    ->url(fn (ContractorProfile $record): string => route('contractor-profiles.show', $record)),
                // Tables\Actions\EditAction::make()
                //     ->url(fn (ContractorProfile $record): string => route('contractor-profiles.edit', $record)),
                // Tables\Actions\DeleteAction::make()
                //     ->requiresConfirmation()
                //     ->modalHeading('Delete Contractor Profile')
                //     ->modalDescription('Are you sure you want to delete this contractor profile? This action cannot be undone.')
                //     ->modalSubmitActionLabel('Yes, delete it')
                //     ->action(fn (ContractorProfile $record) => $record->delete()),
            ])
            ->headerActions([
                Tables\Actions\Action::make('download_template')
                    ->label('Download Template')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->url(route('contractor-profiles.bulk-upload.template'))
                    ->visible(fn() => in_array('Add Contractor Profile', Auth::user()->permissions ?? [])),
                Tables\Actions\Action::make('bulk_upload')
                    ->label('Bulk Upload')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('warning')
                    ->url(route('contractor-profiles.bulk-upload'))
                    ->visible(fn() => in_array('Add Contractor Profile', Auth::user()->permissions ?? [])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Delete Selected Contractor Profiles')
                        ->modalDescription('Are you sure you want to delete the selected contractor profiles? This action cannot be undone.')
                        ->modalSubmitActionLabel('Yes, delete them'),
                ]),
            ]);
    }

    public function render(): View
    {
        return view('livewire.contractor-profiles.list-contractor-profiles');
    }
}
