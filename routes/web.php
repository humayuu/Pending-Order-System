<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeliveryChallanController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::middleware('guest')->group(function (): void {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);
});

Route::middleware('auth')->group(function (): void {
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::resource('clients', ClientController::class)->except(['show']);

    Route::resource('orders', OrderController::class)->only(['index', 'create', 'store', 'show']);

    Route::resource('challans', DeliveryChallanController::class)->only(['index', 'create', 'store', 'show']);

    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/item-wise', [ReportController::class, 'itemWise'])->name('reports.item-wise');
    Route::get('reports/item-wise/pdf', [ReportController::class, 'itemWisePdf'])->name('reports.item-wise.pdf');
    Route::get('reports/item-and-po-wise', [ReportController::class, 'itemAndPoWise'])->name('reports.item-and-po-wise');
    Route::get('reports/item-and-po-wise/pdf', [ReportController::class, 'itemAndPoWisePdf'])->name('reports.item-and-po-wise.pdf');
});
