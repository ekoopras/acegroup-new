<?php

use App\Http\Controllers\AndroidController;
use App\Http\Controllers\ServiceMasukController;
use App\Models\ServiceMasuk;
use App\Services\PrintService;
use Filament\Http\Middleware\Authenticate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/install', function () {
    return view('pwa.install');
});

Route::get('/print/service/{service}', function (ServiceMasuk $service) {
    // 🔒 Jika tidak login, langsung samarkan jadi 404
    if (!Auth::check()) {
        abort(404);
    }

    // Jika aman, panggil method print di Controller secara manual
    return app(ServiceMasukController::class)->print($service);
})->name('service.print');

Route::get('/tracking/{token}', [ServiceMasukController::class, 'track'])->name('tracking.check');
