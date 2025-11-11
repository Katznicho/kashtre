<?php


use App\Http\Controllers\BranchController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\TitleController;
use App\Http\Controllers\QualificationController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\ServicePointController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\ItemUnitController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ItemBulkUploadController;
use App\Http\Controllers\PackageBulkUploadController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\PatientCategoryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ContractorProfileController;
use App\Http\Controllers\ContractorProfileBulkUploadController;
use App\Http\Controllers\InsuranceCompanyController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\SubGroupController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\BulkUploadController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CompletedClientsController;
use App\Http\Controllers\DailyVisitsController;
use App\Http\Controllers\ServiceChargeController;
use App\Http\Controllers\ContractorServiceChargeController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\LocalPaymentController;

use App\Http\Controllers\PackageTrackingController;
use App\Http\Controllers\PackageSalesController;
use App\Http\Controllers\BalanceHistoryController;
use App\Http\Controllers\BusinessBalanceHistoryController;
use App\Http\Controllers\ContractorBalanceHistoryController;
use App\Http\Controllers\ServiceDeliveryController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\MoneyTrackingController;
use App\Http\Controllers\SuspenseAccountController;
use App\Http\Controllers\ServiceQueueController;
use App\Http\Controllers\ServiceDeliveryQueueController;
use App\Http\Controllers\TestingController;
use App\Http\Controllers\MaturationPeriodController;
use App\Http\Controllers\WithdrawalSettingController;
use App\Http\Controllers\BusinessWithdrawalSettingController;
use App\Http\Controllers\WithdrawalRequestController;
use App\Http\Controllers\CreditNoteWorkflowController;
use App\Http\Controllers\CreditNoteWorkflowBulkUploadController;
use App\Http\Controllers\RefundController;
use App\Http\Controllers\ServicePointSupervisorController;
use Illuminate\Support\Facades\Route;






/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::redirect('/', 'login');

// Route::get("makePayment",[PaymentController::class,"makePayment"])->name("makePayment");    

