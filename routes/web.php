<?php

use App\Http\Controllers\AndroidController;
use App\Http\Controllers\ServiceMasukController;
use App\Models\ServiceMasuk;
use App\Models\ServiceProses;
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

// Route::get('/print/service/{service}', function (ServiceMasuk $service) {
//     if (!Auth::check()) {
//         abort(404);
//     }

//     return app(ServiceMasukController::class)->print($service);
// })->name('service.print');

Route::get('/print/service/masuk/{serviceMasuk}', function (ServiceMasuk $serviceMasuk) {
    if (!Auth::check()) abort(404);

    return app(ServiceMasukController::class)->print($serviceMasuk);
})->name('service.print.masuk');

Route::get('/print/service/proses/{serviceProses}', function (ServiceProses $serviceProses) {
    if (!Auth::check()) abort(404);

    return app(ServiceMasukController::class)->printProses($serviceProses);
})->name('service.print.proses');

Route::get('/tracking/{token}', [ServiceMasukController::class, 'track'])->name('tracking.check');
