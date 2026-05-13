<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AndroidController extends Controller
{

    // Menu Service Masuk
    public function serviceMasuk()
    {
        $services = DB::table('service_masuks')
            ->join('data_client', 'service_masuk.data_client_id', '=', 'data_client.id')
            ->select('service_masuk.*', 'data_client.nama as nama_client')
            ->latest()
            ->get();

        return view('android.page.service-masuk', compact('services'));
    }

    // Menu Service Proses
    public function serviceProses()
    {
        $services = DB::table('service_proses')
            ->join('data_client', 'service_proses.data_client_id', '=', 'data_client.id')
            ->select('service_proses.*', 'data_client.nama as nama_client')
            ->latest()
            ->get();

        return view('android.service-proses', compact('services'));
    }

    // Menu Service Jadi
    public function serviceJadi()
    {
        $services = DB::table('service_jadi')
            ->join('data_client', 'service_jadi.data_client_id', '=', 'data_client.id')
            ->select('service_jadi.*', 'data_client.nama as nama_client')
            ->latest()
            ->get();

        return view('android.service-jadi', compact('services'));
    }

    // Menu Data Client
    public function clients()
    {
        $clients = DB::table('data_client')->latest()->get();
        return view('android.clients', compact('clients'));
    }

    // Menu Profile
    public function profile()
    {
        return view('android.profile');
    }
}
