<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Item;
use App\Models\ThirdPartyPayer;

class ThirdPartyPayerController extends Controller
{
    /**
     * Return excluded items for a given business + insurance company.
     */
    public function getExcludedItems(int $businessId, int $insuranceCompanyId)
    {
        $thirdPartyPayer = ThirdPartyPayer::where('business_id', $businessId)
            ->where('insurance_company_id', $insuranceCompanyId)
            ->where('type', 'insurance_company')
            ->whereNull('client_id')
            ->where('status', 'active')
            ->first();

        if (!$thirdPartyPayer || empty($thirdPartyPayer->excluded_items)) {
            return response()->json([
                'success' => true,
                'data' => [],
            ]);
        }

        $items = Item::where('business_id', $businessId)
            ->whereIn('id', $thirdPartyPayer->excluded_items)
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'type']);

        return response()->json([
            'success' => true,
            'data' => $items,
        ]);
    }
}

