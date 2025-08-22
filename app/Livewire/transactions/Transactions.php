<?php

namespace App\Livewire\Transactions;

use App\Models\Transaction;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class Transactions extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        // $query = Transaction::query()
        $query = Transaction::query()->where('business_id', '!=', 1)->latest(); // Orders by created_at DESC by default

         //get the lastest transactions

        ;

        // If not admin, limit to their business_id
        if (Auth::check() && Auth::user()->business_id !== 1) {
            $query->where('business_id', Auth::user()->business_id);
        }

        return $table
            ->query($query)
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->label('Date')
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->sortable()
                    ->money('UGX')
                    ->searchable(),

                Tables\Columns\TextColumn::make('reference')
                    ->searchable()
                    ->copyable()
                    ->sortable()
                    ->label('Payment Reference')

                    ,

                Tables\Columns\TextColumn::make('description')
                    ->limit(40),

                Tables\Columns\TextColumn::make('transaction_for')
                    ->label('Transaction For')
                    ->sortable(),

                Tables\Columns\TextColumn::make('business.name')
                    ->label('Business')
                    ->searchable()
                    ->sortable(),


                Auth::user()?->business_id == 1
                    ? Tables\Columns\SelectColumn::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                        'cancelled' => 'Cancelled',
                        'processing' => 'Processing',
                    ])
                    ->label('Status')
                    ->sortable()
                    : Tables\Columns\TextColumn::make('status')
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->sortable(),

                Tables\Columns\TextColumn::make('origin')
                    ->sortable()
                    ->visible(Auth::user()?->business_id === 1)
                    ,

                Tables\Columns\TextColumn::make('phone_number')
                    ->searchable(),

                Tables\Columns\TextColumn::make('provider')
                    ->sortable(),

                Tables\Columns\TextColumn::make('service'),

                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->label('Date')
                    ->sortable(),

                Tables\Columns\TextColumn::make('currency')
                    ->sortable(),

                Tables\Columns\TextColumn::make('names')
                    ->searchable(),

                Tables\Columns\TextColumn::make('email')
                    ->searchable(),

                Tables\Columns\TextColumn::make('ip_address')
                    ->visible(Auth::user()?->business_id === 1)
                    ,

                Tables\Columns\TextColumn::make('user_agent')
                    ->visible(Auth::user()?->business_id === 1)
                    ,

                Tables\Columns\TextColumn::make('method')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Created At'),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Updated At'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                        'cancelled' => 'Cancelled',
                        'processing' => 'Processing',
                    ]),
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'credit' => 'Credit',
                        'debit' => 'Debit',
                    ]),
                Tables\Filters\SelectFilter::make('provider')
                    ->options([
                        'mtn' => 'MTN',
                        'airtel' => 'Airtel',
                    ]),
                Tables\Filters\SelectFilter::make('transaction_for')
                    ->options([
                        'main' => 'Main',
                        'suspense' => 'Suspense',
                    ]),
                Tables\Filters\Filter::make('date_range')
                    ->form([
                        Forms\Components\DatePicker::make('date_from'),
                        Forms\Components\DatePicker::make('date_to'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $data['date_to'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public function render(): View
    {
        return view('livewire.transactions.transactions');
    }
}
