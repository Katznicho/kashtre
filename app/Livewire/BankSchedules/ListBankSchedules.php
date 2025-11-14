<?php

namespace App\Livewire\BankSchedules;

use App\Models\BankSchedule;
use App\Models\Business;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Livewire\Component;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class ListBankSchedules extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        // Only Kashtre users can access
        if (auth()->user()->business_id !== 1) {
            abort(403, 'Access denied. Bank schedules are only accessible to Kashtre administrators.');
        }

        $query = BankSchedule::query()
            ->with(['business', 'withdrawalRequest', 'creator'])
            ->latest();

        return $table
            ->query($query)
            ->columns([
                TextColumn::make('business.name')
                    ->label('Business')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('client_name')
                    ->label('Client Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('amount')
                    ->label('Amount')
                    ->money('UGX')
                    ->sortable()
                    ->alignRight(),

                TextColumn::make('bank_name')
                    ->label('Bank Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('bank_account')
                    ->label('Bank Account')
                    ->searchable()
                    ->copyable()
                    ->sortable(),

                TextColumn::make('reference_id')
                    ->label('Reference ID')
                    ->searchable()
                    ->copyable()
                    ->sortable()
                    ->default('—'),

                TextColumn::make('withdrawalRequest.uuid')
                    ->label('Withdrawal Request')
                    ->formatStateUsing(fn ($state) => $state ? substr($state, 0, 8) . '...' : '—')
                    ->url(fn ($record) => $record->withdrawal_request_id ? route('withdrawal-requests.show', $record->withdrawal_request_id) : null)
                    ->color(fn ($record) => $record->withdrawal_request_id ? 'primary' : null)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'processed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('creator.name')
                    ->label('Created By')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('business_id')
                    ->label('Business')
                    ->options(Business::where('id', '!=', 1)->pluck('name', 'id'))
                    ->searchable()
                    ->multiple(),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'processed' => 'Processed',
                        'cancelled' => 'Cancelled',
                    ]),
            ])
            ->actions([
                ViewAction::make()
                    ->url(fn (BankSchedule $record): string => route('bank-schedules.show', $record)),
                EditAction::make()
                    ->url(fn (BankSchedule $record): string => route('bank-schedules.edit', $record))
                    ->color('warning')
                    ->visible(fn() => in_array('Manage Bank Schedules', auth()->user()->permissions ?? [])),
                DeleteAction::make()
                    ->successNotificationTitle('Bank schedule deleted successfully.')
                    ->visible(fn() => in_array('Manage Bank Schedules', auth()->user()->permissions ?? [])),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Create Bank Schedule')
                    ->url(route('bank-schedules.create'))
                    ->color('success')
                    ->visible(fn() => in_array('Manage Bank Schedules', auth()->user()->permissions ?? [])),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('30s');
    }

    public function render(): View
    {
        return view('livewire.bank-schedules.list-bank-schedules');
    }

    public function makeFilamentTranslatableContentDriver(): ?\Filament\Support\Contracts\TranslatableContentDriver
    {
        return null;
    }
}
