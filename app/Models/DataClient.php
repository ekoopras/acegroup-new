<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataClient extends Model
{
    protected $fillable = [
        'nama',
        'nomor_wa',
    ];

    protected $casts = [
        'services' => 'array',
    ];

    // function huruf kapital
    public function setNamaAttribute($value)
    {
        $this->attributes['nama'] = ucwords(strtolower($value));
    }

    public function logServices()
    {
        return $this->hasMany(LogService::class);
    }

    public function serviceMasuks()
    {
        return $this->hasMany(ServiceMasuk::class);
    }

    public function serviceProses()
    {
        return $this->hasMany(ServiceProses::class);
    }

    public function serviceJadis()
    {
        return $this->hasMany(ServiceJadi::class);
    }
}
