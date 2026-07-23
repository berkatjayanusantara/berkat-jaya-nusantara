<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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
        'diskon_nominal',
        'tanggal_pengantaran',
        'tipe_perhitungan_harga',
        'satuan_transaksi',
        'satuan_hitung_harga',
        'isi_per_satuan',
        'kena_ppn',
        'jenis_ppn',
        'tarif_ppn',
        'dpp_ppn',
        'nilai_ppn',
        'subtotal',
    ];

    protected $casts = [
        'harga_jual' => 'decimal:2',
        'diskon_nominal' => 'decimal:2',
        'tanggal_pengantaran' => 'date',
        'isi_per_satuan' => 'decimal:3',
        'kena_ppn' => 'boolean',
        'tarif_ppn' => 'decimal:2',
        'dpp_ppn' => 'decimal:2',
        'nilai_ppn' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    /**
     * Filter detail penjualan berdasarkan jenis PPN.
     * Mendukung data lama (kena_ppn boolean) dan data baru (jenis_ppn enum).
     *
     * Nilai $jenisPpn yang valid: 'non_ppn' | 'ppn_normal' | 'ppn_dpp_nilai_lain'
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
                            ->where(function (Builder $k) {
                                $k->where('kena_ppn', true)->orWhereNull('kena_ppn');
                            });
                    });
            }),

            'ppn_dpp_nilai_lain' => $query->where('jenis_ppn', 'ppn_dpp_nilai_lain'),

            default => $query, // jenis tidak dikenal → tidak filter apapun
        };
    }

    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class, 'id_penjualan', 'id_penjualan');
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'id_barang', 'id_barang');
    }
}
