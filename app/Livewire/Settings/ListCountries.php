<?php

namespace App\Livewire\Settings;

use App\Models\Country;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class ListCountries extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->query(Country::query()->orderBy('name'))
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Country')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('iso_code')
                    ->label('ISO')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('currency_code')
                    ->label('Currency')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('exchange_rate_to_usd')
                    ->label('Rate to USD')
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 6))
                    ->sortable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Country')
                    ->model(Country::class)
                    ->form([
                        TextInput::make('name')
                            ->label('Country')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('iso_code')
                            ->label('ISO')
                            ->required()
                            ->maxLength(10)
                            ->dehydrateStateUsing(fn (?string $state) => strtoupper(trim((string) $state))),

                        TextInput::make('currency_code')
                            ->label('Currency')
                            ->required()
                            ->maxLength(10)
                            ->dehydrateStateUsing(fn (?string $state) => strtoupper(trim((string) $state))),

                        TextInput::make('exchange_rate_to_usd')
                            ->label('Exchange Rate to USD')
                            ->numeric()
                            ->required()
                            ->minValue(0.000001)
                            ->step(0.000001),
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->form([
                        TextInput::make('name')
                            ->label('Country')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('iso_code')
                            ->label('ISO')
                            ->required()
                            ->maxLength(10)
                            ->dehydrateStateUsing(fn (?string $state) => strtoupper(trim((string) $state))),

                        TextInput::make('currency_code')
                            ->label('Currency')
                            ->required()
                            ->maxLength(10)
                            ->dehydrateStateUsing(fn (?string $state) => strtoupper(trim((string) $state))),

                        TextInput::make('exchange_rate_to_usd')
                            ->label('Exchange Rate to USD')
                            ->numeric()
                            ->required()
                            ->minValue(0.000001)
                            ->step(0.000001),
                    ]),
                Tables\Actions\DeleteAction::make(),
            ])
            ->paginated([10, 25, 50])
            ->defaultSort('name', 'asc')
            ->emptyStateHeading('No countries yet')
            ->emptyStateDescription('Add your first country and exchange rate to USD.');
    }

    public function render(): View
    {
        return view('livewire.settings.list-countries');
    }
}

