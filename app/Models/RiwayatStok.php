<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiwayatStok extends Model
{
    protected $table = 'riwayat_stok';
    protected $primaryKey = 'id_riwayat_stok';

    public $timestamps = false;

    protected $fillable = [
        'id_barang',
        'tanggal',
        'jenis_pergerakan',
        'jumlah',
        'stok_sebelum',
        'stok_sesudah',
        'sumber_transaksi',
        'keterangan',
        'dibuat_oleh',
        'created_at',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'created_at' => 'datetime',
    ];

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'id_barang', 'id_barang');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'dibuat_oleh', 'id_user');
    }
}
