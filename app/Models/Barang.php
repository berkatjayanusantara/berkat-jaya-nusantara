<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    use HasFactory;

    protected $table = 'barang';
    protected $primaryKey = 'id_barang';

    protected $fillable = [
        'kode_barang',
        'nama_barang',
        'satuan',
        'stok_saat_ini',
        'harga_beli_terakhir',
        'harga_jual_default',
        'keterangan',
        'status_aktif',
    ];

    protected $casts = [
        'status_aktif' => 'boolean',
        'harga_beli_terakhir' => 'decimal:2',
        'harga_jual_default' => 'decimal:2',
    ];
}
