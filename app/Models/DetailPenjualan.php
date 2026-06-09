<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailPenjualan extends Model
{
    protected $table = 'detail_penjualan';
    protected $primaryKey = 'id_detail_penjualan';

    public $timestamps = false;

    protected $fillable = [
        'id_penjualan',
        'id_barang',
        'jumlah',
        'harga_jual',
        'subtotal',
    ];

    protected $casts = [
        'harga_jual' => 'decimal:2',
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
