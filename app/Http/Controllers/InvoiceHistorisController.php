<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Customer;
use App\Models\DetailPembelian;
use App\Models\DetailPenjualan;
use App\Models\Pembelian;
use App\Models\Penjualan;
use App\Models\Piutang;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InvoiceHistorisController extends Controller
{
    public function index()
    {
        $pembelianHistoris = Pembelian::with('supplier')
            ->where('is_historical', true)
            ->orderBy('tanggal_pembelian', 'desc')
            ->limit(10)
            ->get();

        $penjualanHistoris = Penjualan::with('customer')
            ->where('is_historical', true)
            ->orderBy('tanggal_penjualan', 'desc')
            ->limit(10)
            ->get();

        return view('invoice-historis.index', compact(
            'pembelianHistoris',
            'penjualanHistoris'
        ));
    }

    public function createPembelian()
    {
        $nomorPembelian = $this->generateNomorPembelianHistoris();

        $suppliers = Supplier::where('status_aktif', true)
            ->orderBy('nama_supplier')
            ->get();

        $barang = Barang::where('status_aktif', true)
            ->orderBy('nama_barang')
            ->get();

        return view('invoice-historis.create-pembelian', compact(
            'nomorPembelian',
            'suppliers',
            'barang'
        ));
    }

    public function storePembelian(Request $request)
    {
        $request->validate([
            'nomor_dokumen_asli' => 'required|string|max:255',
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
                $subtotalPembelian += (int) $request->jumlah[$index] * (float) $request->harga_beli[$index];
            }

            $persentasePajak = $request->persentase_pajak ?? 0;
            $nilaiPajak = $subtotalPembelian * ($persentasePajak / 100);
            $totalAkhir = $subtotalPembelian + $nilaiPajak;

            $pembelian = Pembelian::create([
                'nomor_pembelian' => $this->generateNomorPembelianHistoris(),
                'is_historical' => true,
                'affect_stock' => false,
                'nomor_dokumen_asli' => $request->nomor_dokumen_asli,
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
                $jumlah = (int) $request->jumlah[$index];
                $hargaBeli = (float) $request->harga_beli[$index];

                DetailPembelian::create([
                    'id_pembelian' => $pembelian->id_pembelian,
                    'id_barang' => $idBarang,
                    'jumlah' => $jumlah,
                    'harga_beli' => $hargaBeli,
                    'subtotal' => $jumlah * $hargaBeli,
                ]);
            }
        });

        return redirect()
            ->route('invoice-historis.index')
            ->with('success', 'Invoice pembelian lama berhasil disimpan tanpa memengaruhi stok.');
    }

    public function createPenjualan()
    {
        $nomorInvoice = $this->generateNomorInvoiceHistoris();

        $customers = Customer::where('status_aktif', true)
            ->orderBy('nama_customer')
            ->get();

        $barang = Barang::where('status_aktif', true)
            ->orderBy('nama_barang')
            ->get();

        return view('invoice-historis.create-penjualan', compact(
            'nomorInvoice',
            'customers',
            'barang'
        ));
    }

    public function storePenjualan(Request $request)
    {
        $request->validate([
            'nomor_dokumen_asli' => 'required|string|max:255',
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
                $subtotalPenjualan += (int) $request->jumlah[$index] * (float) $request->harga_jual[$index];
            }

            $persentasePajak = $request->persentase_pajak ?? 0;
            $nilaiPajak = $subtotalPenjualan * ($persentasePajak / 100);
            $totalAkhir = $subtotalPenjualan + $nilaiPajak;

            $statusPembayaran = $request->metode_pembayaran === 'tunai'
                ? 'lunas'
                : 'belum_lunas';

            $penjualan = Penjualan::create([
                'nomor_invoice' => $this->generateNomorInvoiceHistoris(),
                'is_historical' => true,
                'affect_stock' => false,
                'nomor_dokumen_asli' => $request->nomor_dokumen_asli,
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
                $jumlah = (int) $request->jumlah[$index];
                $hargaJual = (float) $request->harga_jual[$index];

                DetailPenjualan::create([
                    'id_penjualan' => $penjualan->id_penjualan,
                    'id_barang' => $idBarang,
                    'jumlah' => $jumlah,
                    'harga_jual' => $hargaJual,
                    'subtotal' => $jumlah * $hargaJual,
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
                    'catatan' => 'Piutang dari invoice penjualan lama sebelum sistem digitalisasi',
                ]);
            }
        });

        return redirect()
            ->route('invoice-historis.index')
            ->with('success', 'Invoice penjualan lama berhasil disimpan tanpa mengurangi stok.');
    }

    private function generateNomorPembelianHistoris()
    {
        $tanggal = now()->format('Ymd');

        $lastPembelian = Pembelian::where('is_historical', true)
            ->whereDate('created_at', now()->toDateString())
            ->orderBy('id_pembelian', 'desc')
            ->first();

        if (!$lastPembelian) {
            return 'HPB-' . $tanggal . '-0001';
        }

        $lastNumber = (int) substr($lastPembelian->nomor_pembelian, -4);

        return 'HPB-' . $tanggal . '-' . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
    }

    private function generateNomorInvoiceHistoris()
    {
        $tanggal = now()->format('Ymd');

        $lastPenjualan = Penjualan::where('is_historical', true)
            ->whereDate('created_at', now()->toDateString())
            ->orderBy('id_penjualan', 'desc')
            ->first();

        if (!$lastPenjualan) {
            return 'HINV-' . $tanggal . '-0001';
        }

        $lastNumber = (int) substr($lastPenjualan->nomor_invoice, -4);

        return 'HINV-' . $tanggal . '-' . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
    }
}
