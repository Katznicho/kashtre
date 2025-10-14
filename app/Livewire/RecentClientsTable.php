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
        
        // For Kashtre (business_id == 1), show all clients from all businesses
        if ($business->id == 1) {
            $query = Client::query()
                ->where('business_id', '!=', 1)
                ->with(['business', 'branch'])
                ->latest()
                ->limit(20); // Show more for Kashtre
        } else {
            $query = Client::query()
                ->where('business_id', '!=', 1)
                ->where('business_id', $business->id)
                ->where('branch_id', $currentBranch->id)
                ->latest()
                ->limit(5);
        }
        
        return $table
            ->query($query)
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
                
                // Show Business column only for Kashtre users
                ...($business->id == 1 ? [
                    TextColumn::make('business.name')
                        ->label('Business')
                        ->searchable()
                        ->sortable()
                        ->badge()
                        ->color('purple')
                        ->size('sm'),
                ] : []),
                
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
                
                TextColumn::make('balance')
                    ->label('Balance')
                    ->money('UGX')
                    ->size('sm')
                    ->color(fn (float $state): string => $state > 0 ? 'success' : 'gray'),
                
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
                
                Action::make('balance_history')
                    ->label('Balance')
                    ->icon('heroicon-o-currency-dollar')
                    ->color('info')
                    ->size('sm')
                    ->url(fn (Client $record): string => route('balance-statement.show', $record)),
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
