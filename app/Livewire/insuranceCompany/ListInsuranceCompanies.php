<?php

namespace App\Livewire\InsuranceCompany;

use App\Models\InsuranceCompany;
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
        $query = InsuranceCompany::query()->latest();

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
                    ->searchable()
                    ->placeholder('N/A'),

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
            ])
            ->actions([
                EditAction::make()
                    ->visible(fn() => in_array('Edit Insurance Companies', Auth::user()->permissions))
                    ->modalHeading('Edit Insurance Company')
                    ->form(fn(InsuranceCompany $record) => [
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
