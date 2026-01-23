<?php

use App\Http\Controllers\ServiceMasukController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/print/service/{service}', [ServiceMasukController::class, 'print'])
    ->name('service.print');
