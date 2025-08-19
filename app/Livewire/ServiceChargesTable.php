<?php

namespace App\Livewire;

use App\Models\ServiceCharge;
use App\Models\Business;
use App\Models\Branch;
use App\Models\ServicePoint;
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

    public $selectedBusinessId;
    public $availableBusinesses;

    public function mount()
    {
        $this->selectedBusinessId = request()->get('business_id', '');
        $this->loadAvailableBusinesses();
    }

    public function loadAvailableBusinesses()
    {
        $user = Auth::user();
        $business = $user->business;
        
        // Get all businesses except the first one (super business)
        $this->availableBusinesses = Business::where('id', '!=', 1)->get();

        if ($this->availableBusinesses->isNotEmpty() && !$this->selectedBusinessId) {
            $this->selectedBusinessId = $this->availableBusinesses->first()->id;
        }
    }

    public function table(Table $table): Table
    {
        $user = Auth::user();
        $business = $user->business;
        
        $query = ServiceCharge::query()
            ->where('business_id', '!=', 1) // Exclude super business
            ->with(['createdBy', 'entity']);

        if ($this->selectedBusinessId) {
            $query->where('entity_type', 'business')
                  ->where('entity_id', $this->selectedBusinessId);
        }
        
        return $table
            ->query($query)
            ->columns([
                TextColumn::make('entity_type')
                    ->label('Entity Type')
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->badge()
                    ->color('primary')
                    ->sortable(),
                
                TextColumn::make('entity_name')
                    ->label('Entity Name')
                    ->formatStateUsing(fn (ServiceCharge $record): string => $record->entity_name)
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('formatted_amount')
                    ->label('Amount')
                    ->formatStateUsing(fn (ServiceCharge $record): string => $record->formatted_amount)
                    ->sortable(),
                
                TextColumn::make('type')
                    ->label('Type')
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
                SelectFilter::make('entity_type')
                    ->label('Entity Type')
                    ->options([
                        'business' => 'Business',
                        'branch' => 'Branch',
                        'service_point' => 'Service Point',
                    ]),
                
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
                
                EditAction::make()
                    ->label('Edit')
                    ->icon('heroicon-o-pencil')
                    ->url(fn (ServiceCharge $record): string => route('service-charges.edit', $record)),
                
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

    public function updatedSelectedBusinessId($value)
    {
        $this->selectedBusinessId = $value;
        $this->resetTable();
    }

    public function render()
    {
        return view('livewire.service-charges-table');
    }
}
