<?php

use App\Http\Controllers\DeviceController;
use App\Models\DataClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::get('/clients', function () {
    return DataClient::select('nama', 'nomor_wa')->get();
});
