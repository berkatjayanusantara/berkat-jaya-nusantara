<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PembayaranPiutang extends Model
{
    protected $table = 'pembayaran_piutang';
    protected $primaryKey = 'id_pembayaran';

    protected $fillable = [
        'id_piutang',
        'tanggal_pembayaran',
        'nominal_pembayaran',
        'metode_pembayaran',    
        'catatan',
        'dibuat_oleh',
    ];

    protected $casts = [
        'tanggal_pembayaran' => 'date',
        'nominal_pembayaran' => 'decimal:2',
    ];

    public function piutang()
    {
        return $this->belongsTo(Piutang::class, 'id_piutang', 'id_piutang');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'dibuat_oleh', 'id_user');
    }
}
