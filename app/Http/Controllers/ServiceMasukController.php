<?php

namespace App\Http\Controllers;

use App\Models\ServiceMasuk;
use Illuminate\Http\Request;

class ServiceMasukController extends Controller
{
    public function print(ServiceMasuk $service)
    {
        return view('print.service', compact('service'));
    }
}