Route::middleware(['auth', 'verified'])->group(function () {

    // Route for the getting the data feed
    // Route::get('/json-data-feed', [DataFeedController::class, 'getDataFeed'])->name('json_data_feed');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::impersonate();



    Route::resource("businesses", BusinessController::class);
    Route::resource("branches", BranchController::class);
    Route::resource("support", SupportController::class);
    Route::resource("transactions", TransactionController::class);
    Route::get('/sales', [SalesController::class, 'index'])->name('sales.index');
    Route::get('/refunds', [RefundController::class, 'index'])->name('refunds.index');
    Route::resource("users", UserController::class);
    Route::resource("roles", RoleController::class);
    Route::resource("departments", DepartmentController::class);
    Route::resource("titles", TitleController::class);
    Route::resource("qualifications", QualificationController::class);
    Route::resource("rooms", RoomController::class);
    Route::resource("service-points", ServicePointController::class);
    Route::resource("service-queues", ServiceQueueController::class)->except(['create', 'store']);
    
    // Additional service queue routes
    Route::post('/service-queues/{serviceQueue}/start', [ServiceQueueController::class, 'startProcessing'])->name('service-queues.start');
    Route::post('/service-queues/{serviceQueue}/complete', [ServiceQueueController::class, 'complete'])->name('service-queues.complete');
    Route::post('/service-queues/{serviceQueue}/cancel', [ServiceQueueController::class, 'cancel'])->name('service-queues.cancel');
    Route::get('/service-queues/service-point/{servicePoint}/stats', [ServiceQueueController::class, 'getStats'])->name('service-queues.stats');
Route::get('/service-queues/service-point/{servicePoint}/queues', [ServiceQueueController::class, 'getServicePointQueues'])->name('service-queues.service-point-queues');

// Service Delivery Queue routes
Route::post('/service-delivery-queues/{serviceDeliveryQueue}/move-to-partially-done', [ServiceDeliveryQueueController::class, 'moveToPartiallyDone'])->name('service-delivery-queues.move-to-partially-done');
Route::post('/service-delivery-queues/{serviceDeliveryQueue}/move-to-completed', [ServiceDeliveryQueueController::class, 'moveToCompleted'])->name('service-delivery-queues.move-to-completed');

// Client Details routes
Route::get('/service-points/{servicePoint}/client/{clientId}/details', [ServicePointController::class, 'clientDetails'])->name('service-points.client-details');
Route::post('/service-points/{servicePoint}/client/{clientId}/update-statuses', [ServicePointController::class, 'updateClientItemStatuses'])->name('service-points.update-client-statuses');
Route::post('/service-points/{servicePoint}/client/{clientId}/update-statuses-and-process', [ServicePointController::class, 'updateStatusesAndProcessMoneyMovements'])->name('service-points.update-statuses-and-process-money');
Route::get('/service-delivery-queues/service-point/{servicePointId}/items', [ServiceDeliveryQueueController::class, 'getServicePointItems'])->name('service-delivery-queues.service-point-items');
Route::get('/service-delivery-queues/service-point/{servicePointId}/pending', [ServiceDeliveryQueueController::class, 'showPendingItems'])->name('service-delivery-queues.pending');
Route::get('/service-delivery-queues/service-point/{servicePointId}/completed', [ServiceDeliveryQueueController::class, 'showCompletedItems'])->name('service-delivery-queues.completed');

// Queue reset routes (for testing)
Route::post('/service-delivery-queues/service-point/{servicePointId}/reset', [ServiceDeliveryQueueController::class, 'resetServicePointQueues'])->name('service-delivery-queues.reset-service-point');
    
    Route::resource("sections", SectionController::class);
    Route::resource("item-units", ItemUnitController::class);
    
    // Items bulk operations (must come BEFORE items resource route)
    // Goods & Services Bulk Upload Routes
Route::get('/items/bulk-upload', [ItemBulkUploadController::class, 'index'])->name('items.bulk-upload');
Route::get('/items/bulk-upload/template', [ItemBulkUploadController::class, 'downloadTemplate'])->name('items.bulk-upload.template');
Route::post('/items/bulk-upload/import', [ItemBulkUploadController::class, 'import'])->name('items.bulk-upload.import');
Route::get('/items/bulk-upload/filtered-data', [ItemBulkUploadController::class, 'getFilteredData'])->name('items.bulk-upload.filtered-data');
Route::get('/items/bulk-upload/validation-guide', function() {
    return view('items.bulk-upload-validation-guide');
})->name('items.bulk-upload.validation-guide');

// Packages & Bulk Items Upload Routes
Route::get('/package-bulk-upload', [PackageBulkUploadController::class, 'index'])->name('package-bulk-upload.index');
Route::get('/package-bulk-upload/template', [PackageBulkUploadController::class, 'downloadTemplate'])->name('package-bulk-upload.template');
Route::post('/package-bulk-upload/import', [PackageBulkUploadController::class, 'import'])->name('package-bulk-upload.import');
    
    Route::get('/items/filtered-data', [ItemController::class, 'getFilteredData'])->name('items.filtered-data');
    Route::get('/items/generate-code', [ItemController::class, 'generateCode'])->name('items.generate-code');
    Route::resource("items", ItemController::class);
    
    Route::resource("groups", GroupController::class);
    Route::resource("patient-categories", PatientCategoryController::class);
    Route::resource("suppliers", SupplierController::class);
    Route::resource("contractor-profiles", ContractorProfileController::class);
    
    // Contractor Profile bulk operations
    Route::get('/contractor-profiles/bulk-upload', [ContractorProfileBulkUploadController::class, 'index'])->name('contractor-profiles.bulk-upload');
    Route::get('/contractor-profiles/bulk-upload/template', [ContractorProfileBulkUploadController::class, 'downloadTemplate'])->name('contractor-profiles.bulk-upload.template');
    Route::post('/contractor-profiles/bulk-upload/import', [ContractorProfileBulkUploadController::class, 'import'])->name('contractor-profiles.bulk-upload.import');
    Route::get('/contractor-profiles/bulk-upload/users', [ContractorProfileBulkUploadController::class, 'getUsers'])->name('contractor-profiles.bulk-upload.users');
    
    Route::resource("insurance-companies", InsuranceCompanyController::class);
    Route::resource("stores", StoreController::class);
    Route::resource("suppliers", SupplierController::class);
    Route::resource("contractor-profiles", ContractorProfileController::class);
    Route::resource("sub-groups", SubGroupController::class);
    Route::resource("service-charges", ServiceChargeController::class);
    Route::get('/service-charges/get-entities', [ServiceChargeController::class, 'getEntities'])->name('service-charges.get-entities');
    Route::resource("contractor-service-charges", ContractorServiceChargeController::class);
    Route::resource("admins", AdminController::class);
    
    // Maturation Periods Settings (Kashtre only)
    Route::resource("maturation-periods", MaturationPeriodController::class);
    Route::post("maturation-periods/{maturationPeriod}/toggle-status", [MaturationPeriodController::class, 'toggleStatus'])->name('maturation-periods.toggle-status');
    
    // Credit Note Workflow Settings (Kashtre only)
    Route::get('credit-note-workflows/bulk-upload', [CreditNoteWorkflowBulkUploadController::class, 'index'])->name('credit-note-workflows.bulk-upload.index');
    Route::get('credit-note-workflows/bulk-upload/template', [CreditNoteWorkflowBulkUploadController::class, 'downloadTemplate'])->name('credit-note-workflows.bulk-upload.template');
    Route::post('credit-note-workflows/bulk-upload/import', [CreditNoteWorkflowBulkUploadController::class, 'import'])->name('credit-note-workflows.bulk-upload.import');

    Route::resource("credit-note-workflows", CreditNoteWorkflowController::class);
    
    // Service Point Supervisors
    Route::resource("service-point-supervisors", ServicePointSupervisorController::class);
    
    // Service Delivery Queue Reassignment (Supervisors only)
    Route::post("service-delivery-queues/{serviceDeliveryQueue}/reassign", [ServiceDeliveryQueueController::class, 'reassignItem'])->name('service-delivery-queues.reassign');
    
    // Withdrawal Settings
    Route::resource("withdrawal-settings", WithdrawalSettingController::class);
    Route::resource("business-withdrawal-settings", BusinessWithdrawalSettingController::class);
    Route::resource("withdrawal-requests", WithdrawalRequestController::class);
    Route::post("withdrawal-requests/{withdrawalRequest}/approve", [WithdrawalRequestController::class, 'approve'])->name('withdrawal-requests.approve');
    Route::post("withdrawal-requests/{withdrawalRequest}/reject", [WithdrawalRequestController::class, 'reject'])->name('withdrawal-requests.reject');
    
    // Testing routes (Admin only) - Rate limited to prevent abuse
    Route::post('/testing/clear-data', [TestingController::class, 'clearData'])
        ->name('testing.clear-data')
        ->middleware('throttle:5,1'); // Max 5 requests per minute
    
    Route::get('/clients/completed', [CompletedClientsController::class, 'index'])->name('clients.completed');
    Route::get('/clients/{client}/completed-items', [CompletedClientsController::class, 'showCompletedItems'])->name('clients.completed-items');
    Route::post('/clients/search-existing', [ClientController::class, 'searchExistingClient'])->name('clients.search-existing');
    Route::resource("clients", ClientController::class);
    Route::post('/clients/{client}/update-payment-methods', [ClientController::class, 'updatePaymentMethods'])->name('clients.update-payment-methods');
Route::post('/clients/{client}/update-payment-phone', [ClientController::class, 'updatePaymentPhone'])->name('clients.update-payment-phone');
    
    // Visits
    Route::get('/daily-visits', [DailyVisitsController::class, 'index'])->name('daily-visits.index');
    Route::view('/test-livewire', 'test-livewire')->name('test-livewire');

// Invoice routes
Route::post('/invoices/service-charge', [InvoiceController::class, 'serviceCharge'])->name('invoices.service-charge');
Route::post('/invoices/package-adjustment', [InvoiceController::class, 'calculatePackageAdjustment'])->name('invoices.package-adjustment');
Route::post('/invoices/balance-adjustment', [InvoiceController::class, 'calculateBalanceAdjustment'])->name('invoices.balance-adjustment');
// Local development override for mobile money payments
if (app()->environment('local')) {
    Route::post('/invoices/mobile-money-payment', [LocalPaymentController::class, 'processMobileMoneyPayment'])->name('invoices.mobile-money-payment');
} else {
    Route::post('/invoices/mobile-money-payment', [InvoiceController::class, 'processMobileMoneyPayment'])->name('invoices.mobile-money-payment');
}
Route::post('/invoices/reinitiate-failed-transaction', [InvoiceController::class, 'reinitiateFailedTransaction'])->name('invoices.reinitiate-failed-transaction');
Route::post('/invoices/reinitiate-failed-invoice', [InvoiceController::class, 'reinitiateFailedInvoice'])->name('invoices.reinitiate-failed-invoice');

// Receipt testing route (remove in production)
Route::post('/invoices/{invoice}/send-receipts', [InvoiceController::class, 'sendReceipts'])->name('invoices.send-receipts');
Route::post('/invoices/{invoice}/manually-complete', [InvoiceController::class, 'manuallyCompleteTransaction'])->name('invoices.manually-complete');
Route::get('/test-mail-config', [InvoiceController::class, 'testMail'])->name('test-mail-config');

// Balance Statement Routes
Route::get('/balance-statement', [BalanceHistoryController::class, 'index'])->name('balance-statement.index');
Route::get('/balance-statement/{clientId}', [BalanceHistoryController::class, 'show'])->name('balance-statement.show');

// Business Balance Statement Routes
Route::get('/business-balance-statement', [BusinessBalanceHistoryController::class, 'index'])->name('business-balance-statement.index');
Route::get('/business-balance-statement/{business}', [BusinessBalanceHistoryController::class, 'show'])->name('business-balance-statement.show');

// Kashtre (Super Business) Balance Statement Routes
Route::get('/kashtre-balance-statement', [BusinessBalanceHistoryController::class, 'kashtreStatement'])->name('kashtre-balance-statement.index');
Route::get('/kashtre-balance-statement/show', [BusinessBalanceHistoryController::class, 'kashtreStatementShow'])->name('kashtre-balance-statement.show');

// Contractor Balance Statement Routes
Route::get('/contractor-balance-statement', [ContractorBalanceHistoryController::class, 'index'])->name('contractor-balance-statement.index');
Route::get('/contractor-balance-statement/{contractorProfile}', [ContractorBalanceHistoryController::class, 'show'])->name('contractor-balance-statement.show');

// Contractor Withdrawal Request Routes
Route::get('/contractor-withdrawal-requests/{contractorProfile}', [App\Http\Controllers\ContractorWithdrawalRequestController::class, 'index'])->name('contractor-withdrawal-requests.index');
Route::get('/contractor-withdrawal-requests/create/{contractorProfile}', [App\Http\Controllers\ContractorWithdrawalRequestController::class, 'create'])->name('contractor-withdrawal-requests.create');
Route::post('/contractor-withdrawal-requests/{contractorProfile}', [App\Http\Controllers\ContractorWithdrawalRequestController::class, 'store'])->name('contractor-withdrawal-requests.store');
Route::get('/contractor-withdrawal-requests/show/{contractorWithdrawalRequest}', [App\Http\Controllers\ContractorWithdrawalRequestController::class, 'show'])->name('contractor-withdrawal-requests.show');
Route::post('/contractor-withdrawal-requests/{contractorWithdrawalRequest}/approve', [App\Http\Controllers\ContractorWithdrawalRequestController::class, 'approve'])->name('contractor-withdrawal-requests.approve');
Route::post('/contractor-withdrawal-requests/{contractorWithdrawalRequest}/reject', [App\Http\Controllers\ContractorWithdrawalRequestController::class, 'reject'])->name('contractor-withdrawal-requests.reject');

// Finance - Withdrawal Requests (Kashtre)
Route::get('/finance/withdrawals', function () {
    // Only accessible to Kashtre and authenticated users
    if (!auth()->check()) {
        abort(403);
    }
    return view('finance.withdrawals.index');
})->name('finance.withdrawals.index');
Route::get('/balance-statement/{clientId}/json', [BalanceHistoryController::class, 'getBalanceHistory'])->name('balance-statement.json');

Route::get('/invoices/generate-number', [InvoiceController::class, 'generateInvoiceNumber'])->name('invoices.generate-number');
Route::post('/invoices/generate-invoice-number', [InvoiceController::class, 'generateInvoiceNumber'])->name('invoices.generate-invoice-number');
Route::post('/invoices/{invoice}/generate-quotation', [QuotationController::class, 'generateFromInvoice'])->name('invoices.generate-quotation');
Route::get('/invoices/{invoice}/print', [InvoiceController::class, 'print'])->name('invoices.print');
Route::patch('/invoices/{invoice}/cancel', [InvoiceController::class, 'cancel'])->name('invoices.cancel');
Route::resource('invoices', InvoiceController::class);

// Quotation routes
Route::get('/quotations/{quotation}/print', [QuotationController::class, 'print'])->name('quotations.print');
Route::patch('/quotations/{quotation}/status', [QuotationController::class, 'updateStatus'])->name('quotations.update-status');
Route::patch('/quotations/{quotation}/accept', [QuotationController::class, 'accept'])->name('quotations.accept');
Route::patch('/quotations/{quotation}/reject', [QuotationController::class, 'reject'])->name('quotations.reject');
Route::resource('quotations', QuotationController::class);

// Package Tracking Routes
Route::get('/package-tracking/dashboard', [PackageTrackingController::class, 'dashboard'])->name('package-tracking.dashboard');
Route::post('/package-tracking/{packageTracking}/use-quantity', [PackageTrackingController::class, 'useQuantity'])->name('package-tracking.use-quantity');
Route::get('/clients/{client}/packages', [PackageTrackingController::class, 'clientPackages'])->name('package-tracking.client-packages');
Route::resource('package-tracking', PackageTrackingController::class)->except(['create', 'store', 'edit', 'update']);

// Package Sales Routes
Route::get('/package-sales/history', [PackageSalesController::class, 'history'])->name('package-sales.history');
Route::get('/package-sales/export', [PackageSalesController::class, 'export'])->name('package-sales.export');
Route::get('/package-sales/stats', [PackageSalesController::class, 'getStats'])->name('package-sales.stats');
Route::resource('package-sales', PackageSalesController::class)->except(['create', 'store', 'edit', 'update']);

// Service Delivery routes
Route::post('/service-delivery/deliver-item', [ServiceDeliveryController::class, 'deliverItem'])->name('service-delivery.deliver-item');
Route::post('/service-delivery/deliver-multiple', [ServiceDeliveryController::class, 'deliverMultipleItems'])->name('service-delivery.deliver-multiple');
Route::get('/service-delivery/pending/{invoice}', [ServiceDeliveryController::class, 'getPendingDelivery'])->name('service-delivery.pending');
Route::get('/service-delivery/statement/{invoice}', [ServiceDeliveryController::class, 'getDeliveryHistory'])->name('service-delivery.statement');

// Money Tracking routes
Route::get('/money-tracking/dashboard', [MoneyTrackingController::class, 'dashboard'])->name('money-tracking.dashboard');
Route::get('/money-tracking/client-account/{client}', [MoneyTrackingController::class, 'getClientAccount'])->name('money-tracking.client-account');
Route::get('/money-tracking/contractor-account/{contractor}', [MoneyTrackingController::class, 'getContractorAccount'])->name('money-tracking.contractor-account');
Route::get('/money-tracking/transfer-statement', [MoneyTrackingController::class, 'getTransferHistory'])->name('money-tracking.transfer-statement');
Route::get('/money-tracking/account-summary', [MoneyTrackingController::class, 'getAccountSummary'])->name('money-tracking.account-summary');
Route::post('/money-tracking/process-refund', [MoneyTrackingController::class, 'processRefund'])->name('money-tracking.process-refund');

// Suspense Accounts routes
Route::get('/suspense-accounts', [SuspenseAccountController::class, 'index'])->name('suspense-accounts.index');
Route::get('/suspense-accounts/{id}', [SuspenseAccountController::class, 'show'])->name('suspense-accounts.show');
Route::get('/suspense-accounts-api/data', [SuspenseAccountController::class, 'getSuspenseAccountsData'])->name('suspense-accounts.data');
    Route::get('/pos/item-selection/{client}', [TransactionController::class, 'itemSelection'])->name('pos.item-selection');
    
    // Admin bulk operations
    Route::get('/admins/bulk/template', [AdminController::class, 'downloadTemplate'])->name('admins.bulk.template');
    Route::post('/admins/bulk/upload', [AdminController::class, 'bulkUpload'])->name('admins.bulk.upload');
    
    // Staff bulk operations
    Route::get('/users/bulk/template', [UserController::class, 'downloadTemplate'])->name('users.bulk.template');
    Route::post('/users/bulk/upload', [UserController::class, 'bulkUpload'])->name('users.bulk.upload');
    
    // Business bulk operations
    Route::get('/businesses/bulk/template', [BusinessController::class, 'downloadTemplate'])->name('businesses.bulk.template');
    Route::post('/businesses/bulk/upload', [BusinessController::class, 'bulkUpload'])->name('businesses.bulk.upload');
    
    // Branch bulk operations
    Route::get('/branches/bulk/template', [BranchController::class, 'downloadTemplate'])->name('branches.bulk.template');
    Route::post('/branches/bulk/upload', [BranchController::class, 'bulkUpload'])->name('branches.bulk.upload');
    
    Route::resource("audit-logs", AuditLogController::class);
    Route::post('/select-room', [RoomController::class, 'selectRoom'])->name('room.select');
    Route::post('/select-branch', [BranchController::class, 'selectBranch'])->name('branch.select');

    Route::prefix('bulk-upload')->group(function () {
        Route::get('/template', [BulkUploadController::class, 'generateTemplate'])->name('bulk.upload.template');
        Route::get('/form', [BulkUploadController::class, 'showUploadForm'])->name('bulk.upload.form');
        Route::post('/import-validations', [BulkUploadController::class, 'importTemplate'])->name('bulk.upload.import-validations');
    });

    // Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    // routes/web.php
    // Route::get('/users/{user:uuid}', [UserController::class, 'show']);

    Route::get('/test-mail-view', function () {
        return view('mail.bot'); // 
    });
});
