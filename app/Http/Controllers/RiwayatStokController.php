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

        $riwayatStok = RiwayatStok::with(['barang', 'user'])
            ->when($search, function ($query, $search) {
                $query->where('sumber_transaksi', 'like', "%{$search}%")
                    ->orWhere('keterangan', 'like', "%{$search}%")
                    ->orWhereHas('barang', function ($barangQuery) use ($search) {
                        $barangQuery->where('nama_barang', 'like', "%{$search}%")
                            ->orWhere('kode_barang', 'like', "%{$search}%");
                    });
            })
            ->when($jenis, function ($query, $jenis) {
                $query->where('jenis_pergerakan', $jenis);
            })
            ->when($idBarang, function ($query, $idBarang) {
                $query->where('id_barang', $idBarang);
            })
            ->when($tanggalMulai, function ($query, $tanggalMulai) {
                $query->whereDate('tanggal', '>=', $tanggalMulai);
            })
            ->when($tanggalSelesai, function ($query, $tanggalSelesai) {
                $query->whereDate('tanggal', '<=', $tanggalSelesai);
            })
            ->orderBy('tanggal', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        $barang = Barang::orderBy('nama_barang')->get();

        return view('riwayat-stok.index', compact(
            'riwayatStok',
            'barang',
            'search',
            'jenis',
            'idBarang',
            'tanggalMulai',
            'tanggalSelesai'
        ));
    }
}
