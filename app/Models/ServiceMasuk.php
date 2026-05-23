<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ServiceMasuk extends Model
{
    protected $fillable = [
        'category_id',
        'data_client_id',
        'nama_pelanggan',
        'nama_barang',
        'tanggal_masuk',
        'kerusakan',
        'perlengkapan',
        'keterangan',
        'nomor_surat',
        'qrcode',
        'token',
    ];

    protected $casts = [
        'log_status' => 'array',
        'kerusakan' => 'array',
        'perlengkapan' => 'array',
        'tanggal_masuk' => 'date',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function dataClient()
    {
        return $this->belongsTo(DataClient::class);
    }


    // function huruf kapital
    public function setNamaBarangAttribute($value)
    {
        $this->attributes['nama_barang'] = ucwords(strtolower($value));
    }


    protected static function booted()
    {
        static::creating(function ($service) {
            $date = now()->format('dmy');

            // Loop untuk memastikan nomor surat benar-benar unik di semua tabel
            do {
                // Contoh format: S-110526-XJ92 (4 karakter acak)
                $randomStr = strtoupper(str()->random(4));
                $nomorSurat = "S-{$date}-{$randomStr}";

                $exists = collect(['service_masuks', 'service_proses', 'service_jadis'])
                    ->contains(fn($table) => DB::table($table)->where('nomor_surat', $nomorSurat)->exists());
            } while ($exists);

            $service->nomor_surat = $nomorSurat;

            $service->qrcode = QrCode::format('svg')
                ->size(250)
                ->generate($service->nomor_surat);

            $service->token = str()->random(32);
        });
    }
}
