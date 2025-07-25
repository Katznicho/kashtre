<?php

namespace App\Livewire\supplier;

use App\Models\Supplier;
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

class ListSuppliers extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        $query = \App\Models\Supplier::query()->where('business_id', '!=', 1)->latest();
        if (auth()->check() && auth()->user()->business_id !== 1) {
            $query->where('business_id', auth()->user()->business_id);
        }
        return $table
            ->query($query)
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('uuid')
                    ->label('UUID')
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('description')
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('business.name')
                    ->label('Business')
                    ->sortable()
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                \Filament\Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                \Filament\Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                ...((auth()->check() && auth()->user()->business_id === 1) ? [
                    \Filament\Tables\Filters\SelectFilter::make('business_id')
                        ->label('Filter by Business')
                        ->options(\App\Models\Business::pluck('name', 'id'))
                        ->searchable()
                        ->multiple(),
                ] : []),
            ])
            ->actions([
                \Filament\Tables\Actions\EditAction::make()
                    ->label('Edit Supplier')
                    ->visible(fn() => in_array('Edit Supplier', Auth::user()->permissions))
                    ->modalHeading('Edit Supplier')
                    ->form(fn(\App\Models\Supplier $record) => [
                        \Filament\Forms\Components\Select::make('business_id')
                            ->label('Business')
                            ->placeholder('Select a business')
                            ->options(\App\Models\Business::pluck('name', 'id'))
                            ->required()
                            ->disabled(fn() => auth()->user()->business_id !== 1),
                        \Filament\Forms\Components\TextInput::make('name')
                            ->label('Supplier Name')
                            ->required(),
                        \Filament\Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->nullable(),
                    ])
                    ->successNotificationTitle('Supplier updated successfully.'),
            ])
            ->headerActions([
                \Filament\Tables\Actions\CreateAction::make()
                    ->label('Create Supplier')
                    ->visible(fn() => in_array('Add Supplier', Auth::user()->permissions))
                    ->modalHeading('Add New Supplier')
                    ->form([
                        \Filament\Forms\Components\Select::make('business_id')
                            ->label('Business')
                            ->placeholder('Select a business')
                            ->options(\App\Models\Business::pluck('name', 'id'))
                            ->required()
                            ->default(auth()->user()->business_id)
                            ->disabled(fn() => auth()->user()->business_id !== 1),
                        \Filament\Forms\Components\TextInput::make('name')
                            ->label('Supplier Name')
                            ->required(),
                        \Filament\Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->nullable(),
                    ])
                    ->createAnother(false)
                    ->after(function (\App\Models\Supplier $record) {
                        \Filament\Notifications\Notification::make()
                            ->title('Supplier created successfully.')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                \Filament\Tables\Actions\BulkActionGroup::make([
                    //
                ]),
            ]);
    }

    public function render(): View
    {
        return view('livewire.supplier.list-suppliers');
    }
}
