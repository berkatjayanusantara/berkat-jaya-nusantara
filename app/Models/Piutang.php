<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Piutang extends Model
{
    use HasFactory;

    protected $table = 'piutang';
    protected $primaryKey = 'id_piutang';

    protected $fillable = [
        'id_penjualan',
        'nomor_invoice',
        'id_customer',
        'total_piutang',
        'total_dibayar',
        'sisa_piutang',
        'tanggal_jatuh_tempo',
        'status_piutang',
        'catatan',
    ];

    protected $casts = [
        'total_piutang' => 'decimal:2',
        'total_dibayar' => 'decimal:2',
        'sisa_piutang' => 'decimal:2',
        'tanggal_jatuh_tempo' => 'date',
    ];

    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class, 'id_penjualan', 'id_penjualan');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'id_customer', 'id_customer');
    }

    public function pembayaranPiutang()
    {
        return $this->hasMany(PembayaranPiutang::class, 'id_piutang', 'id_piutang');
    }
}
