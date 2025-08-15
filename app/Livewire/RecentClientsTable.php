<?php

namespace App\Livewire;

use App\Models\Client;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class RecentClientsTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use Tables\Concerns\InteractsWithTable;

    public function table(Table $table): Table
    {
        $user = Auth::user();
        $business = $user->business;
        $currentBranch = $user->current_branch;
        
        return $table
            ->query(
                Client::query()
                    ->where('business_id', '!=', 1)
                    ->where('business_id', $business->id)
                    ->where('branch_id', $currentBranch->id)
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                TextColumn::make('client_id')
                    ->label('Client ID')
                    ->searchable()
                    ->weight('bold')
                    ->size('sm'),
                
                TextColumn::make('full_name')
                    ->label('Name')
                    ->searchable(['surname', 'first_name', 'other_names'])
                    ->formatStateUsing(fn (Client $record): string => $record->full_name)
                    ->size('sm'),
                
                TextColumn::make('phone_number')
                    ->label('Phone')
                    ->searchable()
                    ->size('sm'),
                
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'warning',
                        'suspended' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->size('sm'),
                
                TextColumn::make('created_at')
                    ->label('Registered')
                    ->dateTime('M d, Y')
                    ->size('sm'),
            ])
            ->actions([
                Action::make('details')
                    ->label('Details')
                    ->icon('heroicon-o-arrow-right')
                    ->color('success')
                    ->size('sm')
                    ->url(fn (Client $record): string => route('pos.item-selection', $record)),
                
                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->color('primary')
                    ->size('sm')
                    ->url(fn (Client $record): string => route('clients.show', $record)),
            ])
            ->paginated(false)
            ->striped();
    }

    public function makeFilamentTranslatableContentDriver(): ?\Filament\Support\Contracts\TranslatableContentDriver
    {
        return null;
    }

    public function render()
    {
        return view('livewire.recent-clients-table');
    }
}
