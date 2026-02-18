<?php

use App\Http\Controllers\DeviceController;
use App\Models\DataClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

// Route::get('/clients', function () {
//     return DataClient::select('nama', 'nomor_wa')->get();
// });

Route::get('/clients', function (Request $request) {

    if ($request->header('X-API-KEY') !== env('API_SECRET_KEY')) {
        return response()->json([
            'message' => 'Unauthorized'
        ], 401);
    }

    return DataClient::select('nama', 'nomor_wa')->get();
});
