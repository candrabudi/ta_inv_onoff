<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthController::class, 'showLogin']);
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/list', [CategoryController::class, 'list'])->name('categories.list');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{id}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/list', [ProductController::class, 'list'])->name('products.list');
    Route::get('/products/data', [ProductController::class, 'data'])->name('products.data');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/list', [UserController::class, 'list'])->name('users.list');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/list', [TransactionController::class, 'list'])->name('transactions.list');
    Route::get('/transactions/{transaction}/details', [TransactionController::class, 'details'])->name('transactions.details');
    Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
    Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy'])->name('transactions.destroy');

    Route::get('/stocks', [StockController::class, 'index'])->name('stocks.index');
    Route::get('/stocks/list', [StockController::class, 'list'])->name('stocks.list');
    Route::post('/stocks/restock', [StockController::class, 'restock'])->name('stocks.restock');
    Route::get('/stocks/{product}/history', [StockController::class, 'history'])->name('stocks.history');
});

Route::prefix('reports')->name('reports.')->middleware(['auth'])->group(function () {
    Route::get('/', [ReportController::class, 'index'])->name('index');

    Route::get('/stock-summary', [ReportController::class, 'stockSummary'])->name('stock_summary');
    Route::get('/stock-history', [ReportController::class, 'stockHistory'])->name('stock_history');
    Route::get('/transactions', [ReportController::class, 'transactions'])->name('transactions');

    Route::get('/export/stock-summary', [ReportController::class, 'exportStockSummary'])->name('export.stock_summary');
    Route::get('/export/stock-history', [ReportController::class, 'exportStockHistory'])->name('export.stock_history');
    Route::get('/export/transactions', [ReportController::class, 'exportTransactions'])->name('export.transactions');
});
