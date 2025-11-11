<?php

namespace App\Livewire\Refunds;

use App\Models\CreditNote;
use App\Services\CreditNoteService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Throwable;
use Livewire\Component;

class ListRefunds extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        $user = Auth::user();

        $query = CreditNote::query()
            ->with([
                'client',
                'invoice',
                'serviceDeliveryQueue.item',
                'business',
                'branch',
                'approvals',
            ])
            ->orderByDesc('created_at');

        if ($user && $user->business_id !== 1) {
            $query->where('business_id', $user->business_id);
        }

        $isKashtre = $user && $user->business_id === 1;

        return $table
            ->query($query)
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Requested')
                    ->dateTime('M d, Y g:i A')
                    ->sortable(),

                Tables\Columns\TextColumn::make('credit_note_number')
                    ->label('Credit Note')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('serviceDeliveryQueue.item_name')
                    ->label('Item')
                    ->wrap(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->money('UGX', true)
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'approved',
                        'success' => 'processed',
                        'danger' => 'cancelled',
                    ])
                    ->label('Status')
                    ->formatStateUsing(fn (string $state) => ucfirst(str_replace('_', ' ', $state))),

                Tables\Columns\BadgeColumn::make('current_stage')
                    ->label('Stage')
                    ->colors([
                        'warning' => CreditNote::STAGE_SUPERVISOR,
                        'info' => CreditNote::STAGE_AUTHORIZER,
                        'primary' => CreditNote::STAGE_APPROVER,
                        'success' => 'refunded',
                        'secondary' => 'approved',
                        'danger' => 'cancelled',
                    ])
                    ->formatStateUsing(function (?string $state): string {
                        return match ($state) {
                            CreditNote::STAGE_SUPERVISOR => 'Supervisor Review',
                            CreditNote::STAGE_AUTHORIZER => 'Authorizer Review',
                            CreditNote::STAGE_APPROVER => 'Approver Final Sign-off',
                            'approved' => 'Approved',
                            'refunded' => 'Refunded',
                            'cancelled' => 'Cancelled',
                            default => 'Pending',
                        };
                    }),

                Tables\Columns\TextColumn::make('client.name')
                    ->label('Client')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('serviceDeliveryQueue.servicePoint.name')
                    ->label('Service Point')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('invoice.invoice_number')
                    ->label('Invoice')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('business.name')
                    ->label('Business')
                    ->toggleable(isToggledHiddenByDefault: ! $isKashtre),

                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Branch')
                    ->toggleable(isToggledHiddenByDefault: ! $isKashtre),
            ])
            ->filters([
                Tables\Filters\Filter::make('date')
                    ->form([
                        DatePicker::make('from')->label('From'),
                        DatePicker::make('to')->label('To'),
                    ])
                    ->query(function ($query, array $data) {
                        if (! empty($data['from'])) {
                            $query->whereDate('created_at', '>=', $data['from']);
                        }

                        if (! empty($data['to'])) {
                            $query->whereDate('created_at', '<=', $data['to']);
                        }
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if (! empty($data['from'])) {
                            $indicators[] = 'From: ' . $data['from'];
                        }
                        if (! empty($data['to'])) {
                            $indicators[] = 'To: ' . $data['to'];
                        }

                        return $indicators;
                    }),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'processed' => 'Processed',
                        'cancelled' => 'Cancelled',
                    ]),

                Tables\Filters\SelectFilter::make('servicePoint')
                    ->label('Service Point')
                    ->relationship(
                        'serviceDeliveryQueue.servicePoint',
                        'name',
                        fn ($query) => $user && $user->business_id !== 1
                            ? $query->where('business_id', $user->business_id)
                            : $query
                    )
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-m-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->form([
                        Textarea::make('notes')
                            ->label('Notes (optional)')
                            ->rows(3)
                            ->maxLength(500),
                    ])
                    ->visible(fn (CreditNote $record): bool => $record->userCanApprove(Auth::user()))
                    ->action(function (CreditNote $record, array $data): void {
                        $approval = $record->pendingApprovalForUser(Auth::user());

                        if (! $approval) {
                            Notification::make()
                                ->title('You have no actions pending for this refund.')
                                ->warning()
                                ->send();

                            return;
                        }

                        try {
                            app(CreditNoteService::class)->approve($approval, Auth::user(), $data['notes'] ?? null);

                            Notification::make()
                                ->title('Approval recorded')
                                ->success()
                                ->send();
                        } catch (Throwable $e) {
                            Notification::make()
                                ->title('Failed to approve refund')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-m-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->form([
                        Textarea::make('notes')
                            ->label('Reason')
                            ->rows(3)
                            ->maxLength(500)
                            ->required(),
                    ])
                    ->visible(fn (CreditNote $record): bool => $record->userCanApprove(Auth::user()))
                    ->action(function (CreditNote $record, array $data): void {
                        $approval = $record->pendingApprovalForUser(Auth::user());

                        if (! $approval) {
                            Notification::make()
                                ->title('You have no actions pending for this refund.')
                                ->warning()
                                ->send();

                            return;
                        }

                        try {
                            app(CreditNoteService::class)->reject($approval, Auth::user(), $data['notes'] ?? null);

                            Notification::make()
                                ->title('Refund rejected')
                                ->success()
                                ->send();
                        } catch (Throwable $e) {
                            Notification::make()
                                ->title('Failed to reject refund')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Tables\Actions\Action::make('invoice')
                    ->label('Invoice')
                    ->icon('heroicon-m-document-text')
                    ->url(fn (CreditNote $record): string => route('invoices.show', $record->invoice_id))
                    ->openUrlInNewTab()
                    ->visible(fn (CreditNote $record): bool => ! empty($record->invoice_id)),

                Tables\Actions\Action::make('client')
                    ->label('Client')
                    ->icon('heroicon-m-user')
                    ->url(fn (CreditNote $record): string => route('clients.show', $record->client_id))
                    ->visible(fn (CreditNote $record): bool => ! empty($record->client_id)),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('export')
                    ->label('Export Selected')
                    ->icon('heroicon-m-arrow-down-tray')
                    ->action(fn () => null)
                    ->requiresConfirmation()
                    ->disabled(),
            ])
            ->emptyStateIcon('heroicon-o-arrow-uturn-left')
            ->emptyStateHeading('No refund requests yet')
            ->emptyStateDescription('Refunds will appear as soon as items are flagged as not done.');
    }

    public function render(): View
    {
        return view('livewire.refunds.list-refunds');
    }
}

