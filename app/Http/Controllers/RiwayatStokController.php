<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\RiwayatStok;
use Illuminate\Http\Request;

class RiwayatStokController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;
        $jenis = $request->jenis_pergerakan;
        $idBarang = $request->id_barang;
        $tanggalMulai = $request->tanggal_mulai;
        $tanggalSelesai = $request->tanggal_selesai;
        $tipeRiwayat = $request->tipe_riwayat;
        $statusBarang = $request->status_barang;
        $tipeHarga = $request->tipe_harga;
        $statusPpn = $request->status_ppn;

        $query = $this->queryRiwayatStok($request);

        $riwayatUntukTotal = (clone $query)->get();
        $ringkasan = $this->hitungRingkasanRiwayatStok($riwayatUntukTotal);

        $riwayatStok = $query
            ->orderBy('tanggal', 'desc')
            ->orderBy('created_at', 'desc')
            ->orderBy('id_riwayat_stok', 'desc')
            ->paginate(10)
            ->withQueryString();

        $barang = Barang::orderBy('nama_barang')->get();

        return view('riwayat-stok.index', array_merge([
            'riwayatStok' => $riwayatStok,
            'barang' => $barang,
            'search' => $search,
            'jenis' => $jenis,
            'idBarang' => $idBarang,
            'tanggalMulai' => $tanggalMulai,
            'tanggalSelesai' => $tanggalSelesai,
            'tipeRiwayat' => $tipeRiwayat,
            'statusBarang' => $statusBarang,
            'tipeHarga' => $tipeHarga,
            'statusPpn' => $statusPpn,
        ], $ringkasan));
    }

    private function queryRiwayatStok(Request $request)
    {
        return RiwayatStok::with(['barang', 'user'])
            ->when($request->search, function ($query) use ($request) {
                $search = $request->search;

                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('sumber_transaksi', 'like', "%{$search}%")
                        ->orWhere('keterangan', 'like', "%{$search}%")
                        ->orWhereHas('barang', function ($barangQuery) use ($search) {
                            $barangQuery->where('kode_barang', 'like', "%{$search}%")
                                ->orWhere('nama_barang', 'like', "%{$search}%")
                                ->orWhere('satuan', 'like', "%{$search}%")
                                ->orWhere('satuan_hitung_harga', 'like', "%{$search}%")
                                ->orWhere('tipe_perhitungan_harga', 'like', "%{$search}%")
                                ->orWhere('keterangan', 'like', "%{$search}%");
                        })
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('nama_user', 'like', "%{$search}%")
                                ->orWhere('username', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->jenis_pergerakan, function ($query) use ($request) {
                $query->where('jenis_pergerakan', $request->jenis_pergerakan);
            })
            ->when($request->id_barang, function ($query) use ($request) {
                $query->where('id_barang', $request->id_barang);
            })
            ->when($request->tanggal_mulai, function ($query) use ($request) {
                $query->whereDate('tanggal', '>=', $request->tanggal_mulai);
            })
            ->when($request->tanggal_selesai, function ($query) use ($request) {
                $query->whereDate('tanggal', '<=', $request->tanggal_selesai);
            })
            ->when($request->tipe_riwayat === 'opname', function ($query) {
                $query->where('sumber_transaksi', 'like', 'STOCK-OPNAME%');
            })
            ->when($request->tipe_riwayat === 'non_opname', function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->whereNull('sumber_transaksi')
                        ->orWhere('sumber_transaksi', 'not like', 'STOCK-OPNAME%');
                });
            })
            ->when($request->status_barang !== null && $request->status_barang !== '', function ($query) use ($request) {
                $query->whereHas('barang', function ($barangQuery) use ($request) {
                    $barangQuery->where('status_aktif', $request->status_barang);
                });
            })
            ->when($request->tipe_harga === 'normal', function ($query) {
                $query->whereHas('barang', function ($barangQuery) {
                    $barangQuery->where(function ($subQuery) {
                        $subQuery->where('tipe_perhitungan_harga', 'normal')
                            ->orWhereNull('tipe_perhitungan_harga');
                    });
                });
            })
            ->when($request->tipe_harga === 'isi_kemasan', function ($query) {
                $query->whereHas('barang', function ($barangQuery) {
                    $barangQuery->where('tipe_perhitungan_harga', 'isi_kemasan');
                });
            })
            ->when($request->status_ppn === 'kena_ppn', function ($query) {
                $query->whereHas('barang', function ($barangQuery) {
                    $barangQuery->where(function ($subQuery) {
                        $subQuery->where('kena_ppn', true)
                            ->orWhereNull('kena_ppn');
                    });
                });
            })
            ->when($request->status_ppn === 'non_ppn', function ($query) {
                $query->whereHas('barang', function ($barangQuery) {
                    $barangQuery->where('kena_ppn', false);
                });
            });
    }

    private function hitungRingkasanRiwayatStok($riwayatStok): array
    {
        $totalData = $riwayatStok->count();

        $totalMasuk = $riwayatStok
            ->where('jenis_pergerakan', 'masuk')
            ->sum('jumlah');

        $totalKeluar = $riwayatStok
            ->where('jenis_pergerakan', 'keluar')
            ->sum('jumlah');

        $totalPenyesuaian = $riwayatStok
            ->where('jenis_pergerakan', 'penyesuaian')
            ->count();

        $totalJumlahPenyesuaian = $riwayatStok
            ->where('jenis_pergerakan', 'penyesuaian')
            ->sum('jumlah');

        $totalOpname = $riwayatStok
            ->filter(function ($item) {
                return str_starts_with((string) $item->sumber_transaksi, 'STOCK-OPNAME');
            })
            ->count();

        $totalNonOpname = $riwayatStok
            ->filter(function ($item) {
                return !str_starts_with((string) $item->sumber_transaksi, 'STOCK-OPNAME');
            })
            ->count();

        $totalTransaksiMasuk = $riwayatStok
            ->where('jenis_pergerakan', 'masuk')
            ->count();

        $totalTransaksiKeluar = $riwayatStok
            ->where('jenis_pergerakan', 'keluar')
            ->count();

        $totalBarangUnik = $riwayatStok
            ->pluck('id_barang')
            ->filter()
            ->unique()
            ->count();

        $totalBarangNormal = $riwayatStok
            ->filter(function ($item) {
                return ($item->barang->tipe_perhitungan_harga ?? 'normal') === 'normal';
            })
            ->pluck('id_barang')
            ->filter()
            ->unique()
            ->count();

        $totalBarangIsiKemasan = $riwayatStok
            ->filter(function ($item) {
                return ($item->barang->tipe_perhitungan_harga ?? 'normal') === 'isi_kemasan';
            })
            ->pluck('id_barang')
            ->filter()
            ->unique()
            ->count();

        $totalBarangKenaPpn = $riwayatStok
            ->filter(function ($item) {
                return (bool) ($item->barang->kena_ppn ?? true);
            })
            ->pluck('id_barang')
            ->filter()
            ->unique()
            ->count();

        $totalBarangNonPpn = $riwayatStok
            ->filter(function ($item) {
                return !(bool) ($item->barang->kena_ppn ?? true);
            })
            ->pluck('id_barang')
            ->filter()
            ->unique()
            ->count();

        $totalSelisihBertambah = $riwayatStok
            ->filter(function ($item) {
                return ((int) $item->stok_sesudah - (int) $item->stok_sebelum) > 0;
            })
            ->sum(function ($item) {
                return (int) $item->stok_sesudah - (int) $item->stok_sebelum;
            });

        $totalSelisihBerkurang = $riwayatStok
            ->filter(function ($item) {
                return ((int) $item->stok_sesudah - (int) $item->stok_sebelum) < 0;
            })
            ->sum(function ($item) {
                return abs((int) $item->stok_sesudah - (int) $item->stok_sebelum);
            });

        $nettoPerubahan = $totalSelisihBertambah - $totalSelisihBerkurang;

        return compact(
            'totalData',
            'totalMasuk',
            'totalKeluar',
            'totalPenyesuaian',
            'totalJumlahPenyesuaian',
            'totalOpname',
            'totalNonOpname',
            'totalTransaksiMasuk',
            'totalTransaksiKeluar',
            'totalBarangUnik',
            'totalBarangNormal',
            'totalBarangIsiKemasan',
            'totalBarangKenaPpn',
            'totalBarangNonPpn',
            'totalSelisihBertambah',
            'totalSelisihBerkurang',
            'nettoPerubahan'
        );
    }
}
