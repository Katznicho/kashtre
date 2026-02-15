<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixPaymentMethodEnum extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:payment-method-enum';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add insurance and credit_arrangement to business_balance_histories payment_method enum';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Adding insurance and credit_arrangement to business_balance_histories payment_method enum...');
        
        try {
            DB::statement("ALTER TABLE business_balance_histories MODIFY COLUMN payment_method ENUM('account_balance', 'mobile_money', 'bank_transfer', 'v_card', 'p_card', 'insurance', 'credit_arrangement') NULL DEFAULT 'mobile_money'");
            
            $this->info('✓ Successfully updated payment_method enum!');
            $this->info('The enum now includes: account_balance, mobile_money, bank_transfer, v_card, p_card, insurance, credit_arrangement');
            
            return 0;
        } catch (\Exception $e) {
            $this->error('✗ Failed to update payment_method enum: ' . $e->getMessage());
            return 1;
        }
    }
}
