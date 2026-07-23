<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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
        'status_aktif'       => 'boolean',
        'kena_ppn'           => 'boolean',
        'harga_beli_terakhir' => 'decimal:2',
        'harga_jual_default' => 'decimal:2',
        'isi_per_satuan'     => 'decimal:3',
    ];

    /**
     * Filter barang berdasarkan jenis PPN.
     *
     * Nilai $jenisPpn yang valid: 'non_ppn' | 'ppn_normal' | 'ppn_dpp_nilai_lain'
     *
     * Cara pakai:
     *   Barang::byJenisPpn('non_ppn')->count();
     *   Barang::where('status_aktif', true)->byJenisPpn('ppn_normal')->get();
     */
    public function scopeByJenisPpn(Builder $query, string $jenisPpn): Builder
    {
        return match ($jenisPpn) {
            'non_ppn' => $query->where(function (Builder $q) {
                $q->where('jenis_ppn', 'non_ppn')
                    ->orWhere(function (Builder $legacy) {
                        $legacy->whereNull('jenis_ppn')->where('kena_ppn', false);
                    });
            }),

            'ppn_normal' => $query->where(function (Builder $q) {
                $q->where('jenis_ppn', 'ppn_normal')
                    ->orWhere(function (Builder $legacy) {
                        $legacy->whereNull('jenis_ppn')
                            ->where(function (Builder $kena) {
                                $kena->where('kena_ppn', true)->orWhereNull('kena_ppn');
                            });
                    });
            }),

            'ppn_dpp_nilai_lain' => $query->where('jenis_ppn', 'ppn_dpp_nilai_lain'),

            default => $query, // jenis tidak dikenal → tidak filter apapun
        };
    }
}

