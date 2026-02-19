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
        
        // Check if this insurance company has a third_party_business_id (links to third-party system)
        // The API uses the third-party business ID to find invoices, not the Kashtre insurance company ID
        $thirdPartyBusinessId = $insuranceCompany->third_party_business_id;
        if ($thirdPartyBusinessId) {
            $this->info("Third-party business ID: {$thirdPartyBusinessId}");
            $this->info("Note: The API uses this ID ({$thirdPartyBusinessId}) to query invoices, not the Kashtre ID ({$insuranceCompany->id})");
        }
        
        // Find all third-party payers for this insurance company
        // Try both the Kashtre insurance company ID and the third-party business ID
        $thirdPartyPayers = ThirdPartyPayer::where('insurance_company_id', $insuranceCompany->id)
            ->where('type', 'insurance_company')
            ->where('status', 'active')
            ->get();
        
        // If no payers found with Kashtre ID, try finding by third-party business ID
        // by finding insurance companies with that third_party_business_id
        if ($thirdPartyPayers->isEmpty() && $thirdPartyBusinessId) {
            $this->info("No third-party payers found with Kashtre insurance company ID. Trying third-party business ID...");
            $matchingInsuranceCompanies = InsuranceCompany::where('third_party_business_id', $thirdPartyBusinessId)->pluck('id');
            if (!$matchingInsuranceCompanies->isEmpty()) {
                $thirdPartyPayers = ThirdPartyPayer::whereIn('insurance_company_id', $matchingInsuranceCompanies)
                    ->where('type', 'insurance_company')
                    ->where('status', 'active')
                    ->get();
                if (!$thirdPartyPayers->isEmpty()) {
                    $this->info("Found {$thirdPartyPayers->count()} third-party payer(s) using third-party business ID.");
                }
            }
        }
        
        // Also find invoices through clients with this insurance company
        $clientsWithInsurance = \App\Models\Client::where('insurance_company_id', $insuranceCompany->id)->pluck('id');
        
        // If no clients found, try to find invoices by checking all invoices and their payment methods
        // Some invoices might have insurance in payment_methods array but clients don't have insurance_company_id set
        if ($clientsWithInsurance->isEmpty()) {
            $this->info("No clients found with insurance_company_id. Checking invoices with insurance in payment_methods...");
            
            // Find invoices where payment_methods contains 'insurance'
            $invoicesWithInsurance = Invoice::whereJsonContains('payment_methods', 'insurance')->get();
            
            if (!$invoicesWithInsurance->isEmpty()) {
                $this->info("Found {$invoicesWithInsurance->count()} invoice(s) with insurance in payment_methods.");
                $this->info("These invoices may need to be reset manually or through the API endpoint.");
                $thirdPartyBusinessId = $insuranceCompany->third_party_business_id ?? 'N/A';
                $this->info("To reset via API, use the third-party system's insurance company ID: {$thirdPartyBusinessId}");
            }
        }
        
        $totalInvoicesReset = 0;
        $totalPaymentsDeleted = 0;
        
        // Method 1: Find invoices through third-party payers and balance history
        if (!$thirdPartyPayers->isEmpty()) {
            $this->info("Found {$thirdPartyPayers->count()} third-party payer(s)");
            
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
                
                if ($totalInvoicesReset > 0) {
                    $this->info("\n✅ Successfully reset invoices!");
                    $this->info("   - Invoices reset: {$totalInvoicesReset}");
                    $this->info("   - Payment entries deleted: {$totalPaymentsDeleted}");
                    return 0;
                }
            } catch (\Exception $e) {
                DB::rollBack();
                $this->error("Error resetting invoices: " . $e->getMessage());
                return 1;
            }
        }
        
        // Method 2: Find invoices directly through clients
        if (!$clientsWithInsurance->isEmpty()) {
            $this->info("Found {$clientsWithInsurance->count()} client(s) with this insurance company.");
            
            // Find invoices for these clients
            $invoices = Invoice::whereIn('client_id', $clientsWithInsurance)->get();
            
            if ($invoices->isEmpty()) {
                $this->warn("No invoices found for clients with this insurance company.");
            } else {
                $this->info("Found {$invoices->count()} invoice(s) to reset.");
                
                DB::beginTransaction();
                
                try {
                    // Get invoice IDs
                    $invoiceIds = $invoices->pluck('id');
                    
                    // Find and delete credit entries in balance history for these invoices
                    $creditEntries = ThirdPartyPayerBalanceHistory::whereIn('invoice_id', $invoiceIds)
                        ->where('transaction_type', 'credit')
                        ->get();
                    
                    if ($creditEntries->count() > 0) {
                        $this->info("Deleting {$creditEntries->count()} payment entry/entries from balance history...");
                        ThirdPartyPayerBalanceHistory::whereIn('invoice_id', $invoiceIds)
                            ->where('transaction_type', 'credit')
                            ->delete();
                        $totalPaymentsDeleted += $creditEntries->count();
                    }
                    
                    // Update invoices
                    foreach ($invoices as $invoice) {
                        $invoice->update([
                            'amount_paid' => 0,
                            'balance_due' => $invoice->total_amount,
                            'payment_status' => 'pending_payment',
                        ]);
                        $totalInvoicesReset++;
                    }
                    
                    // Update debit entries
                    ThirdPartyPayerBalanceHistory::whereIn('invoice_id', $invoiceIds)
                        ->where('transaction_type', 'debit')
                        ->update(['payment_status' => 'pending_payment']);
                    
                    DB::commit();
                    
                    $this->info("\n✅ Successfully reset {$totalInvoicesReset} invoice(s) to unpaid status!");
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
                    return 1;
                }
            }
        }
        
        // Method 3: Try to find invoices through balance history with client relationship
        $this->info("Trying to find invoices through balance history...");
        
        $balanceHistories = ThirdPartyPayerBalanceHistory::whereHas('invoice.client', function($q) use ($insuranceCompany) {
            $q->where('insurance_company_id', $insuranceCompany->id);
        })->where('transaction_type', 'debit')->get();
        
        // Method 4: If still not found, try finding invoices by checking all invoices with clients that have this insurance company
        if ($balanceHistories->isEmpty()) {
            $this->info("Trying alternative method: Finding invoices through client insurance company relationship...");
            
            // Find all invoices where the client has this insurance company
            $invoices = Invoice::whereHas('client', function($q) use ($insuranceCompany) {
                $q->where('insurance_company_id', $insuranceCompany->id);
            })->get();
            
            if ($invoices->isEmpty()) {
                $this->warn("No invoices found for this insurance company.");
                $this->info("\nDebugging info:");
                $this->info("  - Insurance Company ID: {$insuranceCompany->id}");
                $this->info("  - Insurance Company Code: {$code}");
                $this->info("  - Clients with this insurance company: " . \App\Models\Client::where('insurance_company_id', $insuranceCompany->id)->count());
                $this->info("  - Total invoices in system: " . Invoice::count());
                return 0;
            }
            
            $this->info("Found {$invoices->count()} invoice(s) through client relationship.");
            
            DB::beginTransaction();
            
            try {
                $invoiceIds = $invoices->pluck('id');
                
                // Find and delete credit entries
                $creditEntries = ThirdPartyPayerBalanceHistory::whereIn('invoice_id', $invoiceIds)
                    ->where('transaction_type', 'credit')
                    ->get();
                
                if ($creditEntries->count() > 0) {
                    $this->info("Deleting {$creditEntries->count()} payment entry/entries...");
                    ThirdPartyPayerBalanceHistory::whereIn('invoice_id', $invoiceIds)
                        ->where('transaction_type', 'credit')
                        ->delete();
                    $totalPaymentsDeleted += $creditEntries->count();
                }
                
                // Update invoices
                foreach ($invoices as $invoice) {
                    $invoice->update([
                        'amount_paid' => 0,
                        'balance_due' => $invoice->total_amount,
                        'payment_status' => 'pending_payment',
                    ]);
                    $totalInvoicesReset++;
                }
                
                // Update debit entries
                ThirdPartyPayerBalanceHistory::whereIn('invoice_id', $invoiceIds)
                    ->where('transaction_type', 'debit')
                    ->update(['payment_status' => 'pending_payment']);
                
                DB::commit();
                
                $this->info("\n✅ Successfully reset {$totalInvoicesReset} invoice(s) to unpaid status!");
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
                return 1;
            }
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
                $totalPaymentsDeleted += $creditEntries->count();
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
                    $totalInvoicesReset++;
                }
            }
            
            // Update debit entries
            ThirdPartyPayerBalanceHistory::whereIn('invoice_id', $invoiceIds)
                ->where('transaction_type', 'debit')
                ->update(['payment_status' => 'pending_payment']);
            
            DB::commit();
            
            $this->info("\n✅ Successfully reset {$totalInvoicesReset} invoice(s) to unpaid status!");
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
