<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'customers';
    protected $primaryKey = 'id_customer';

    protected $fillable = [
        'kode_customer',
        'nama_customer',
        'nomor_telepon',
        'alamat',
        'kategori_customer',
        'catatan',
        'status_aktif',
    ];

    protected $casts = [
        'status_aktif' => 'boolean',
    ];
}
