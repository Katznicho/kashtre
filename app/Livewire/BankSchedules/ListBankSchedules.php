<?php

namespace App\Livewire\BankSchedules;

use App\Models\BankSchedule;
use App\Models\Business;
use App\Services\BankScheduleProcessingService;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\BulkAction;
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

    public string $activeTab = 'pending';

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
        
        // Force refresh the table data
        $this->resetTable();
    }

    public function table(Table $table): Table
    {
        // Only Kashtre users can access
        if (auth()->user()->business_id !== 1) {
            abort(403, 'Access denied. Bank schedules are only accessible to Kashtre administrators.');
        }

        $query = BankSchedule::query()
            ->with(['business', 'withdrawalRequest', 'creator'])
            ->when($this->activeTab === 'pending', function ($query) {
                return $query->where('status', 'pending');
            })
            ->when($this->activeTab === 'completed', function ($query) {
                return $query->where('status', 'processed');
            })
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

                TextColumn::make('withdrawal_charge')
                    ->label('Charge')
                    ->money('UGX')
                    ->sortable()
                    ->alignRight()
                    ->toggleable(isToggledHiddenByDefault: false),

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
            ])
            ->actions([
                ViewAction::make()
                    ->url(fn (BankSchedule $record): string => route('bank-schedules.show', $record)),
                Action::make('markAsDone')
                    ->label('Mark as Done')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (BankSchedule $record) => $record->status === 'pending' && $this->activeTab === 'pending')
                    ->requiresConfirmation()
                    ->modalHeading('Mark Bank Schedule as Done')
                    ->modalDescription('Are you sure you want to mark this bank schedule as done? This will process the money transfer.')
                    ->modalSubmitActionLabel('Yes, mark as done')
                    ->action(function (BankSchedule $record) {
                        if ($record->status !== 'pending') {
                            \Filament\Notifications\Notification::make()
                                ->title('Invalid action')
                                ->body('Only pending bank schedules can be marked as done.')
                                ->warning()
                                ->send();
                            return;
                        }
                        
                        $processingService = app(BankScheduleProcessingService::class);
                        
                        try {
                            $processingService->processBankSchedule($record);
                            
                            \Filament\Notifications\Notification::make()
                                ->title('Bank schedule processed successfully')
                                ->success()
                                ->send();
                            
                            // Refresh the table
                            $this->resetTable();
                        } catch (\Exception $e) {
                            \Filament\Notifications\Notification::make()
                                ->title('Failed to process bank schedule')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->bulkActions([])
            ->headerActions([
                Action::make('markAllAsDone')
                    ->label('Mark All Pending as Done')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn() => $this->activeTab === 'pending')
                    ->requiresConfirmation()
                    ->modalHeading('Mark All Pending Bank Schedules as Done')
                    ->modalDescription('Are you sure you want to mark all pending bank schedules on this page as done? This will process the money transfers for all pending schedules.')
                    ->modalSubmitActionLabel('Yes, mark all as done')
                    ->action(function () {
                        try {
                            // Check if we're on the pending tab
                            if ($this->activeTab !== 'pending') {
                                \Filament\Notifications\Notification::make()
                                    ->title('Invalid action')
                                    ->body('You can only mark bank schedules as done from the Pending tab.')
                                    ->warning()
                                    ->send();
                                return;
                            }
                            
                            // Get all pending bank schedules from the current query
                            $bankSchedules = BankSchedule::where('status', 'pending')
                                ->get();
                            
                            if ($bankSchedules->isEmpty()) {
                                \Filament\Notifications\Notification::make()
                                    ->title('No pending schedules')
                                    ->body('There are no pending bank schedules to process.')
                                    ->warning()
                                    ->send();
                                return;
                            }
                            
                            $processingService = app(BankScheduleProcessingService::class);
                            $bankScheduleIds = $bankSchedules->pluck('id')->toArray();
                            
                            \Log::info('Starting bank schedule processing', [
                                'count' => count($bankScheduleIds),
                                'ids' => $bankScheduleIds
                            ]);
                            
                            $results = $processingService->processBankSchedules($bankScheduleIds);
                            
                            $successCount = count($results['success']);
                            $failedCount = count($results['failed']);
                            
                            \Log::info('Bank schedule processing completed', [
                                'success_count' => $successCount,
                                'failed_count' => $failedCount,
                                'results' => $results
                            ]);
                            
                            if ($successCount > 0) {
                                \Filament\Notifications\Notification::make()
                                    ->title("Successfully processed {$successCount} bank schedule(s)")
                                    ->success()
                                    ->send();
                            }
                            
                            if ($failedCount > 0) {
                                $errorMessages = collect($results['failed'])
                                    ->pluck('error')
                                    ->unique()
                                    ->implode(', ');
                                    
                                \Filament\Notifications\Notification::make()
                                    ->title("Failed to process {$failedCount} bank schedule(s)")
                                    ->body($errorMessages)
                                    ->danger()
                                    ->send();
                            } elseif ($successCount === 0) {
                                // If nothing succeeded and nothing failed, there might be an issue
                                \Filament\Notifications\Notification::make()
                                    ->title('No schedules were processed')
                                    ->body('No bank schedules were processed. Please check the logs for details.')
                                    ->warning()
                                    ->send();
                            }
                            
                            // Refresh the table
                            $this->resetTable();
                        } catch (\Exception $e) {
                            \Log::error('Error in markAllAsDone action', [
                                'error' => $e->getMessage(),
                                'trace' => $e->getTraceAsString()
                            ]);
                            
                            \Filament\Notifications\Notification::make()
                                ->title('Error processing bank schedules')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
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
