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
    Route::resource('payments', PaymentController::class);
    Route::get('payments-upload', function() {
        return view('payments.upload');
    })->name('payments.upload-data');
    Route::post('payments-upload', [PaymentController::class, 'uploadMonthEndData'])->name('payments.upload');

    Route::get('postcard-data', function() {
        return view('payments.postcard-data');
    })->name('payments.postcard-data');
    
    Route::post('postcard-data', [PaymentController::class, 'generatePostcardData'])->name('payments.postcard-data');
    Route::get('postcard-export-csv', [PaymentController::class, 'exportPostcardCsv'])->name('payments.postcard-csv');
    Route::post('postcard-pdf', [PostcardController::class, 'generatePostcardPDF'])->name('payments.postcard-pdf');

    // Report routes (Manager and Admin only)
    Route::middleware(['role:admin,manager'])->group(function () {
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/create', [ReportController::class, 'create'])->name('reports.create');
        Route::post('reports/customers', [ReportController::class, 'generateCustomerReport'])->name('reports.customers');
        Route::post('reports/payments', [ReportController::class, 'generatePaymentReport'])->name('reports.payments');
    });
});
