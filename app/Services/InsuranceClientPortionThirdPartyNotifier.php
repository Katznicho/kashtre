<?php

namespace App\Services;

use App\Models\InsuranceCompany;
use App\Models\Invoice;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;

/**
 * When an insurance client's portion is paid on Kashtre, records the payment on the third-party
 * insurer app (payments list + client account). Used after MM confirmation (cron) and for cash.
 */
class InsuranceClientPortionThirdPartyNotifier
{
    public static function notifyIfApplicable(Invoice $invoice, Transaction $transaction): void
    {
        try {
            $client = $invoice->client;
            if (!$client || !$client->insurance_company_id) {
                return;
            }

            $snapshot = $invoice->insurance_authorization_snapshot;
            if (!is_array($snapshot) || empty($snapshot)) {
                return;
            }

            $localInsurance = InsuranceCompany::find($client->insurance_company_id);
            if (!$localInsurance || !$localInsurance->third_party_business_id) {
                return;
            }

            $policyNumber = trim((string) ($client->policy_number ?? ''));
            if ($policyNumber === '') {
                return;
            }

            $clientPortion = isset($snapshot['client_total'])
                ? (float) $snapshot['client_total']
                : (float) ($invoice->insurance_client_total ?? $invoice->total_amount);

            if ($clientPortion <= 0) {
                return;
            }

            $paymentReference = 'CP-' . $transaction->created_at->format('Ymd') . '-' . $transaction->id;

            $authorizationReference = $invoice->insurance_authorization_reference
                ?? ($snapshot['authorization_reference'] ?? null);

            $method = $transaction->method ?? 'mobile_money';
            if (!in_array($method, ['cash', 'bank_transfer', 'mobile_money', 'cheque', 'card', 'credit', 'other'], true)) {
                $method = 'mobile_money';
            }

            $payload = [
                'insurance_company_id' => (int) $localInsurance->third_party_business_id,
                'policy_number' => $policyNumber,
                'amount' => $clientPortion,
                'payment_reference' => $paymentReference,
                'kashtre_invoice_id' => (string) $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'authorization_reference' => $authorizationReference,
                'connected_business_id' => $invoice->business_id,
                'payment_method' => $method,
                'mobile_money_number' => $transaction->phone_number ?? null,
                'payment_date' => now()->format('Y-m-d'),
            ];

            $service = new ThirdPartyApiService();
            $result = $service->recordClientPortionPayment($payload);

            Log::info('InsuranceClientPortionThirdPartyNotifier: third-party record-client-portion', [
                'invoice_id' => $invoice->id,
                'transaction_id' => $transaction->id,
                'success' => $result['success'] ?? false,
                'message' => $result['message'] ?? null,
            ]);
        } catch (\Throwable $e) {
            Log::error('InsuranceClientPortionThirdPartyNotifier: exception', [
                'invoice_id' => $invoice->id ?? null,
                'transaction_id' => $transaction->id ?? null,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
