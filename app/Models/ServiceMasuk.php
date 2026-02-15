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
        'nama_barang',
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

    public function dataClient()
    {
        return $this->belongsTo(DataClient::class);
    }


    protected static function booted()
    {
        static::creating(function ($service) {

            $date = now()->format('dmy');

            // Ambil nomor terakhir dari SEMUA tabel
            $tables = ['service_masuks', 'service_proses', 'service_jadis'];

            $lastNumbers = collect($tables)->map(function ($table) use ($date) {
                return DB::table($table)
                    ->where('nomor_surat', 'like', "S-{$date}-%")
                    ->orderBy('nomor_surat', 'desc')
                    ->value('nomor_surat');
            });

            $maxNumber = $lastNumbers
                ->filter()
                ->map(fn($item) => (int) substr($item, -3))
                ->max();

            $nextNumber = $maxNumber ? $maxNumber + 1 : 1;

            $number = str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

            $service->nomor_surat = "S-{$date}-{$number}";

            $service->qrcode = QrCode::format('svg')
                ->size(250)
                ->generate($service->nomor_surat);
        });
    }
}
