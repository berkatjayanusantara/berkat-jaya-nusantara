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
            'pajak_ditambahkan' => 'nullable|in:0,1',
            'catatan' => 'nullable|string',

            'id_barang' => 'required|array|min:1',
            'id_barang.*' => 'required|exists:barang,id_barang',

            'jumlah_dipesan' => 'required|array|min:1',
            'jumlah_dipesan.*' => 'required|integer|min:1',

            'jumlah' => 'required|array|min:1',
            'jumlah.*' => 'required|integer|min:0',

            'harga_beli' => 'required|array|min:1',
            'harga_beli.*' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $subtotalPembelian = 0;
            $totalDipesan = 0;
            $totalDiterima = 0;

            foreach ($request->id_barang as $index => $idBarang) {
                $jumlahDipesan = (int) $request->jumlah_dipesan[$index];
                $jumlahDiterima = (int) $request->jumlah[$index];
                $hargaBeli = (float) $request->harga_beli[$index];

                if ($jumlahDiterima > $jumlahDipesan) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'jumlah' => 'Jumlah diterima tidak boleh lebih besar dari jumlah dipesan.',
                    ]);
                }

                $totalDipesan += $jumlahDipesan;
                $totalDiterima += $jumlahDiterima;

                $subtotalPembelian += $jumlahDiterima * $hargaBeli;
            }

            if ($totalDiterima <= 0) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'jumlah' => 'Minimal harus ada barang yang diterima.',
                ]);
            }

            $statusPenerimaan = $totalDiterima < $totalDipesan
                ? 'sebagian'
                : 'lengkap';

            $persentasePajak = (float) ($request->persentase_pajak ?? 0);
            $nilaiPajak = $subtotalPembelian * ($persentasePajak / 100);

            $pajakDitambahkan = (bool) $request->boolean('pajak_ditambahkan');

            $totalAkhir = $pajakDitambahkan
                ? $subtotalPembelian + $nilaiPajak
                : $subtotalPembelian;

            $nomorPembelian = $this->generateNomorPembelian(true);

            $pembelian = Pembelian::create([
                'nomor_pembelian' => $nomorPembelian,
                'tanggal_pembelian' => $request->tanggal_pembelian,
                'id_supplier' => $request->id_supplier,
                'status_penerimaan' => $statusPenerimaan,
                'subtotal' => $subtotalPembelian,
                'persentase_pajak' => $persentasePajak,
                'nilai_pajak' => $nilaiPajak,
                'pajak_ditambahkan' => $pajakDitambahkan,
                'total_akhir' => $totalAkhir,
                'catatan' => $request->catatan,
                'dibuat_oleh' => Auth::id(),
            ]);

            foreach ($request->id_barang as $index => $idBarang) {
                $barang = Barang::findOrFail($idBarang);

                $jumlahDipesan = (int) $request->jumlah_dipesan[$index];
                $jumlahDiterima = (int) $request->jumlah[$index];
                $hargaBeli = (float) $request->harga_beli[$index];
                $subtotalDetail = $jumlahDiterima * $hargaBeli;

                DetailPembelian::create([
                    'id_pembelian' => $pembelian->id_pembelian,
                    'id_barang' => $barang->id_barang,
                    'jumlah_dipesan' => $jumlahDipesan,
                    'jumlah' => $jumlahDiterima,
                    'harga_beli' => $hargaBeli,
                    'subtotal' => $subtotalDetail,
                ]);

                if ($jumlahDiterima > 0) {
                    $stokSebelum = $barang->stok_saat_ini;
                    $stokSesudah = $stokSebelum + $jumlahDiterima;

                    $barang->update([
                        'stok_saat_ini' => $stokSesudah,
                        'harga_beli_terakhir' => $hargaBeli,
                    ]);

                    RiwayatStok::create([
                        'id_barang' => $barang->id_barang,
                        'tanggal' => $request->tanggal_pembelian,
                        'jenis_pergerakan' => 'masuk',
                        'jumlah' => $jumlahDiterima,
                        'stok_sebelum' => $stokSebelum,
                        'stok_sesudah' => $stokSesudah,
                        'sumber_transaksi' => $pembelian->nomor_pembelian,
                        'keterangan' => 'Stok masuk dari pembelian. Dipesan: ' . $jumlahDipesan . ', diterima: ' . $jumlahDiterima,
                        'dibuat_oleh' => Auth::id(),
                        'created_at' => now(),
                    ]);
                }
            }
        });

        return redirect()
            ->route('pembelian.index')
            ->with('success', 'Transaksi pembelian berhasil disimpan dan stok barang berhasil diperbarui sesuai jumlah diterima.');
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

    private function generateNomorPembelian(bool $lock = false)
    {
        $tanggal = now()->format('Ymd');
        $prefix = 'PB-' . $tanggal . '-';

        $query = Pembelian::where('nomor_pembelian', 'like', $prefix . '%')
            ->orderBy('nomor_pembelian', 'desc');

        if ($lock) {
            $query->lockForUpdate();
        }

        $lastPembelian = $query->first();

        if (!$lastPembelian) {
            return $prefix . '0001';
        }

        $lastNumber = (int) substr($lastPembelian->nomor_pembelian, -4);
        $newNumber = $lastNumber + 1;

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
