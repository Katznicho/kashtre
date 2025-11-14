<?php

namespace App\Livewire\PaymentMethodAccounts;

use App\Models\PaymentMethodAccount;
use App\Models\PaymentMethodAccountTransaction;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Livewire\Component;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

class TransactionsTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public PaymentMethodAccount $paymentMethodAccount;
    public $totalCredits = 0;
    public $totalDebits = 0;
    public $calculatedBalance = 0;

    public function mount(PaymentMethodAccount $paymentMethodAccount)
    {
        $this->paymentMethodAccount = $paymentMethodAccount->load(['business', 'createdBy']);
        
        // Calculate totals
        $this->totalCredits = PaymentMethodAccountTransaction::forAccount($paymentMethodAccount->id)
            ->credits()
            ->completed()
            ->sum('amount');

        $this->totalDebits = PaymentMethodAccountTransaction::forAccount($paymentMethodAccount->id)
            ->debits()
            ->completed()
            ->sum('amount');

        $this->calculatedBalance = $this->totalCredits - $this->totalDebits;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                PaymentMethodAccountTransaction::forAccount($this->paymentMethodAccount->id)
                    ->with(['client', 'invoice', 'createdBy', 'business'])
                    ->latest()
            )
            ->columns([
                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('M d, Y H:i:s')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('type')
                    ->label('Type')
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'credit' => 'success',
                        'debit' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('reference')
                    ->label('Reference')
                    ->searchable()
                    ->copyable()
                    ->sortable()
                    ->url(fn ($record) => $record->invoice_id ? route('invoices.show', $record->invoice_id) : null)
                    ->color(fn ($record) => $record->invoice_id ? 'primary' : null),
                
                TextColumn::make('external_reference')
                    ->label('External Ref')
                    ->searchable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->default('—'),

                TextColumn::make('description')
                    ->label('Description')
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->description)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('amount')
                    ->label('Amount')
                    ->sortable()
                    ->formatStateUsing(function ($state, $record) {
                        $sign = $record->type === 'credit' ? '+' : '-';
                        return $sign . number_format($state, 2) . ' ' . ($record->currency ?? 'UGX');
                    })
                    ->color(fn ($record) => $record->type === 'credit' ? 'success' : 'danger')
                    ->alignRight(),

                TextColumn::make('balance_before')
                    ->label('Balance Before')
                    ->sortable()
                    ->formatStateUsing(fn ($state, $record) => $state ? number_format($state, 2) . ' ' . ($record->currency ?? 'UGX') : 'N/A')
                    ->alignRight(),

                TextColumn::make('balance_after')
                    ->label('Balance After')
                    ->sortable()
                    ->formatStateUsing(fn ($state, $record) => $state ? number_format($state, 2) . ' ' . ($record->currency ?? 'UGX') : 'N/A')
                    ->alignRight(),

                TextColumn::make('client.name')
                    ->label('Client')
                    ->searchable()
                    ->sortable()
                    ->url(fn ($record) => $record->client_id ? route('clients.show', $record->client_id) : null)
                    ->color(fn ($record) => $record->client_id ? 'primary' : null)
                    ->default('—'),

                TextColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'pending' => 'warning',
                        'failed' => 'danger',
                        'cancelled' => 'secondary',
                        default => 'gray',
                    })
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Transaction Type')
                    ->options([
                        'credit' => 'Credit',
                        'debit' => 'Debit',
                    ]),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                        'cancelled' => 'Cancelled',
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50, 100]);
    }

    public function render(): View
    {
        return view('livewire.payment-method-accounts.transactions-table');
    }

    public function makeFilamentTranslatableContentDriver(): ?\Filament\Support\Contracts\TranslatableContentDriver
    {
        return null;
    }
}
