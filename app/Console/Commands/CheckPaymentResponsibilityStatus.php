<?php

namespace App\Console\Commands;

use App\Models\MoneyTransfer;
use App\Models\Client;
use App\Payments\YoAPI;
use App\Services\MoneyTrackingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class CheckPaymentResponsibilityStatus extends Command
{
    protected $signature = 'payments:check-responsibility-status'; 
    protected $description = 'Check and update YoAPI payment statuses for payment responsibility payments (deductible/copay)';

    public function handle()
    {
        Log::info('=== CRON JOB STARTED: CheckPaymentResponsibilityStatus ===', [
            'timestamp' => now(),
            'command' => 'payments:check-responsibility-status',
            'server' => gethostname(),
            'php_version' => PHP_VERSION
        ]);

        // Get all pending payment responsibility MoneyTransfer records that have mobile money transaction references
        $pendingPayments = MoneyTransfer::where('status', 'pending')
            ->where('transfer_type', 'payment_received')
            ->whereNotNull('metadata')
            ->whereNotNull('reference')
            ->with(['client', 'business'])
            ->get()
            ->filter(function($transfer) {
                $metadata = $transfer->metadata ?? [];
                $hasPaymentResponsibility = isset($metadata['payment_responsibility_type']) && 
                    in_array($metadata['payment_responsibility_type'], ['deductible', 'copay']);
                $hasMobileMoney = isset($metadata['payment_method']) && 
                    $metadata['payment_method'] === 'mobile_money';
                $hasTransactionId = isset($metadata['transaction_reference']) || isset($metadata['transaction_id']);
                
                return $hasPaymentResponsibility && $hasMobileMoney && $hasTransactionId;
            });

        Log::info('Found pending payment responsibility mobile money payments', [
            'count' => $pendingPayments->count(),
            'payments' => $pendingPayments->map(function($p) {
                $metadata = $p->metadata ?? [];
                return [
                    'id' => $p->id,
                    'reference' => $p->reference,
                    'transaction_reference' => $metadata['transaction_reference'] ?? $metadata['transaction_id'] ?? null,
                    'payment_responsibility_type' => $metadata['payment_responsibility_type'] ?? null,
                    'amount' => $p->amount,
                    'client_id' => $p->client_id,
                    'created_at' => $p->created_at->toDateTimeString(),
                    'age_minutes' => now()->diffInMinutes($p->created_at),
                ];
            })->toArray()
        ]);

        if ($pendingPayments->isEmpty()) {
            Log::info('No pending payment responsibility mobile money payments found - CRON JOB EXITING');
            $this->info('No pending payments to check.');
            return;
        }

        $yoPayments = new YoAPI(
            config('payments.yo_username'),
            config('payments.yo_password')
        );

        $processedCount = 0;
        $completedCount = 0;
        $failedCount = 0;
        $timeoutCount = 0;

        foreach ($pendingPayments as $index => $payment) {
            try {
                $metadata = $payment->metadata ?? [];
                $transactionReference = $metadata['transaction_reference'] ?? $metadata['transaction_id'] ?? null;
                $paymentType = $metadata['payment_responsibility_type'] ?? 'unknown';
                $paymentAge = now()->diffInMinutes($payment->created_at);

                Log::info("=== PROCESSING PAYMENT RESPONSIBILITY PAYMENT " . ($index + 1) . " OF " . $pendingPayments->count() . " ===", [
                    'payment_id' => $payment->id,
                    'reference' => $payment->reference,
                    'transaction_reference' => $transactionReference,
                    'payment_type' => $paymentType,
                    'amount' => $payment->amount,
                    'client_id' => $payment->client_id,
                    'created_at' => $payment->created_at->toDateTimeString(),
                    'age_minutes' => $paymentAge
                ]);

                // Timeout logic: fail payments after 5 minutes
                if ($paymentAge >= 5) {
                    Log::warning("Payment responsibility payment {$payment->id} has timed out after {$paymentAge} minutes - marking as failed", [
                        'payment_id' => $payment->id,
                        'reference' => $payment->reference,
                        'age_minutes' => $paymentAge,
                    ]);

                    DB::beginTransaction();
                    try {
                        $payment->update([
                            'status' => 'failed',
                            'metadata' => array_merge($metadata, [
                                'timeout_reason' => "Timed out after {$paymentAge} minutes",
                                'timeout_at' => now()->toDateTimeString(),
                            ])
                        ]);

                        DB::commit();
                        $timeoutCount++;
                        $processedCount++;
                        
                        Log::info("Payment responsibility payment {$payment->id} marked as failed due to timeout", [
                            'payment_id' => $payment->id,
                            'reference' => $payment->reference,
                        ]);
                    } catch (\Exception $e) {
                        DB::rollBack();
                        Log::error("Error marking payment responsibility payment as failed (timeout)", [
                            'payment_id' => $payment->id,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                        ]);
                    }
                    continue;
                }

                if (!$transactionReference) {
                    Log::warning("Payment responsibility payment {$payment->id} has no transaction reference - skipping", [
                        'payment_id' => $payment->id,
                        'reference' => $payment->reference,
                    ]);
                    continue;
                }

                // Check payment status with YoAPI
                Log::info("Checking payment responsibility payment status with YoAPI", [
                    'payment_id' => $payment->id,
                    'transaction_reference' => $transactionReference,
                ]);

                $statusCheck = $yoPayments->ac_transaction_check_status($transactionReference);

                Log::info("YoAPI status check response for payment responsibility payment", [
                    'payment_id' => $payment->id,
                    'transaction_reference' => $transactionReference,
                    'yo_api_response' => $statusCheck,
                ]);

                if (!isset($statusCheck['Status'])) {
                    Log::warning("Invalid YoAPI response for payment responsibility payment", [
                        'payment_id' => $payment->id,
                        'transaction_reference' => $transactionReference,
                        'response' => $statusCheck,
                    ]);
                    continue;
                }

                $yoStatus = strtoupper($statusCheck['Status'] ?? '');
                $yoStatusCode = $statusCheck['StatusCode'] ?? null;
                $yoStatusMessage = $statusCheck['StatusMessage'] ?? '';

                DB::beginTransaction();
                try {
                    if ($yoStatus === 'SUCCEEDED' || $yoStatusCode === 'SUCCEEDED') {
                        // Payment succeeded - mark as completed
                        $payment->update([
                            'status' => 'completed',
                            'processed_at' => now(),
                            'metadata' => array_merge($metadata, [
                                'yo_status' => $yoStatus,
                                'yo_status_code' => $yoStatusCode,
                                'yo_status_message' => $yoStatusMessage,
                                'completed_at' => now()->toDateTimeString(),
                                'last_status_check' => now()->toDateTimeString(),
                            ])
                        ]);

                        // Update client's deductible/copay status if needed
                        if ($payment->client_id) {
                            $client = Client::find($payment->client_id);
                            if ($client) {
                                // The API endpoint will recalculate deductible/copay status
                                // We just need to ensure the payment is marked as completed
                                Log::info("Payment responsibility payment completed - client status will be recalculated on next check", [
                                    'payment_id' => $payment->id,
                                    'client_id' => $client->id,
                                    'payment_type' => $paymentType,
                                ]);
                            }
                        }

                        DB::commit();
                        $completedCount++;
                        $processedCount++;
                        
                        Log::info("Payment responsibility payment {$payment->id} marked as completed", [
                            'payment_id' => $payment->id,
                            'reference' => $payment->reference,
                            'payment_type' => $paymentType,
                            'amount' => $payment->amount,
                        ]);

                    } elseif ($yoStatus === 'FAILED' || $yoStatusCode === 'FAILED') {
                        // Payment failed
                        $payment->update([
                            'status' => 'failed',
                            'metadata' => array_merge($metadata, [
                                'yo_status' => $yoStatus,
                                'yo_status_code' => $yoStatusCode,
                                'yo_status_message' => $yoStatusMessage,
                                'failed_at' => now()->toDateTimeString(),
                                'failure_reason' => $yoStatusMessage ?: 'Payment failed',
                                'last_status_check' => now()->toDateTimeString(),
                            ])
                        ]);

                        DB::commit();
                        $failedCount++;
                        $processedCount++;
                        
                        Log::info("Payment responsibility payment {$payment->id} marked as failed", [
                            'payment_id' => $payment->id,
                            'reference' => $payment->reference,
                            'failure_reason' => $yoStatusMessage,
                        ]);

                    } else {
                        // Still pending - just update metadata with latest check
                        $payment->update([
                            'metadata' => array_merge($metadata, [
                                'yo_status' => $yoStatus,
                                'yo_status_code' => $yoStatusCode,
                                'yo_status_message' => $yoStatusMessage,
                                'last_status_check' => now()->toDateTimeString(),
                            ])
                        ]);

                        DB::commit();
                        $processedCount++;
                        
                        Log::info("Payment responsibility payment {$payment->id} still pending - status updated", [
                            'payment_id' => $payment->id,
                            'reference' => $payment->reference,
                            'yo_status' => $yoStatus,
                        ]);
                    }

                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error("Error updating payment responsibility payment status", [
                        'payment_id' => $payment->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }

            } catch (\Exception $e) {
                Log::error("Error processing payment responsibility payment", [
                    'payment_id' => $payment->id ?? null,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        Log::info('=== CRON JOB COMPLETED: CheckPaymentResponsibilityStatus ===', [
            'timestamp' => now(),
            'processed_count' => $processedCount,
            'completed_count' => $completedCount,
            'failed_count' => $failedCount,
            'timeout_count' => $timeoutCount,
        ]);

        $this->info("Processed {$processedCount} payments: {$completedCount} completed, {$failedCount} failed, {$timeoutCount} timed out.");
    }
}
