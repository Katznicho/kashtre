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
use App\Http\Controllers\ServiceChargeController;
use App\Http\Controllers\ContractorServiceChargeController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ServiceDeliveryController;
use App\Http\Controllers\MoneyTrackingController;
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
    Route::resource("users", UserController::class);
    Route::resource("roles", RoleController::class);
    Route::resource("departments", DepartmentController::class);
    Route::resource("titles", TitleController::class);
    Route::resource("qualifications", QualificationController::class);
    Route::resource("rooms", RoomController::class);
    Route::resource("service-points", ServicePointController::class);
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
    Route::resource("clients", ClientController::class);
    Route::post('/clients/{client}/update-payment-methods', [ClientController::class, 'updatePaymentMethods'])->name('clients.update-payment-methods');
Route::post('/clients/{client}/update-payment-phone', [ClientController::class, 'updatePaymentPhone'])->name('clients.update-payment-phone');

// Invoice routes
Route::post('/invoices/service-charge', [InvoiceController::class, 'serviceCharge'])->name('invoices.service-charge');
Route::post('/invoices/package-adjustment', [InvoiceController::class, 'calculatePackageAdjustment'])->name('invoices.package-adjustment');
Route::get('/invoices/generate-number', [InvoiceController::class, 'generateInvoiceNumber'])->name('invoices.generate-number');
Route::get('/invoices/{invoice}/print', [InvoiceController::class, 'print'])->name('invoices.print');
Route::patch('/invoices/{invoice}/cancel', [InvoiceController::class, 'cancel'])->name('invoices.cancel');
Route::resource('invoices', InvoiceController::class);

// Service Delivery routes
Route::post('/service-delivery/deliver-item', [ServiceDeliveryController::class, 'deliverItem'])->name('service-delivery.deliver-item');
Route::post('/service-delivery/deliver-multiple', [ServiceDeliveryController::class, 'deliverMultipleItems'])->name('service-delivery.deliver-multiple');
Route::get('/service-delivery/pending/{invoice}', [ServiceDeliveryController::class, 'getPendingDelivery'])->name('service-delivery.pending');
Route::get('/service-delivery/history/{invoice}', [ServiceDeliveryController::class, 'getDeliveryHistory'])->name('service-delivery.history');

// Money Tracking routes
Route::get('/money-tracking/dashboard', [MoneyTrackingController::class, 'dashboard'])->name('money-tracking.dashboard');
Route::get('/money-tracking/client-account/{client}', [MoneyTrackingController::class, 'getClientAccount'])->name('money-tracking.client-account');
Route::get('/money-tracking/contractor-account/{contractor}', [MoneyTrackingController::class, 'getContractorAccount'])->name('money-tracking.contractor-account');
Route::get('/money-tracking/transfer-history', [MoneyTrackingController::class, 'getTransferHistory'])->name('money-tracking.transfer-history');
Route::get('/money-tracking/account-summary', [MoneyTrackingController::class, 'getAccountSummary'])->name('money-tracking.account-summary');
Route::post('/money-tracking/process-refund', [MoneyTrackingController::class, 'processRefund'])->name('money-tracking.process-refund');
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
