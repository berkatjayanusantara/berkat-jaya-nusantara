<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailPenjualan extends Model
{
    use HasFactory;

    protected $table = 'detail_penjualan';
    protected $primaryKey = 'id_detail_penjualan';

    public $timestamps = false;

    protected $fillable = [
        'id_penjualan',
        'id_barang',
        'jumlah',
        'harga_jual',
        'tipe_perhitungan_harga',
        'satuan_transaksi',
        'satuan_hitung_harga',
        'isi_per_satuan',
        'subtotal',
    ];

    protected $casts = [
        'harga_jual' => 'decimal:2',
        'isi_per_satuan' => 'decimal:3',
        'subtotal' => 'decimal:2',
    ];

    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class, 'id_penjualan', 'id_penjualan');
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'id_barang', 'id_barang');
    }
}
