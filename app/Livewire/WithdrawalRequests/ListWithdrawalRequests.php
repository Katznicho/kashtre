<?php

namespace App\Livewire\WithdrawalRequests;

use App\Models\WithdrawalRequest;
use App\Models\Business;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class ListWithdrawalRequests extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        $query = WithdrawalRequest::query()->with('business', 'requester')->latest();

        // If user is not from Kashtre, only show their business requests
        if (Auth::check() && Auth::user()->business_id !== 1) {
            $query->where('business_id', Auth::user()->business_id);
        }

        return $table
            ->query($query)
            ->columns([
                Tables\Columns\TextColumn::make('uuid')
                    ->label('Request ID')
                    ->copyable()
                    ->copyMessage('ID copied!')
                    ->searchable()
                    ->limit(8)
                    ->tooltip(fn ($record) => $record->uuid),
                
                Tables\Columns\TextColumn::make('business.name')
                    ->label('Business')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('requester.name')
                    ->label('Requested By')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->money('UGX')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('withdrawal_charge')
                    ->label('Charge')
                    ->money('UGX')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
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
                
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Payment Method')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'bank_transfer' => 'success',
                        'mobile_money' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => str_replace('_', ' ', ucwords($state, '_')))
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn($record): string => $record->status_color)
                    ->formatStateUsing(fn ($record): string => $record->formatted_status)
                    ->sortable()
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('business_approvals_count')
                    ->label('Business Approvals')
                    ->formatStateUsing(fn ($record): string => "{$record->business_approvals_count}/{$record->required_business_approvals}")
                    ->color(fn($record): string => $record->hasBusinessApproval() ? 'success' : 'warning')
                    ->badge()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('kashtre_approvals_count')
                    ->label('Kashtre Approvals')
                    ->formatStateUsing(fn ($record): string => "{$record->kashtre_approvals_count}/{$record->required_kashtre_approvals}")
                    ->color(fn($record): string => $record->hasKashtreApproval() ? 'success' : 'warning')
                    ->badge()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                ...(Auth::check() && Auth::user()->business_id === 1 ? [
                    Tables\Filters\SelectFilter::make('business_id')
                        ->label('Business')
                        ->options(Business::pluck('name', 'id'))
                        ->searchable()
                        ->multiple(),
                ] : []),
                
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
                    ->label('Withdrawal Type')
                    ->options([
                        'regular' => 'Regular',
                        'express' => 'Express',
                    ]),
                
                Tables\Filters\SelectFilter::make('payment_method')
                    ->label('Payment Method')
                    ->options([
                        'bank_transfer' => 'Bank Transfer',
                        'mobile_money' => 'Mobile Money',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn (WithdrawalRequest $record): string => route('withdrawal-requests.show', $record)),
                
                Tables\Actions\EditAction::make()
                    ->url(fn (WithdrawalRequest $record): string => route('withdrawal-requests.edit', $record))
                    ->visible(fn (WithdrawalRequest $record): bool => 
                        in_array('Manage Withdrawal Requests', Auth::user()->permissions ?? []) 
                        && $record->isPending()
                    ),
            ])
            ->bulkActions([
                // No bulk actions
            ])
            ->headerActions([
                Tables\Actions\Action::make('create')
                    ->label('New Withdrawal Request')
                    ->icon('heroicon-o-plus')
                    ->url(route('withdrawal-requests.create'))
                    ->visible(fn (): bool => in_array('View Withdrawal Requests', Auth::user()->permissions ?? [])),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No withdrawal requests found')
            ->emptyStateDescription('Create your first withdrawal request to get started.')
            ->emptyStateIcon('heroicon-o-banknotes');
    }

    public function render(): View
    {
        return view('livewire.withdrawal-requests.list-withdrawal-requests');
    }
}
