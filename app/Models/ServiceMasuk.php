<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ServiceMasuk extends Model
{
    protected $fillable = [
        'category_id',
        'nama_barang',
        'nama_client',
        'nomor_wa',
        'tanggal_masuk',
        'kerusakan',
        'perlengkapan',
        'keterangan',
        'nomor_surat',
        'qrcode',
    ];

    protected $casts = [
        'perlengkapan' => 'array',
        'tanggal_masuk' => 'date',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    protected static function booted()
    {
        static::creating(function ($service) {
            // Format tanggal: ddmmyy
            $date = now()->format('dmy');

            // Hitung data hari ini
            $countToday = DB::table('service_masuks')
                ->whereDate('created_at', now()->toDateString())
                ->count() + 1;

            // Nomor urut 3 digit
            $number = str_pad($countToday, 3, '0', STR_PAD_LEFT);

            // Nomor surat final
            $service->nomor_surat = "S-{$date}-{$number}";

            // QR Code (base64 PNG)
            $service->qrcode = QrCode::format('svg')
                ->size(250)
                ->generate($service->nomor_surat);
        });
    }
}
