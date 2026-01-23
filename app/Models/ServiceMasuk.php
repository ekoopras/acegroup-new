<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceMasuk extends Model
{
    protected $fillable = [
        'category_id',
        'nama_client',
        'nomor_wa',
        'tanggal_masuk',
        'kerusakan',
        'perlengkapan',
        'keterangan',
    ];

    protected $casts = [
        'perlengkapan' => 'array',
        'tanggal_masuk' => 'date',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
