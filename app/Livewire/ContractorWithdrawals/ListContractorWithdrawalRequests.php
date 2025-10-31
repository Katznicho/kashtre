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

class ListContractorWithdrawalRequests extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public int $contractorProfileId;

    public function mount(int $contractorProfileId): void
    {
        $this->contractorProfileId = $contractorProfileId;
    }

    public function table(Table $table): Table
    {
        $query = ContractorWithdrawalRequest::query()
            ->where('contractor_profile_id', $this->contractorProfileId)
            ->with(['requestedBy', 'contractorProfile.business'])
            ->latest();

        // If user is not from Kashtre, enforce business scope (defense-in-depth)
        if (Auth::check() && Auth::user()->business_id !== 1) {
            $query->where('business_id', Auth::user()->business_id);
        }

        return $table
            ->query($query)
            ->columns([
                Tables\Columns\TextColumn::make('uuid')
                    ->label('Reference')
                    ->copyable()
                    ->copyMessage('Reference copied!')
                    ->searchable()
                    ->limit(8)
                    ->tooltip(fn ($record) => $record->uuid),

                Tables\Columns\TextColumn::make('contractorProfile.business.name')
                    ->label('Business')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('requestedBy.name')
                    ->label('Requested By')
                    ->sortable()
                    ->searchable(),

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

                Tables\Columns\TextColumn::make('withdrawal_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'express' => 'warning',
                        'regular' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(function ($record): string {
                        return match ($record->status) {
                            'completed' => 'success',
                            'rejected' => 'danger',
                            'pending', 'business_approved', 'kashtre_approved', 'approved', 'processing' => 'warning',
                            default => 'gray',
                        };
                    })
                    ->formatStateUsing(fn ($record): string => $record->getStatusLabel())
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('M d, Y H:i')
                    ->sortable(),
            ])
            ->filters([
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

                Tables\Filters\SelectFilter::make('withdrawal_type')
                    ->label('Type')
                    ->options([
                        'regular' => 'Regular',
                        'express' => 'Express',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->url(fn (ContractorWithdrawalRequest $record): string => route('contractor-withdrawal-requests.show', $record)),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No withdrawal requests found')
            ->emptyStateDescription('Create your first withdrawal request to get started.')
            ->emptyStateIcon('heroicon-o-banknotes');
    }

    public function render(): View
    {
        return view('livewire.contractor-withdrawals.list-contractor-withdrawal-requests');
    }
}


