<?php

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

Route::get('/track/{token}', [ServiceMasukController::class, 'track'])->name('tracking.check');
