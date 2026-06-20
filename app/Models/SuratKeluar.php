<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuratKeluar extends Model
{
    use HasFactory;

    protected $table = 'surat_keluar';
    protected $primaryKey = 'id_surat';

    protected $fillable = [
        'nomor_surat',
        'tanggal_surat',
        'jenis_surat',
        'tujuan',
        'alamat_tujuan',
        'perihal',
        'lampiran',
        'pembuka',
        'isi_surat',
        'penutup',
        'kota_ttd',
        'nama_penandatangan',
        'jabatan_penandatangan',
        'status_surat',
        'catatan_internal',
        'dibuat_oleh',
    ];

    protected $casts = [
        'tanggal_surat' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'dibuat_oleh', 'id_user');
    }
}
