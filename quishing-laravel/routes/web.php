<?php

use App\Http\Controllers\ScanController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// Home - redirect to scan page
Route::get('/', function () {
    return redirect()->route('scan.index');
})->name('home');

// Scan routes (public, no auth required)
Route::get('/scan', [ScanController::class, 'index'])->name('scan.index');
Route::post('/scan', [ScanController::class, 'scan'])->name('scan.analyze');
Route::get('/history', [ScanController::class, 'history'])->name('scan.history');
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// API route for bot integration
Route::post('/api/scan', [ScanController::class, 'apiScan'])->name('api.scan');
Route::post('/api/scan-qr-image', [ScanController::class, 'scanQrImage'])->name('api.scan-qr-image');
