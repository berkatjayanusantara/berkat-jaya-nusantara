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
        'tipe_perhitungan_harga',
        'satuan_hitung_harga',
        'isi_per_satuan',
        'kena_ppn',
        'jenis_ppn',
        'keterangan',
        'status_aktif',
    ];

    protected $casts = [
        'status_aktif' => 'boolean',
        'kena_ppn' => 'boolean',
        'harga_beli_terakhir' => 'decimal:2',
        'harga_jual_default' => 'decimal:2',
        'isi_per_satuan' => 'decimal:3',
    ];
}
