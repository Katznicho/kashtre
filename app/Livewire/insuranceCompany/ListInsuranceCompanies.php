<?php

namespace App\Livewire\InsuranceCompany;

use App\Models\InsuranceCompany;
use App\Models\Business;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ListInsuranceCompanies extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        $query = InsuranceCompany::query()->where('business_id', '!=', 1)->latest();

        if (Auth::check() && Auth::user()->business_id !== 1) {
            $query->where('business_id', Auth::user()->business_id);
        }

        return $table
            ->query($query)
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Company Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('business.name')
                    ->label('Business')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->limit(50)
                    ->wrap()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('deleted_at')
                    ->label('Deleted At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                ...(Auth::check() && Auth::user()->business_id === 1 ? [
                    Tables\Filters\SelectFilter::make('business_id')
                        ->label('Filter by Business')
                        ->options(Business::where('id', '!=', 1)->pluck('name', 'id'))
                        ->searchable()
                        ->multiple(),
                ] : []),
            ])
            ->actions([
                EditAction::make()
                    ->visible(fn() => in_array('Edit Insurance Companies', Auth::user()->permissions))
                    ->modalHeading('Edit Insurance Company')
                    ->form(fn(InsuranceCompany $record) => [
                        Select::make('business_id')
                            ->label('Business')
                            ->placeholder('Select a business')
                            ->options(Business::where('id', '!=', 1)->pluck('name', 'id'))
                            ->required()
                            ->disabled(fn() => Auth::user()->business_id !== 1),

                        TextInput::make('name')
                            ->label('Company Name')
                            ->placeholder('Enter company name')
                            ->required(),

                        Textarea::make('description')
                            ->label('Description')
                            ->placeholder('Enter company description')
                            ->nullable(),
                    ])
                    ->successNotificationTitle('Insurance company updated successfully.'),

                DeleteAction::make()
                    ->visible(fn() => in_array('Delete Insurance Companies', Auth::user()->permissions))
                    ->modalHeading('Delete Insurance Company')
                    ->successNotificationTitle('Insurance company deleted (soft) successfully.'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->headerActions([
                CreateAction::make()
                    ->visible(fn() => in_array('Add Insurance Companies', Auth::user()->permissions))
                    ->label('Create Insurance Company')
                    ->modalHeading('Add New Insurance Company')
                    ->form([
                        Select::make('business_id')
                            ->label('Business')
                            ->placeholder('Select a business')
                            ->options(Business::where('id', '!=', 1)->pluck('name', 'id'))
                            ->required()
                            ->default(Auth::user()->business_id)
                            ->disabled(fn() => Auth::user()->business_id !== 1),

                        TextInput::make('name')
                            ->label('Company Name')
                            ->placeholder('Enter company name')
                            ->required(),

                        Textarea::make('description')
                            ->label('Description')
                            ->placeholder('Enter company description')
                            ->nullable(),
                    ])
                    ->createAnother(false)
                    ->after(function (InsuranceCompany $record) {
                        Notification::make()
                            ->title('Insurance company created successfully.')
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public function render(): View
    {
        return view('livewire.insurance-company.list-insurance-companies');
    }
}
