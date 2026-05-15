<?php

namespace App\Http\Controllers;

use App\Models\ServiceMasuk;
use App\Models\ServiceProses;
use Illuminate\Http\Request;

class ServiceMasukController extends Controller
{
    public function print(ServiceMasuk $service)
    {
        return view('pdf.service', compact('service'));
    }

    public function track($token)
    {
        // 1. Cek di tabel JADI (Selesai)
        $jadi = \App\Models\ServiceJadi::where('token', $token)->first();
        if ($jadi) {
            return view('tracking.index', [
                'service' => $jadi,
                'status' => 'selesai',
                'logs' => collect($jadi->log_status ?? [])->sortByDesc('tanggal')
            ]);
        }

        // 2. Cek di tabel PROSES (Sedang dikerjakan)
        $proses = \App\Models\ServiceProses::where('token', $token)->first();
        if ($proses) {
            return view('tracking.index', [
                'service' => $proses,
                'status' => 'proses',
                'logs' => collect($proses->log_status ?? [])->sortByDesc('tanggal')
            ]);
        }

        // 3. Cek di tabel MASUK (Antrean)
        $masuk = \App\Models\ServiceMasuk::where('token', $token)->firstOrFail();
        return view('tracking.index', [
            'service' => $masuk,
            'status' => 'antrean',
            'logs' => collect([])
        ]);
    }
}
