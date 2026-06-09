<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Customer;
use App\Models\DetailPenjualan;
use App\Models\Penjualan;
use App\Models\Piutang;
use App\Models\RiwayatStok;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PenjualanController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;

        $penjualan = Penjualan::with(['customer', 'user'])
            ->when($search, function ($query, $search) {
                $query->where('nomor_invoice', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($customerQuery) use ($search) {
                        $customerQuery->where('nama_customer', 'like', "%{$search}%");
                    });
            })
            ->orderBy('tanggal_penjualan', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('penjualan.index', compact('penjualan', 'search'));
    }

    public function create()
    {
        $nomorInvoice = $this->generateNomorInvoice();

        $customers = Customer::where('status_aktif', true)
            ->orderBy('nama_customer')
            ->get();

        $barang = Barang::where('status_aktif', true)
            ->orderBy('nama_barang')
            ->get();

        return view('penjualan.create', compact(
            'nomorInvoice',
            'customers',
            'barang'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal_penjualan' => 'required|date',
            'id_customer' => 'required|exists:customers,id_customer',
            'persentase_pajak' => 'nullable|numeric|min:0|max:100',
            'metode_pembayaran' => 'required|in:tunai,kredit',
            'tanggal_jatuh_tempo' => 'nullable|required_if:metode_pembayaran,kredit|date',
            'catatan' => 'nullable|string',

            'id_barang' => 'required|array|min:1',
            'id_barang.*' => 'required|exists:barang,id_barang',

            'jumlah' => 'required|array|min:1',
            'jumlah.*' => 'required|integer|min:1',

            'harga_jual' => 'required|array|min:1',
            'harga_jual.*' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $subtotalPenjualan = 0;

            foreach ($request->id_barang as $index => $idBarang) {
                $barang = Barang::findOrFail($idBarang);
                $jumlah = (int) $request->jumlah[$index];

                if ($jumlah > $barang->stok_saat_ini) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'stok' => 'Stok barang ' . $barang->nama_barang . ' tidak mencukupi. Stok tersedia: ' . $barang->stok_saat_ini,
                    ]);
                }

                $hargaJual = (float) $request->harga_jual[$index];
                $subtotalPenjualan += $jumlah * $hargaJual;
            }

            $persentasePajak = $request->persentase_pajak ?? 0;
            $nilaiPajak = $subtotalPenjualan * ($persentasePajak / 100);
            $totalAkhir = $subtotalPenjualan + $nilaiPajak;

            $statusPembayaran = $request->metode_pembayaran === 'tunai'
                ? 'lunas'
                : 'belum_lunas';

            $penjualan = Penjualan::create([
                'nomor_invoice' => $this->generateNomorInvoice(),
                'tanggal_penjualan' => $request->tanggal_penjualan,
                'id_customer' => $request->id_customer,
                'subtotal' => $subtotalPenjualan,
                'persentase_pajak' => $persentasePajak,
                'nilai_pajak' => $nilaiPajak,
                'total_akhir' => $totalAkhir,
                'metode_pembayaran' => $request->metode_pembayaran,
                'status_pembayaran' => $statusPembayaran,
                'tanggal_jatuh_tempo' => $request->tanggal_jatuh_tempo,
                'catatan' => $request->catatan,
                'dibuat_oleh' => Auth::id(),
            ]);

            foreach ($request->id_barang as $index => $idBarang) {
                $barang = Barang::findOrFail($idBarang);

                $jumlah = (int) $request->jumlah[$index];
                $hargaJual = (float) $request->harga_jual[$index];
                $subtotalDetail = $jumlah * $hargaJual;

                DetailPenjualan::create([
                    'id_penjualan' => $penjualan->id_penjualan,
                    'id_barang' => $barang->id_barang,
                    'jumlah' => $jumlah,
                    'harga_jual' => $hargaJual,
                    'subtotal' => $subtotalDetail,
                ]);

                $stokSebelum = $barang->stok_saat_ini;
                $stokSesudah = $stokSebelum - $jumlah;

                $barang->update([
                    'stok_saat_ini' => $stokSesudah,
                ]);

                RiwayatStok::create([
                    'id_barang' => $barang->id_barang,
                    'tanggal' => $request->tanggal_penjualan,
                    'jenis_pergerakan' => 'keluar',
                    'jumlah' => $jumlah,
                    'stok_sebelum' => $stokSebelum,
                    'stok_sesudah' => $stokSesudah,
                    'sumber_transaksi' => $penjualan->nomor_invoice,
                    'keterangan' => 'Stok keluar dari penjualan',
                    'dibuat_oleh' => Auth::id(),
                    'created_at' => now(),
                ]);
            }

            if ($request->metode_pembayaran === 'kredit') {
                Piutang::create([
                    'id_penjualan' => $penjualan->id_penjualan,
                    'nomor_invoice' => $penjualan->nomor_invoice,
                    'id_customer' => $penjualan->id_customer,
                    'total_piutang' => $totalAkhir,
                    'total_dibayar' => 0,
                    'sisa_piutang' => $totalAkhir,
                    'tanggal_jatuh_tempo' => $request->tanggal_jatuh_tempo,
                    'status_piutang' => 'belum_lunas',
                    'catatan' => 'Piutang otomatis dari transaksi penjualan kredit',
                ]);
            }
        });

        return redirect()
            ->route('penjualan.index')
            ->with('success', 'Transaksi penjualan berhasil disimpan.');
    }

    public function show(Penjualan $penjualan)
    {
        $penjualan->load([
            'customer',
            'user',
            'detailPenjualan.barang',
            'piutang',
        ]);

        return view('penjualan.show', compact('penjualan'));
    }

    private function generateNomorInvoice()
    {
        $tanggal = now()->format('Ymd');

        $lastPenjualan = Penjualan::whereDate('created_at', now()->toDateString())
            ->orderBy('id_penjualan', 'desc')
            ->first();

        if (!$lastPenjualan) {
            return 'INV-' . $tanggal . '-0001';
        }

        $lastNumber = (int) substr($lastPenjualan->nomor_invoice, -4);
        $newNumber = $lastNumber + 1;

        return 'INV-' . $tanggal . '-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
