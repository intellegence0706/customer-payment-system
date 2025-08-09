<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\PostcardController;

// Authentication routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

// Protected routes
Route::middleware(['auth'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    
    // Customer routes
    Route::resource('customers', CustomerController::class);
    Route::get('customers-export-csv', [CustomerController::class, 'exportCsv'])->name('customers.export-csv');
    Route::get('api/bank-name', [CustomerController::class, 'getBankName'])->name('customers.get-bank-name');
    Route::get('api/branch-name', [CustomerController::class, 'getBranchName'])->name('customers.get-branch-name');

    // Payment routes
    // Define specific routes BEFORE the resource route to avoid collision with payments/{payment}
    Route::get('payments/upload', [PaymentController::class, 'showUploadForm'])->name('payments.upload-form');
    Route::post('payments/upload', [PaymentController::class, 'uploadMonthEndData'])->name('payments.upload');
    Route::get('payments/postcard-form', function() {
        return view('payments.postcard-form');
    })->name('payments.postcard-form');
    
    Route::get('payments/postcard-data', [PaymentController::class, 'generatePostcardData'])->name('payments.postcard-data');
    Route::get('payments/export-csv', [PaymentController::class, 'exportPostcardCsv'])->name('payments.export-csv');
    Route::get('payments/export-pdf', [PaymentController::class, 'exportPostcardPdf'])->name('payments.export-pdf');
    
    // Keep the resource route last
    Route::resource('payments', PaymentController::class);

    // Report routes (Manager and Admin only)
    Route::middleware(['role:admin,manager'])->group(function () {
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/create', [ReportController::class, 'create'])->name('reports.create');
        Route::post('reports/customers', [ReportController::class, 'generateCustomerReport'])->name('reports.customers');
        Route::post('reports/payments', [ReportController::class, 'generatePaymentReport'])->name('reports.payments');
    });
});
