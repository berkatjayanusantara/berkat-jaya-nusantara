<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penjualan extends Model
{
    use HasFactory;

    protected $table = 'penjualan';
    protected $primaryKey = 'id_penjualan';

    protected $fillable = [
        'nomor_invoice',
        'is_historical',
        'affect_stock',
        'nomor_dokumen_asli',
        'tanggal_penjualan',
        'id_customer',
        'subtotal',
        'persentase_pajak',
        'nilai_pajak',
        'pajak_ditambahkan',
        'total_akhir',
        'metode_pembayaran',
        'status_pembayaran',
        'tanggal_jatuh_tempo',
        'catatan',
        'dibuat_oleh',
    ];

    protected $casts = [
        'tanggal_penjualan' => 'date',
        'tanggal_jatuh_tempo' => 'date',
        'subtotal' => 'decimal:2',
        'persentase_pajak' => 'decimal:2',
        'nilai_pajak' => 'decimal:2',
        'pajak_ditambahkan' => 'boolean',
        'total_akhir' => 'decimal:2',
        'is_historical' => 'boolean',
        'affect_stock' => 'boolean',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'id_customer', 'id_customer');
    }

    public function detailPenjualan()
    {
        return $this->hasMany(DetailPenjualan::class, 'id_penjualan', 'id_penjualan');
    }

    public function piutang()
    {
        return $this->hasOne(Piutang::class, 'id_penjualan', 'id_penjualan');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'dibuat_oleh', 'id_user');
    }
}
