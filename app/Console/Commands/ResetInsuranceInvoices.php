<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\InsuranceCompany;
use App\Models\ThirdPartyPayer;
use App\Models\ThirdPartyPayerBalanceHistory;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ResetInsuranceInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:reset-unpaid {code : Insurance company code}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset all invoices for an insurance company to unpaid status';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $code = strtoupper($this->argument('code'));
        
        $this->info("Resetting invoices for insurance company with code: {$code}");
        
        // Find insurance company
        $insuranceCompany = InsuranceCompany::where('code', $code)->first();
        
        if (!$insuranceCompany) {
            $this->error("Insurance company with code '{$code}' not found.");
            return 1;
        }
        
        $this->info("Found insurance company: {$insuranceCompany->name} (ID: {$insuranceCompany->id})");
        
        // Find all third-party payers for this insurance company
        $thirdPartyPayers = ThirdPartyPayer::where('insurance_company_id', $insuranceCompany->id)
            ->where('type', 'insurance_company')
            ->where('status', 'active')
            ->get();
        
        // Also try to find invoices through clients with this insurance company
        $clientsWithInsurance = \App\Models\Client::where('insurance_company_id', $insuranceCompany->id)->pluck('id');
        
        if ($thirdPartyPayers->isEmpty() && $clientsWithInsurance->isEmpty()) {
            $this->warn("No third-party payers or clients found for this insurance company.");
            $this->info("Trying to find invoices through balance history...");
            
            // Try to find balance history entries that might reference this insurance company
            // through invoice metadata or other means
            $balanceHistories = ThirdPartyPayerBalanceHistory::whereHas('invoice.client', function($q) use ($insuranceCompany) {
                $q->where('insurance_company_id', $insuranceCompany->id);
            })->where('transaction_type', 'debit')->get();
            
            if ($balanceHistories->isEmpty()) {
                $this->warn("No invoices found for this insurance company.");
                return 0;
            }
            
            $invoiceIds = $balanceHistories->pluck('invoice_id')->unique()->filter();
            $this->info("Found {$invoiceIds->count()} invoice(s) through balance history.");
            
            DB::beginTransaction();
            
            try {
                // Delete credit entries for these invoices
                $creditEntries = ThirdPartyPayerBalanceHistory::whereIn('invoice_id', $invoiceIds)
                    ->where('transaction_type', 'credit')
                    ->get();
                
                if ($creditEntries->count() > 0) {
                    $this->info("Deleting {$creditEntries->count()} payment entry/entries...");
                    ThirdPartyPayerBalanceHistory::whereIn('invoice_id', $invoiceIds)
                        ->where('transaction_type', 'credit')
                        ->delete();
                }
                
                // Update invoices
                foreach ($invoiceIds as $invoiceId) {
                    $invoice = Invoice::find($invoiceId);
                    if ($invoice) {
                        $invoice->update([
                            'amount_paid' => 0,
                            'balance_due' => $invoice->total_amount,
                            'payment_status' => 'pending_payment',
                        ]);
                    }
                }
                
                // Update debit entries
                ThirdPartyPayerBalanceHistory::whereIn('invoice_id', $invoiceIds)
                    ->where('transaction_type', 'debit')
                    ->update(['payment_status' => 'pending_payment']);
                
                DB::commit();
                
                $this->info("\n✅ Successfully reset {$invoiceIds->count()} invoice(s) to unpaid status!");
                return 0;
            } catch (\Exception $e) {
                DB::rollBack();
                $this->error("Error resetting invoices: " . $e->getMessage());
                return 1;
            }
        }
        
        // If we have clients but no third-party payers, reset invoices directly
        if ($thirdPartyPayers->isEmpty() && !$clientsWithInsurance->isEmpty()) {
            $this->info("Found {$clientsWithInsurance->count()} client(s) with this insurance company.");
            
            // Find invoices for these clients
            $invoices = Invoice::whereIn('client_id', $clientsWithInsurance)->get();
            
            if ($invoices->isEmpty()) {
                $this->warn("No invoices found for clients with this insurance company.");
                return 0;
            }
            
            $this->info("Found {$invoices->count()} invoice(s) to reset.");
            
            DB::beginTransaction();
            
            try {
                foreach ($invoices as $invoice) {
                    $invoice->update([
                        'amount_paid' => 0,
                        'balance_due' => $invoice->total_amount,
                        'payment_status' => 'pending_payment',
                    ]);
                }
                
                DB::commit();
                
                $this->info("\n✅ Successfully reset {$invoices->count()} invoice(s) to unpaid status!");
                
                return 0;
            } catch (\Exception $e) {
                DB::rollBack();
                $this->error("Error resetting invoices: " . $e->getMessage());
                return 1;
            }
        }
        
        $this->info("Found {$thirdPartyPayers->count()} third-party payer(s)");
        
        $totalInvoicesReset = 0;
        $totalPaymentsDeleted = 0;
        
        DB::beginTransaction();
        
        try {
            foreach ($thirdPartyPayers as $payer) {
                $this->line("Processing third-party payer: {$payer->name} (ID: {$payer->id})");
                
                // Get all invoice IDs for this payer
                $invoiceIds = ThirdPartyPayerBalanceHistory::where('third_party_payer_id', $payer->id)
                    ->where('transaction_type', 'debit')
                    ->whereNotNull('invoice_id')
                    ->distinct()
                    ->pluck('invoice_id')
                    ->filter();
                
                if ($invoiceIds->isEmpty()) {
                    $this->line("  No invoices found for this payer.");
                    continue;
                }
                
                $this->line("  Found {$invoiceIds->count()} invoice(s)");
                
                // Delete all credit entries (payments) for these invoices
                $creditEntries = ThirdPartyPayerBalanceHistory::where('third_party_payer_id', $payer->id)
                    ->where('transaction_type', 'credit')
                    ->whereIn('invoice_id', $invoiceIds)
                    ->get();
                
                $creditCount = $creditEntries->count();
                if ($creditCount > 0) {
                    $this->line("  Deleting {$creditCount} payment entry/entries...");
                    
                    // Calculate total credits to subtract from balance
                    $totalCredits = $creditEntries->sum('change_amount');
                    
                    // Delete credit entries
                    ThirdPartyPayerBalanceHistory::where('third_party_payer_id', $payer->id)
                        ->where('transaction_type', 'credit')
                        ->whereIn('invoice_id', $invoiceIds)
                        ->delete();
                    
                    $totalPaymentsDeleted += $creditCount;
                    
                    // Update third-party payer balance (add back the credits)
                    $payer->refresh();
                    $newBalance = $payer->current_balance + abs($totalCredits);
                    $payer->update(['current_balance' => $newBalance]);
                    
                    $this->line("  Updated payer balance: {$payer->current_balance} -> {$newBalance}");
                }
                
                // Update all invoices to unpaid status
                foreach ($invoiceIds as $invoiceId) {
                    $invoice = Invoice::find($invoiceId);
                    if ($invoice) {
                        $invoice->update([
                            'amount_paid' => 0,
                            'balance_due' => $invoice->total_amount,
                            'payment_status' => 'pending_payment',
                        ]);
                        $totalInvoicesReset++;
                    }
                }
                
                // Update payment_status on debit entries
                ThirdPartyPayerBalanceHistory::where('third_party_payer_id', $payer->id)
                    ->where('transaction_type', 'debit')
                    ->whereIn('invoice_id', $invoiceIds)
                    ->update(['payment_status' => 'pending_payment']);
                
                $this->line("  Reset {$invoiceIds->count()} invoice(s) to unpaid status");
            }
            
            DB::commit();
            
            $this->info("\n✅ Successfully reset invoices!");
            $this->info("   - Invoices reset: {$totalInvoicesReset}");
            $this->info("   - Payment entries deleted: {$totalPaymentsDeleted}");
            
            Log::info('Invoices reset to unpaid', [
                'insurance_company_id' => $insuranceCompany->id,
                'insurance_company_code' => $code,
                'invoices_reset' => $totalInvoicesReset,
                'payments_deleted' => $totalPaymentsDeleted,
            ]);
            
            return 0;
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            $this->error("Error resetting invoices: " . $e->getMessage());
            Log::error('Failed to reset invoices', [
                'insurance_company_id' => $insuranceCompany->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return 1;
        }
    }
}
