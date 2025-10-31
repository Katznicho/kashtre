<?php

namespace App\Livewire\ContractorWithdrawals;

use App\Models\ContractorWithdrawalRequest;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ListAllContractorWithdrawalRequests extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        $query = ContractorWithdrawalRequest::query()
            ->with(['requestedBy', 'contractorProfile.user', 'business'])
            ->latest();

        // Defense-in-depth: non-Kashtre users see only their business records
        if (Auth::check() && Auth::user()->business_id !== 1) {
            $query->where('business_id', Auth::user()->business_id);
        }

        return $table
            ->query($query)
            ->columns([
                Tables\Columns\TextColumn::make('uuid')
                    ->label('Reference')
                    ->copyable()
                    ->limit(8)
                    ->searchable()
                    ->tooltip(fn ($record) => $record->uuid),

                Tables\Columns\TextColumn::make('business.name')
                    ->label('Business')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('contractorProfile.user.name')
                    ->label('Contractor')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('requestedBy.name')
                    ->label('Requested By')
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Withdrawal Amount')
                    ->money('UGX')
                    ->sortable(),

                Tables\Columns\TextColumn::make('withdrawal_charge')
                    ->label('Charge')
                    ->money('UGX')
                    ->sortable(),

                Tables\Columns\TextColumn::make('net_amount')
                    ->label('Net Amount')
                    ->money('UGX')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn($record): string => match ($record->status) {
                        'completed' => 'success',
                        'rejected' => 'danger',
                        'pending', 'business_approved', 'kashtre_approved', 'approved', 'processing' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($record): string => $record->getStatusLabel())
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('M d, Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('business_id')
                    ->label('Business')
                    ->options(fn () => \App\Models\Business::pluck('name', 'id')->toArray())
                    ->searchable()
                    ->visible(fn () => Auth::check() && Auth::user()->business_id === 1),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'business_approved' => 'Business Approved',
                        'kashtre_approved' => 'Kashtre Approved',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'processing' => 'Processing',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                    ])
                    ->multiple(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->url(fn (ContractorWithdrawalRequest $record): string => route('contractor-withdrawal-requests.show', $record)),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No contractor withdrawals found')
            ->emptyStateDescription('There are no contractor withdrawal requests yet.')
            ->emptyStateIcon('heroicon-o-user');
    }

    public function render(): View
    {
        return view('livewire.contractor-withdrawals.list-all-contractor-withdrawal-requests');
    }
}


