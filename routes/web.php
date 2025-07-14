<?php

use App\Http\Controllers\ApiKeyController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\BulkPaymentController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\BusinessDocumentController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\paymentLinkController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DeveloperController;
use App\Http\Controllers\FloatManagementController;
use App\Http\Controllers\GetStartedController;
use App\Http\Controllers\IndividualPaymentController;

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

Route::middleware(['auth:sanctum', 'verified'])->group(function () {

    // Route for the getting the data feed
    // Route::get('/json-data-feed', [DataFeedController::class, 'getDataFeed'])->name('json_data_feed');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::impersonate();

    // Route::middleware(['auth'])->group(function () {
    //     Route::get('/impersonate/{id}', function ($id) {
    //         $user = User::findOrFail($id);
    //         auth()->user()->impersonate($user);
    //         return redirect('/dashboard');
    //     })->name('impersonate');

    //     Route::get('/leave-impersonation', function () {
    //         auth()->user()->leaveImpersonation();
    //         return redirect('/users');
    //     })->name('impersonate.leave');
    // });

    // Robot
    // Route::resource('payments', PaymentController::class);
    // Route::resource("history", HistoryController::class);

    Route::resource("businesses", BusinessController::class);
    Route::resource("branches", BranchController::class);
    Route::resource("support", SupportController::class);
    Route::resource("payment-links", PaymentLinkController::class);
    Route::resource("transactions", TransactionController::class);
    Route::resource("collections",  CollectionController::class);
    Route::resource("individual-payments", IndividualPaymentController::class);
    Route::resource("bulk-payments", BulkPaymentController::class);
    Route::resource("float-management", FloatManagementController::class);
    Route::resource("get-started", GetStartedController::class);
    Route::resource("business-documents", BusinessDocumentController::class);
    Route::resource("users", UserController::class);




    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    // routes/web.php
    Route::get('/users/{user:uuid}', [UserController::class, 'show']);

    //uuids
    Route::get('/pay/{paymentLink:uuid}', [PaymentLinkController::class, 'show'])->name('payment-links.public.show');
    Route::post('/pay/{paymentLink:uuid}/pay', [PaymentLinkController::class, 'pay'])->name('public.payment.pay');

    //uuids
    // Route::prefix('developers')->name('developers.')->group(function () {
    //     Route::get('/api-keys', [DeveloperController::class, 'apiKeys'])->name('api-keys');
    //     Route::get('/webhooks', [DeveloperController::class, 'webhooks'])->name('webhooks');
    //     Route::get('/docs', [DeveloperController::class, 'documentation'])->name('documentation');
    // });

    //
    // routes/web.php

     Route::prefix('developer')->group(function () {
        Route::get('/api-keys', [ApiKeyController::class, 'index'])->name('api-keys.index');
        Route::post('/api-keys/generate', [ApiKeyController::class, 'generate'])->name('api-keys.generate');
        Route::post('/api-keys/email', [ApiKeyController::class, 'email'])->name('api-keys.email');

     });





    Route::get('/test-mail-view', function () {
        return view('mail.bot'); // 
    });
});
