<?php

use App\Http\Controllers\AndroidController;
use App\Http\Controllers\ServiceMasukController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/install', function () {
    return view('pwa.install');
});

Route::get('/print/service/{service}', [ServiceMasukController::class, 'print'])
    ->name('service.print');

Route::get('/tracking/{token}', [ServiceMasukController::class, 'track'])->name('tracking.check');

Route::prefix('ace')->group(function () {
    Route::get('/service-masuk', [AndroidController::class, 'serviceMasuk'])->name('android.service-masuk');
    Route::get('/service-proses', [AndroidController::class, 'serviceProses'])->name('android.service-proses');
    Route::get('/service-jadi', [AndroidController::class, 'serviceJadi'])->name('android.service-jadi');
    Route::get('/clients', [AndroidController::class, 'clients'])->name('android.clients');
    Route::get('/profile', [AndroidController::class, 'profile'])->name('android.profile');
});
