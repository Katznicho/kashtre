<?php

namespace App\Livewire\Items;

use App\Models\Item;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

class ListItems extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        $query = \App\Models\Item::query()->where('business_id', '!=', 1)->latest();
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
                \Filament\Tables\Columns\TextColumn::make('code')
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('type'),
                \Filament\Tables\Columns\TextColumn::make('group.name')
                    ->label('Group')
                    ->sortable()
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('subgroup.name')
                    ->label('Subgroup')
                    ->sortable()
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('department.name')
                    ->label('Department')
                    ->sortable()
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('uom.name')
                    ->label('Unit')
                    ->sortable()
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('servicePoint.name')
                    ->label('Service Point')
                    ->sortable()
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('default_price')
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('hospital_share')
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('contractorAccount.name')
                    ->label('Contractor Account')
                    ->sortable()
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
                        ->options(\App\Models\Business::where('id', '!=', 1)->pluck('name', 'id'))
                        ->searchable()
                        ->multiple(),
                ] : []),
            ])
            ->actions([
                //
            ])
            ->bulkActions([
                \Filament\Tables\Actions\BulkActionGroup::make([
                    //
                ]),
            ]);
    }

    public function render(): View
    {
        return view('livewire.items.list-items');
    }
}
