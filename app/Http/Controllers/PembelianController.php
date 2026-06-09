<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\DetailPembelian;
use App\Models\Pembelian;
use App\Models\RiwayatStok;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PembelianController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;

        $pembelian = Pembelian::with(['supplier', 'user'])
            ->when($search, function ($query, $search) {
                $query->where('nomor_pembelian', 'like', "%{$search}%")
                    ->orWhereHas('supplier', function ($supplierQuery) use ($search) {
                        $supplierQuery->where('nama_supplier', 'like', "%{$search}%");
                    });
            })
            ->orderBy('tanggal_pembelian', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('pembelian.index', compact('pembelian', 'search'));
    }

    public function create()
    {
        $nomorPembelian = $this->generateNomorPembelian();

        $suppliers = Supplier::where('status_aktif', true)
            ->orderBy('nama_supplier')
            ->get();

        $barang = Barang::where('status_aktif', true)
            ->orderBy('nama_barang')
            ->get();

        return view('pembelian.create', compact(
            'nomorPembelian',
            'suppliers',
            'barang'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal_pembelian' => 'required|date',
            'id_supplier' => 'required|exists:suppliers,id_supplier',
            'persentase_pajak' => 'nullable|numeric|min:0|max:100',
            'catatan' => 'nullable|string',

            'id_barang' => 'required|array|min:1',
            'id_barang.*' => 'required|exists:barang,id_barang',

            'jumlah' => 'required|array|min:1',
            'jumlah.*' => 'required|integer|min:1',

            'harga_beli' => 'required|array|min:1',
            'harga_beli.*' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $subtotalPembelian = 0;

            foreach ($request->id_barang as $index => $idBarang) {
                $jumlah = (int) $request->jumlah[$index];
                $hargaBeli = (float) $request->harga_beli[$index];

                $subtotalPembelian += $jumlah * $hargaBeli;
            }

            $persentasePajak = $request->persentase_pajak ?? 0;
            $nilaiPajak = $subtotalPembelian * ($persentasePajak / 100);
            $totalAkhir = $subtotalPembelian + $nilaiPajak;

            $pembelian = Pembelian::create([
                'nomor_pembelian' => $this->generateNomorPembelian(),
                'tanggal_pembelian' => $request->tanggal_pembelian,
                'id_supplier' => $request->id_supplier,
                'subtotal' => $subtotalPembelian,
                'persentase_pajak' => $persentasePajak,
                'nilai_pajak' => $nilaiPajak,
                'total_akhir' => $totalAkhir,
                'catatan' => $request->catatan,
                'dibuat_oleh' => Auth::id(),
            ]);

            foreach ($request->id_barang as $index => $idBarang) {
                $barang = Barang::findOrFail($idBarang);

                $jumlah = (int) $request->jumlah[$index];
                $hargaBeli = (float) $request->harga_beli[$index];
                $subtotalDetail = $jumlah * $hargaBeli;

                DetailPembelian::create([
                    'id_pembelian' => $pembelian->id_pembelian,
                    'id_barang' => $barang->id_barang,
                    'jumlah' => $jumlah,
                    'harga_beli' => $hargaBeli,
                    'subtotal' => $subtotalDetail,
                ]);

                $stokSebelum = $barang->stok_saat_ini;
                $stokSesudah = $stokSebelum + $jumlah;

                $barang->update([
                    'stok_saat_ini' => $stokSesudah,
                    'harga_beli_terakhir' => $hargaBeli,
                ]);

                RiwayatStok::create([
                    'id_barang' => $barang->id_barang,
                    'tanggal' => $request->tanggal_pembelian,
                    'jenis_pergerakan' => 'masuk',
                    'jumlah' => $jumlah,
                    'stok_sebelum' => $stokSebelum,
                    'stok_sesudah' => $stokSesudah,
                    'sumber_transaksi' => $pembelian->nomor_pembelian,
                    'keterangan' => 'Stok masuk dari pembelian',
                    'dibuat_oleh' => Auth::id(),
                    'created_at' => now(),
                ]);
            }
        });

        return redirect()
            ->route('pembelian.index')
            ->with('success', 'Transaksi pembelian berhasil disimpan dan stok barang berhasil diperbarui.');
    }

    public function show(Pembelian $pembelian)
    {
        $pembelian->load([
            'supplier',
            'user',
            'detailPembelian.barang',
        ]);

        return view('pembelian.show', compact('pembelian'));
    }

    private function generateNomorPembelian()
    {
        $tanggal = now()->format('Ymd');

        $lastPembelian = Pembelian::whereDate('created_at', now()->toDateString())
            ->orderBy('id_pembelian', 'desc')
            ->first();

        if (!$lastPembelian) {
            return 'PB-' . $tanggal . '-0001';
        }

        $lastNumber = (int) substr($lastPembelian->nomor_pembelian, -4);
        $newNumber = $lastNumber + 1;

        return 'PB-' . $tanggal . '-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
