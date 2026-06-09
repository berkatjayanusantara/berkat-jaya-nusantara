<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pembelian extends Model
{
    protected $table = 'pembelian';
    protected $primaryKey = 'id_pembelian';

    protected $fillable = [
        'nomor_pembelian',
        'is_historical',
        'affect_stock',
        'nomor_dokumen_asli',
        'tanggal_pembelian',
        'id_supplier',
        'subtotal',
        'persentase_pajak',
        'nilai_pajak',
        'total_akhir',
        'catatan',
        'dibuat_oleh',
    ];

    protected $casts = [
        'tanggal_pembelian' => 'date',
        'subtotal' => 'decimal:2',
        'persentase_pajak' => 'decimal:2',
        'nilai_pajak' => 'decimal:2',
        'total_akhir' => 'decimal:2',
        'is_historical' => 'boolean',
        'affect_stock' => 'boolean',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'id_supplier', 'id_supplier');
    }

    public function detailPembelian()
    {
        return $this->hasMany(DetailPembelian::class, 'id_pembelian', 'id_pembelian');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'dibuat_oleh', 'id_user');
    }
}
