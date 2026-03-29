<?php

use App\Http\Controllers\EmergencyController;

// Queue display board — migrated to standalone Calling Service
// API endpoints for TV are no longer served from the Kashtre monolith

// Public token-authenticated endpoint for the display board to get emergency color
Route::get('/display/emergency-status', [EmergencyController::class, 'displayEmergencyStatus']);

// Public token-authenticated endpoint for the display board to get PA config (sections + Reverb details)
Route::get('/display/pa-config', [\App\Http\Controllers\PaAnnouncementController::class, 'displayPaConfig']);
Route::post('/display/pa-signal', [\App\Http\Controllers\PaAnnouncementController::class, 'displaySignal']);
Route::options('/display/pa-config', fn () => response()->noContent()->withHeaders([
    'Access-Control-Allow-Origin' => '*',
    'Access-Control-Allow-Methods' => 'GET, OPTIONS',
    'Access-Control-Allow-Headers' => 'Content-Type',
]));
Route::options('/display/pa-signal', fn () => response()->noContent()->withHeaders([
    'Access-Control-Allow-Origin' => '*',
    'Access-Control-Allow-Methods' => 'POST, OPTIONS',
    'Access-Control-Allow-Headers' => 'Content-Type',
]));



Route::prefix('v1')->group(function () {
    include_once __DIR__ . '/custom/airtel_routes.php';
    include_once __DIR__ . '/custom/mtn_routes.php';

    // Invoice API routes for third-party vendors
    Route::get('/invoices/insurance-company/{insuranceCompanyId}', [\App\Http\Controllers\API\InvoiceController::class, 'getInvoicesForInsuranceCompany']);
    Route::post('/invoices/{invoiceId}/mark-paid', [\App\Http\Controllers\API\InvoiceController::class, 'markInvoiceAsPaid']);
    Route::get('/invoices/{invoiceId}/details', [\App\Http\Controllers\API\InvoiceController::class, 'getInvoiceDetails']);

    // Client deductible tracking
    Route::get('/clients/{client}/deductible-used', [\App\Http\Controllers\API\ClientController::class, 'getDeductibleUsed']);

    // Client co-pay status tracking
    Route::get('/clients/{client}/copay-status', [\App\Http\Controllers\API\ClientController::class, 'getCopayPaidStatus']);
});
