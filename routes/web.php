<?php

use App\Http\Controllers\AgencyController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\Auth\ChangePasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\KhataController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SalesmanController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/language/{locale}', [LanguageController::class, 'switch'])->name('language.switch');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.store');
    Route::get('/forgot-password', [PasswordResetController::class, 'request'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'email'])->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'resetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->name('password.update');
});

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/password/change', [ChangePasswordController::class, 'edit'])->name('password.change');
    Route::post('/password/change', [ChangePasswordController::class, 'update'])->name('password.change.update');

    Route::get('/search', [SearchController::class, 'index'])->name('search');

    Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
    Route::get('/suppliers/create', [SupplierController::class, 'create'])->name('suppliers.create');
    Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
    Route::get('/suppliers/{supplier}', [SupplierController::class, 'show'])->name('suppliers.show');
    Route::get('/suppliers/{supplier}/edit', [SupplierController::class, 'edit'])->name('suppliers.edit');
    Route::put('/suppliers/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');
    Route::post('/suppliers/{supplier}/archive', [SupplierController::class, 'archive'])->name('suppliers.archive');
    Route::post('/suppliers/{supplier}/restore', [SupplierController::class, 'restore'])->name('suppliers.restore');
    Route::delete('/suppliers/{supplier}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');
    Route::get('/suppliers/{supplier}/statement', [SupplierController::class, 'statement'])->name('suppliers.statement');
    Route::get('/suppliers/{supplier}/statement/pdf', [SupplierController::class, 'statementPdf'])->name('suppliers.statement.pdf');
    Route::get('/suppliers/{supplier}/statement/excel', [SupplierController::class, 'statementExcel'])->name('suppliers.statement.excel');
    Route::get('/suppliers/{supplier}/whatsapp', [SupplierController::class, 'whatsapp'])->name('suppliers.whatsapp');

    Route::get('/agencies', [AgencyController::class, 'index'])->name('agencies.index');
    Route::get('/agencies/create', [AgencyController::class, 'create'])->name('agencies.create');
    Route::post('/agencies', [AgencyController::class, 'store'])->name('agencies.store');
    Route::get('/agencies/{agency}', [AgencyController::class, 'show'])->name('agencies.show');
    Route::get('/agencies/{agency}/edit', [AgencyController::class, 'edit'])->name('agencies.edit');
    Route::put('/agencies/{agency}', [AgencyController::class, 'update'])->name('agencies.update');
    Route::post('/agencies/{agency}/archive', [AgencyController::class, 'archive'])->name('agencies.archive');
    Route::post('/agencies/{agency}/restore', [AgencyController::class, 'restore'])->name('agencies.restore');
    Route::delete('/agencies/{agency}', [AgencyController::class, 'destroy'])->name('agencies.destroy');

    Route::get('/salesmen', [SalesmanController::class, 'index'])->name('salesmen.index');
    Route::get('/salesmen/create', [SalesmanController::class, 'create'])->name('salesmen.create');
    Route::post('/salesmen', [SalesmanController::class, 'store'])->name('salesmen.store');
    Route::get('/salesmen/{salesman}', [SalesmanController::class, 'show'])->name('salesmen.show');
    Route::get('/salesmen/{salesman}/edit', [SalesmanController::class, 'edit'])->name('salesmen.edit');
    Route::put('/salesmen/{salesman}', [SalesmanController::class, 'update'])->name('salesmen.update');
    Route::post('/salesmen/{salesman}/archive', [SalesmanController::class, 'archive'])->name('salesmen.archive');
    Route::post('/salesmen/{salesman}/restore', [SalesmanController::class, 'restore'])->name('salesmen.restore');
    Route::delete('/salesmen/{salesman}', [SalesmanController::class, 'destroy'])->name('salesmen.destroy');

    Route::get('/purchases', [PurchaseController::class, 'index'])->name('purchases.index');
    Route::get('/purchases/create', [PurchaseController::class, 'create'])->name('purchases.create');
    Route::post('/purchases', [PurchaseController::class, 'store'])->name('purchases.store');
    Route::get('/purchases/{purchase}', [PurchaseController::class, 'show'])->name('purchases.show');
    Route::get('/purchases/{purchase}/edit', [PurchaseController::class, 'edit'])->name('purchases.edit');
    Route::put('/purchases/{purchase}', [PurchaseController::class, 'update'])->name('purchases.update');
    Route::delete('/purchases/{purchase}', [PurchaseController::class, 'destroy'])->name('purchases.destroy');
    Route::get('/purchases/{purchase}/print', [PurchaseController::class, 'print'])->name('purchases.print');
    Route::get('/purchases/{purchase}/pdf', [PurchaseController::class, 'pdf'])->name('purchases.pdf');
    Route::get('/purchases/{purchase}/attachment', [PurchaseController::class, 'downloadAttachment'])->name('purchases.attachment');

    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('/payments/create', [PaymentController::class, 'create'])->name('payments.create');
    Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');
    Route::get('/payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
    Route::get('/payments/{payment}/edit', [PaymentController::class, 'edit'])->name('payments.edit');
    Route::put('/payments/{payment}', [PaymentController::class, 'update'])->name('payments.update');
    Route::delete('/payments/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy');
    Route::get('/payments/{payment}/receipt/pdf', [PaymentController::class, 'receipt'])->name('payments.receipt.pdf');
    Route::get('/payments/{payment}/receipt', [PaymentController::class, 'downloadReceipt'])->name('payments.receipt');

    Route::get('/khata', [KhataController::class, 'index'])->name('khata.index');
    Route::get('/khata/{supplier}', [KhataController::class, 'show'])->name('khata.show');
    Route::post('/khata/{supplier}/adjustment', [KhataController::class, 'addAdjustment'])->name('khata.adjustment');
    Route::post('/khata/{supplier}/return', [KhataController::class, 'addReturn'])->name('khata.return');
    Route::delete('/khata/{supplier}/entries/{entry}', [KhataController::class, 'destroyEntry'])->name('khata.entries.destroy');
    Route::delete('/khata/{supplier}', [KhataController::class, 'clear'])->name('khata.clear');
    Route::get('/adjustments', [KhataController::class, 'adjustments'])->name('khata.adjustments');
    Route::get('/returns', [KhataController::class, 'returns'])->name('khata.returns');

    Route::get('/receivables', [FinanceController::class, 'receivables'])->name('receivables.index');
    Route::get('/payables', [FinanceController::class, 'payables'])->name('payables.index');
    Route::get('/finance/{type}/export', [FinanceController::class, 'export'])->name('finance.export');

    Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
    Route::get('/expenses/create', [ExpenseController::class, 'create'])->name('expenses.create');
    Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
    Route::get('/expenses/{expense}/edit', [ExpenseController::class, 'edit'])->name('expenses.edit');
    Route::put('/expenses/{expense}', [ExpenseController::class, 'update'])->name('expenses.update');
    Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/print', [ReportController::class, 'print'])->name('reports.print');
    Route::get('/reports/pdf', [ReportController::class, 'pdf'])->name('reports.pdf');
    Route::get('/reports/excel', [ReportController::class, 'excel'])->name('reports.excel');

    Route::get('/backups', [BackupController::class, 'index'])->name('backups.index');
    Route::post('/backups', [BackupController::class, 'create'])->name('backups.create');
    Route::post('/backups/json', [BackupController::class, 'json'])->name('backups.json');
    Route::get('/backups/{backup}/download', [BackupController::class, 'download'])->name('backups.download');
    Route::post('/backups/{backup}/verify', [BackupController::class, 'verify'])->name('backups.verify');
    Route::post('/backups/{backup}/restore', [BackupController::class, 'restore'])->name('backups.restore');
    Route::delete('/backups/{backup}', [BackupController::class, 'destroy'])->name('backups.destroy');
    Route::post('/backups/upload', [BackupController::class, 'upload'])->name('backups.upload');

    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::post('/users/{id}/restore', [UserController::class, 'restore'])->name('users.restore');

    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

    Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::get('/audit', [AuditLogController::class, 'index'])->name('audit.index');
});
