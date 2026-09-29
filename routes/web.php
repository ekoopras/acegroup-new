<?php

use App\Http\Controllers\AndroidController;
use App\Http\Controllers\ServiceMasukController;
use App\Models\DataService;
use App\Models\ServiceJadi;
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

Route::get('/print/service/data-service/{dataService}', function (DataService $dataService) {
    if (!Auth::check()) abort(404);

    return app(ServiceMasukController::class)->printDataService($dataService);
})->name('service.print.dataservice');

Route::get('/tracking/{token}', [ServiceMasukController::class, 'track'])->name('tracking.check');

Route::get('/service-masuk/print', function () {
    $records = session('print_service_masuk_records', collect());

    if ($records->isEmpty()) {
        return 'Tidak ada data untuk dicetak.';
    }

    return view('print.service-masuk', compact('records'));
})->name('service-masuk.print');

Route::get('/service-proses/print', function () {
    // Ambil semua data Service Proses beserta relasinya
    $records = ServiceProses::with(['dataClient', 'category'])
        ->latest()
        ->get();

    if ($records->isEmpty()) {
        return '<script>alert("Tidak ada data service proses untuk dicetak."); window.close();</script>';
    }

    return view('print.service-proses', compact('records'));
})->name('service-proses.print');

Route::get('/service-jadi/print', function () {
    // Ambil semua data Service Jadi beserta relasinya
    $records = ServiceJadi::with(['dataClient', 'category'])
        ->latest()
        ->get();

    if ($records->isEmpty()) {
        return '<script>alert("Tidak ada data service jadi untuk dicetak."); window.close();</script>';
    }

    return view('print.service-jadi', compact('records'));
})->name('service-jadi.print');
