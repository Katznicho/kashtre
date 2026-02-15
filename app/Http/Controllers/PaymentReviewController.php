<?php

namespace App\Http\Controllers;

use App\Models\ThirdPartyPayerBalanceHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PaymentReviewController extends Controller
{
    /**
     * Display a listing of payments pending review
     */
    public function index()
    {
        $pendingPayments = ThirdPartyPayerBalanceHistory::where('transaction_type', 'credit')
            ->where('payment_status', 'pending_review')
            ->with(['invoice', 'client', 'business', 'branch', 'thirdPartyPayer'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('payment-reviews.index', compact('pendingPayments'));
    }

    /**
     * Show details of a specific payment review
     */
    public function show($id)
    {
        $payment = ThirdPartyPayerBalanceHistory::with(['invoice', 'client', 'business', 'branch', 'thirdPartyPayer'])
            ->findOrFail($id);

        if ($payment->payment_status !== 'pending_review') {
            return redirect()->route('payment-reviews.index')
                ->with('error', 'This payment is not pending review.');
        }

        return view('payment-reviews.show', compact('payment'));
    }

    /**
     * Approve a payment
     */
    public function approve(Request $request, $id)
    {
        $payment = ThirdPartyPayerBalanceHistory::findOrFail($id);

        if ($payment->payment_status !== 'pending_review') {
            return back()->with('error', 'This payment is not pending review.');
        }

        // Update payment status to paid
        $payment->update([
            'payment_status' => 'paid',
        ]);

        // Update all related debit entries for this invoice
        ThirdPartyPayerBalanceHistory::where('invoice_id', $payment->invoice_id)
            ->where('third_party_payer_id', $payment->third_party_payer_id)
            ->where('transaction_type', 'debit')
            ->update(['payment_status' => 'paid']);

        Log::info('Payment approved', [
            'payment_id' => $id,
            'invoice_id' => $payment->invoice_id,
            'approved_by' => auth()->id(),
        ]);

        return redirect()->route('payment-reviews.index')
            ->with('success', 'Payment approved successfully.');
    }

    /**
     * Reject a payment
     */
    public function reject(Request $request, $id)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $payment = ThirdPartyPayerBalanceHistory::findOrFail($id);

        if ($payment->payment_status !== 'pending_review') {
            return back()->with('error', 'This payment is not pending review.');
        }

        // Update payment status to rejected
        $payment->update([
            'payment_status' => 'rejected',
            'notes' => ($payment->notes ?? '') . ' | REJECTED: ' . $validated['rejection_reason'],
        ]);

        // Revert the balance change
        $thirdPartyPayer = $payment->thirdPartyPayer;
        $currentBalance = $thirdPartyPayer->current_balance ?? 0;
        $newBalance = $currentBalance - $payment->change_amount; // Subtract the credit
        
        $thirdPartyPayer->update(['current_balance' => $newBalance]);

        Log::info('Payment rejected', [
            'payment_id' => $id,
            'invoice_id' => $payment->invoice_id,
            'rejection_reason' => $validated['rejection_reason'],
            'rejected_by' => auth()->id(),
        ]);

        return redirect()->route('payment-reviews.index')
            ->with('success', 'Payment rejected successfully.');
    }

    /**
     * Download proof of payment
     */
    public function downloadProof($id)
    {
        $payment = ThirdPartyPayerBalanceHistory::findOrFail($id);

        if (!$payment->proof_of_payment_path) {
            abort(404, 'Proof of payment not found.');
        }

        $filePath = storage_path('app/public/' . $payment->proof_of_payment_path);

        if (!file_exists($filePath)) {
            abort(404, 'Proof of payment file not found.');
        }

        return response()->download($filePath);
    }
}
