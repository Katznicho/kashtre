<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    public function up(): void
    {
        // Fix ThirdPartyPayer records where insurance_company_id stores a third-party vendor ID
        // instead of the local Kashtre InsuranceCompany.id.
        //
        // Detection: if a ThirdPartyPayer's insurance_company_id also appears as a
        // third_party_business_id in insurance_companies for the same business, it means
        // the vendor ID was stored directly — find the correct local IC and update it.

        $payers = DB::table('third_party_payers')
            ->whereNull('client_id')
            ->whereNull('deleted_at')
            ->where('type', 'insurance_company')
            ->get();

        foreach ($payers as $payer) {
            $isVendorId = DB::table('insurance_companies')
                ->where('third_party_business_id', $payer->insurance_company_id)
                ->where('business_id', $payer->business_id)
                ->whereNull('deleted_at')
                ->exists();

            if (!$isVendorId) {
                continue;
            }

            $correctIc = DB::table('insurance_companies')
                ->where('third_party_business_id', $payer->insurance_company_id)
                ->where('business_id', $payer->business_id)
                ->whereNull('deleted_at')
                ->orderBy('id')
                ->first();

            if ($correctIc) {
                DB::table('third_party_payers')
                    ->where('id', $payer->id)
                    ->update(['insurance_company_id' => $correctIc->id]);

                Log::info('Fixed third_party_payer insurance_company_id', [
                    'payer_id' => $payer->id,
                    'payer_name' => $payer->name,
                    'old_insurance_company_id' => $payer->insurance_company_id,
                    'new_insurance_company_id' => $correctIc->id,
                    'business_id' => $payer->business_id,
                ]);
            }
        }
    }

    public function down(): void
    {
        // Not reversible — this fixes corrupted data.
    }
};
