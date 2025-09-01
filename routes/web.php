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

Route::middleware(['auth'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');  
    Route::resource('customers', CustomerController::class);
    Route::get('customers-export-csv', [CustomerController::class, 'exportCsv'])->name('customers.export-csv');
    Route::get('customers-export-xlsx', [CustomerController::class, 'exportXlsx'])->name('customers.export-xlsx');
    Route::get('customers/import', [CustomerController::class, 'showImportForm'])->name('customers.import');
    Route::post('customers/import', [CustomerController::class, 'import'])->name('customers.import');
    
    // API endpoints for bank and branch name retrieval
    Route::get('api/bank-name', [CustomerController::class, 'getBankName'])->name('customers.get-bank-name');
    Route::get('api/branch-name', [CustomerController::class, 'getBranchName'])->name('customers.get-branch-name');

    Route::get('payments/upload', [PaymentController::class, 'showUploadForm'])->name('payments.upload-form');
    Route::post('payments/upload', [PaymentController::class, 'uploadMonthEndData'])->name('payments.upload');
    Route::get('payments/xlsx-viewer', [PaymentController::class, 'showXlsxViewer'])->name('payments.xlsx-viewer');
    Route::post('payments/xlsx-preview', [PaymentController::class, 'previewXlsxData'])->name('payments.xlsx-preview');
    Route::post('payments/xlsx-import', [PaymentController::class, 'importSelectedXlsxData'])->name('payments.xlsx-import');
    Route::get('payments/postcard-form', [PaymentController::class, 'showPostcardForm'])->name('payments.postcard-form');
    Route::get('payments/postcard-data', [PaymentController::class, 'generatePostcardData'])->name('payments.postcard-data');
    Route::get('payments/export-csv', [PaymentController::class, 'exportPostcardCsv'])->name('payments.export-csv');
    Route::get('payments/export-xlsx', [PaymentController::class, 'exportPostcardXlsx'])->name('payments.export-xlsx');
    Route::get('payments/export-pdf', [PaymentController::class, 'exportPostcardPdf'])->name('payments.export-pdf');
    Route::get('/postcards/print/csv', [\App\Http\Controllers\PaymentController::class, 'exportPostcardPrintCsv'])->name('postcards.print.csv');
    Route::get('/postcards/print/xlsx', [\App\Http\Controllers\PaymentController::class, 'exportPostcardPrintXlsx'])->name('postcards.print.xlsx');
    Route::get('/postcards/print/pdf', [\App\Http\Controllers\PaymentController::class, 'exportPostcardPrintPdf'])->name('postcards.print.pdf');
    Route::resource('payments', PaymentController::class);

    Route::middleware(['role:admin,manager'])->group(function () {
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/create', [ReportController::class, 'create'])->name('reports.create');
        Route::post('reports/customers', [ReportController::class, 'generateCustomerReport'])->name('reports.customers');
        Route::post('reports/customers-xlsx', [ReportController::class, 'generateCustomerXlsx'])->name('reports.customers-xlsx');
        Route::post('reports/payments', [ReportController::class, 'generatePaymentReport'])->name('reports.payments');
        Route::post('reports/payments-xlsx', [ReportController::class, 'generatePaymentXlsx'])->name('reports.payments-xlsx');
    });
});
