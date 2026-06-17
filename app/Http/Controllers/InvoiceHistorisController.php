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
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Schema;

class InvoiceHistorisController extends Controller
{
    public function index()
    {
        $pembelianHistoris = Pembelian::with('supplier')
            ->where('is_historical', true)
            ->orderBy('tanggal_pembelian', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $penjualanHistoris = Penjualan::with('customer')
            ->where('is_historical', true)
            ->orderBy('tanggal_penjualan', 'desc')
            ->orderBy('created_at', 'desc')
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
            'nomor_dokumen_asli' => [
                'required',
                'string',
                'max:255',
                Rule::unique('pembelian', 'nomor_dokumen_asli'),
            ],
            'nomor_delivery_order' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('pembelian', 'nomor_delivery_order'),
            ],
            'nomor_surat_jalan' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('pembelian', 'nomor_surat_jalan'),
            ],
            'tanggal_pembelian' => 'required|date',
            'id_supplier' => 'required|exists:suppliers,id_supplier',

            'nilai_pajak' => 'nullable|numeric|min:0',
            'biaya_lain' => 'nullable|numeric|min:0',
            'potongan_diskon' => 'nullable|numeric|min:0',
            'keterangan_penyesuaian_total' => 'nullable|string',
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
                    throw ValidationException::withMessages([
                        'jumlah' => 'Jumlah diterima tidak boleh lebih besar dari jumlah dipesan.',
                    ]);
                }

                $totalDipesan += $jumlahDipesan;
                $totalDiterima += $jumlahDiterima;
                $subtotalPembelian += $jumlahDiterima * $hargaBeli;
            }

            if ($totalDiterima <= 0) {
                throw ValidationException::withMessages([
                    'jumlah' => 'Minimal harus ada barang yang diterima.',
                ]);
            }

            $statusPenerimaan = $totalDiterima < $totalDipesan
                ? 'sebagian'
                : 'lengkap';

            $nilaiPajak = (float) ($request->nilai_pajak ?? 0);
            $biayaLain = (float) ($request->biaya_lain ?? 0);
            $potonganDiskon = (float) ($request->potongan_diskon ?? 0);

            $totalSebelumPotongan = $subtotalPembelian + $nilaiPajak + $biayaLain;

            if ($potonganDiskon > $totalSebelumPotongan) {
                throw ValidationException::withMessages([
                    'potongan_diskon' => 'Potongan/diskon tidak boleh lebih besar dari subtotal + PPN supplier + biaya lain.',
                ]);
            }

            $totalAkhir = $totalSebelumPotongan - $potonganDiskon;

            $persentasePajak = $subtotalPembelian > 0
                ? round(($nilaiPajak / $subtotalPembelian) * 100, 2)
                : 0;

            $pembelian = Pembelian::create([
                'nomor_pembelian' => $this->generateNomorPembelianHistoris(),
                'nomor_delivery_order' => $this->ubahKosongMenjadiNull($request->nomor_delivery_order),
                'nomor_surat_jalan' => $this->ubahKosongMenjadiNull($request->nomor_surat_jalan),
                'is_historical' => true,
                'affect_stock' => false,
                'status_penerimaan' => $statusPenerimaan,
                'nomor_dokumen_asli' => trim($request->nomor_dokumen_asli),
                'tanggal_pembelian' => $request->tanggal_pembelian,
                'id_supplier' => $request->id_supplier,
                'subtotal' => $subtotalPembelian,
                'persentase_pajak' => $persentasePajak,
                'nilai_pajak' => $nilaiPajak,
                'pajak_ditambahkan' => true,
                'biaya_lain' => $biayaLain,
                'potongan_diskon' => $potonganDiskon,
                'keterangan_penyesuaian_total' => $request->keterangan_penyesuaian_total,
                'total_akhir' => $totalAkhir,
                'catatan' => $request->catatan,
                'dibuat_oleh' => Auth::id(),
            ]);

            foreach ($request->id_barang as $index => $idBarang) {
                $jumlahDipesan = (int) $request->jumlah_dipesan[$index];
                $jumlahDiterima = (int) $request->jumlah[$index];
                $hargaBeli = (float) $request->harga_beli[$index];

                DetailPembelian::create([
                    'id_pembelian' => $pembelian->id_pembelian,
                    'id_barang' => $idBarang,
                    'jumlah_dipesan' => $jumlahDipesan,
                    'jumlah' => $jumlahDiterima,
                    'harga_beli' => $hargaBeli,
                    'subtotal' => $jumlahDiterima * $hargaBeli,
                ]);
            }
        });

        return redirect()
            ->route('invoice-historis.index')
            ->with('success', 'Invoice pembelian lama berhasil disimpan lengkap dengan PPN manual, biaya lain, dan potongan tanpa memengaruhi stok.');
    }

    public function showPembelian(Pembelian $pembelian)
    {
        $this->pastikanPembelianHistoris($pembelian);

        $pembelian->load([
            'supplier',
            'user',
            'detailPembelian.barang',
        ]);

        return view('pembelian.show', compact('pembelian'));
    }

    public function editPembelian(Pembelian $pembelian)
    {
        $this->pastikanPembelianHistoris($pembelian);

        $pembelian->load([
            'supplier',
            'detailPembelian.barang',
        ]);

        $suppliers = Supplier::where('status_aktif', true)
            ->orWhere('id_supplier', $pembelian->id_supplier)
            ->orderBy('nama_supplier')
            ->get();

        $barang = Barang::where('status_aktif', true)
            ->orWhereIn('id_barang', $pembelian->detailPembelian->pluck('id_barang'))
            ->orderBy('nama_barang')
            ->get();

        return view('invoice-historis.edit-pembelian', compact(
            'pembelian',
            'suppliers',
            'barang'
        ));
    }

    public function updatePembelian(Request $request, Pembelian $pembelian)
    {
        $this->pastikanPembelianHistoris($pembelian);

        $request->validate([
            'nomor_dokumen_asli' => [
                'required',
                'string',
                'max:255',
                Rule::unique('pembelian', 'nomor_dokumen_asli')
                    ->ignore($pembelian->id_pembelian, 'id_pembelian'),
            ],
            'nomor_delivery_order' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('pembelian', 'nomor_delivery_order')
                    ->ignore($pembelian->id_pembelian, 'id_pembelian'),
            ],
            'nomor_surat_jalan' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('pembelian', 'nomor_surat_jalan')
                    ->ignore($pembelian->id_pembelian, 'id_pembelian'),
            ],
            'tanggal_pembelian' => 'required|date',
            'id_supplier' => 'required|exists:suppliers,id_supplier',

            'nilai_pajak' => 'nullable|numeric|min:0',
            'biaya_lain' => 'nullable|numeric|min:0',
            'potongan_diskon' => 'nullable|numeric|min:0',
            'keterangan_penyesuaian_total' => 'nullable|string',
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

        DB::transaction(function () use ($request, $pembelian) {
            $subtotalPembelian = 0;
            $totalDipesan = 0;
            $totalDiterima = 0;

            foreach ($request->id_barang as $index => $idBarang) {
                $jumlahDipesan = (int) $request->jumlah_dipesan[$index];
                $jumlahDiterima = (int) $request->jumlah[$index];
                $hargaBeli = (float) $request->harga_beli[$index];

                if ($jumlahDiterima > $jumlahDipesan) {
                    throw ValidationException::withMessages([
                        'jumlah' => 'Jumlah diterima tidak boleh lebih besar dari jumlah dipesan.',
                    ]);
                }

                $totalDipesan += $jumlahDipesan;
                $totalDiterima += $jumlahDiterima;
                $subtotalPembelian += $jumlahDiterima * $hargaBeli;
            }

            if ($totalDiterima <= 0) {
                throw ValidationException::withMessages([
                    'jumlah' => 'Minimal harus ada barang yang diterima.',
                ]);
            }

            $statusPenerimaan = $totalDiterima < $totalDipesan
                ? 'sebagian'
                : 'lengkap';

            $nilaiPajak = (float) ($request->nilai_pajak ?? 0);
            $biayaLain = (float) ($request->biaya_lain ?? 0);
            $potonganDiskon = (float) ($request->potongan_diskon ?? 0);

            $totalSebelumPotongan = $subtotalPembelian + $nilaiPajak + $biayaLain;

            if ($potonganDiskon > $totalSebelumPotongan) {
                throw ValidationException::withMessages([
                    'potongan_diskon' => 'Potongan/diskon tidak boleh lebih besar dari subtotal + PPN supplier + biaya lain.',
                ]);
            }

            $totalAkhir = $totalSebelumPotongan - $potonganDiskon;

            $persentasePajak = $subtotalPembelian > 0
                ? round(($nilaiPajak / $subtotalPembelian) * 100, 2)
                : 0;

            $pembelian->update([
                'nomor_delivery_order' => $this->ubahKosongMenjadiNull($request->nomor_delivery_order),
                'nomor_surat_jalan' => $this->ubahKosongMenjadiNull($request->nomor_surat_jalan),
                'is_historical' => true,
                'affect_stock' => false,
                'status_penerimaan' => $statusPenerimaan,
                'nomor_dokumen_asli' => trim($request->nomor_dokumen_asli),
                'tanggal_pembelian' => $request->tanggal_pembelian,
                'id_supplier' => $request->id_supplier,
                'subtotal' => $subtotalPembelian,
                'persentase_pajak' => $persentasePajak,
                'nilai_pajak' => $nilaiPajak,
                'pajak_ditambahkan' => true,
                'biaya_lain' => $biayaLain,
                'potongan_diskon' => $potonganDiskon,
                'keterangan_penyesuaian_total' => $request->keterangan_penyesuaian_total,
                'total_akhir' => $totalAkhir,
                'catatan' => $request->catatan,
            ]);

            $pembelian->detailPembelian()->delete();

            foreach ($request->id_barang as $index => $idBarang) {
                $jumlahDipesan = (int) $request->jumlah_dipesan[$index];
                $jumlahDiterima = (int) $request->jumlah[$index];
                $hargaBeli = (float) $request->harga_beli[$index];

                DetailPembelian::create([
                    'id_pembelian' => $pembelian->id_pembelian,
                    'id_barang' => $idBarang,
                    'jumlah_dipesan' => $jumlahDipesan,
                    'jumlah' => $jumlahDiterima,
                    'harga_beli' => $hargaBeli,
                    'subtotal' => $jumlahDiterima * $hargaBeli,
                ]);
            }
        });

        return redirect()
            ->route('invoice-historis.pembelian.show', [
                'pembelian' => $pembelian->id_pembelian,
                'back_url' => route('invoice-historis.index'),
            ])
            ->with('success', 'Invoice pembelian lama berhasil diperbarui lengkap dengan PPN manual, biaya lain, dan potongan tanpa memengaruhi stok.');
    }

    public function exportPembelianExcel(Pembelian $pembelian)
    {
        $this->pastikanPembelianHistoris($pembelian);

        return app(PembelianController::class)->exportExcel($pembelian);
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
            'nomor_dokumen_asli' => [
                'required',
                'string',
                'max:255',
                Rule::unique('penjualan', 'nomor_dokumen_asli'),
            ],
            'tanggal_penjualan' => 'required|date',
            'id_customer' => 'required|exists:customers,id_customer',
            'mode_ppn' => 'required|in:tanpa_ppn,include,exclude',
            'butuh_faktur_pajak' => 'nullable|boolean',
            'nomor_faktur_pajak' => 'nullable|string|max:255',
            'tanggal_faktur_pajak' => 'nullable|date',
            'nama_faktur_pajak' => 'nullable|required_if:butuh_faktur_pajak,1|string|max:255',
            'npwp_faktur_pajak' => 'nullable|required_if:butuh_faktur_pajak,1|string|max:255',
            'alamat_faktur_pajak' => 'nullable|string',
            'jenis_penyesuaian_total' => 'nullable|in:tidak_ada,tambah,kurang',
            'nominal_penyesuaian_total' => 'nullable|numeric|min:0',
            'keterangan_penyesuaian_total' => 'nullable|string',
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
            $ringkasan = $this->hitungRingkasanPenjualanHistoris($request);
            $modePpn = $request->mode_ppn;
            $persentasePajak = $modePpn === 'tanpa_ppn' ? 0 : 11;
            $pajakDitambahkan = $modePpn === 'exclude';

            $penjualan = Penjualan::create([
                'nomor_invoice' => $this->generateNomorInvoiceHistoris(),
                'is_historical' => true,
                'affect_stock' => false,
                'nomor_dokumen_asli' => trim($request->nomor_dokumen_asli),
                'tanggal_penjualan' => $request->tanggal_penjualan,
                'id_customer' => $request->id_customer,
                'subtotal' => $ringkasan['subtotal'],
                'dpp_ppn' => $ringkasan['dpp_ppn'],
                'persentase_pajak' => $persentasePajak,
                'mode_ppn' => $modePpn,
                'nilai_pajak' => $ringkasan['nilai_pajak'],
                'pajak_ditambahkan' => $pajakDitambahkan,
                'butuh_faktur_pajak' => $request->boolean('butuh_faktur_pajak'),
                'nomor_faktur_pajak' => $request->boolean('butuh_faktur_pajak') ? $request->nomor_faktur_pajak : null,
                'tanggal_faktur_pajak' => $request->boolean('butuh_faktur_pajak') ? $request->tanggal_faktur_pajak : null,
                'nama_faktur_pajak' => $request->boolean('butuh_faktur_pajak') ? $request->nama_faktur_pajak : null,
                'npwp_faktur_pajak' => $request->boolean('butuh_faktur_pajak') ? $request->npwp_faktur_pajak : null,
                'alamat_faktur_pajak' => $request->boolean('butuh_faktur_pajak') ? $request->alamat_faktur_pajak : null,
                'total_sebelum_penyesuaian' => $ringkasan['total_sebelum_penyesuaian'],
                'jenis_penyesuaian_total' => $ringkasan['jenis_penyesuaian_total'],
                'nominal_penyesuaian_total' => $ringkasan['nominal_penyesuaian_total'],
                'keterangan_penyesuaian_total' => $ringkasan['keterangan_penyesuaian_total'],
                'total_akhir' => $ringkasan['total_akhir'],
                'metode_pembayaran' => $request->metode_pembayaran,
                'status_pembayaran' => $request->metode_pembayaran === 'tunai' ? 'lunas' : 'belum_lunas',
                'tanggal_jatuh_tempo' => $request->metode_pembayaran === 'kredit' ? $request->tanggal_jatuh_tempo : null,
                'catatan' => $request->catatan,
                'dibuat_oleh' => Auth::id(),
            ]);

            $this->isiKolomOpsionalPenjualanHistoris($penjualan, [
                'subtotal_kena_ppn' => $ringkasan['subtotal_kena_ppn'],
                'subtotal_non_ppn' => $ringkasan['subtotal_non_ppn'],
            ]);

            foreach ($ringkasan['details'] as $detail) {
                $this->buatDetailPenjualanHistoris($penjualan, $detail['barang'], $detail['jumlah'], $detail['harga_jual'], $modePpn);
            }

            if ($request->metode_pembayaran === 'kredit') {
                Piutang::create([
                    'id_penjualan' => $penjualan->id_penjualan,
                    'nomor_invoice' => $penjualan->nomor_dokumen_asli ?: $penjualan->nomor_invoice,
                    'id_customer' => $penjualan->id_customer,
                    'total_piutang' => $ringkasan['total_akhir'],
                    'total_dibayar' => 0,
                    'sisa_piutang' => $ringkasan['total_akhir'],
                    'tanggal_jatuh_tempo' => $request->tanggal_jatuh_tempo,
                    'status_piutang' => 'belum_lunas',
                    'catatan' => 'Piutang dari invoice penjualan lama sebelum sistem digitalisasi',
                ]);
            }
        });

        return redirect()
            ->route('invoice-historis.index')
            ->with('success', 'Invoice penjualan lama berhasil disimpan lengkap tanpa mengurangi stok.');
    }

    public function showPenjualan(Penjualan $penjualan)
    {
        $this->pastikanPenjualanHistoris($penjualan);

        $penjualan->load([
            'customer',
            'user',
            'detailPenjualan.barang',
            'piutang',
        ]);

        return view('penjualan.show', compact('penjualan'));
    }

    public function editPenjualan(Penjualan $penjualan)
    {
        $this->pastikanPenjualanHistoris($penjualan);

        $penjualan->load([
            'customer',
            'detailPenjualan.barang',
            'piutang.pembayaranPiutang',
        ]);

        $customers = Customer::where('status_aktif', true)
            ->orWhere('id_customer', $penjualan->id_customer)
            ->orderBy('nama_customer')
            ->get();

        $barang = Barang::where('status_aktif', true)
            ->orWhereIn('id_barang', $penjualan->detailPenjualan->pluck('id_barang'))
            ->orderBy('nama_barang')
            ->get();

        return view('invoice-historis.edit-penjualan', compact(
            'penjualan',
            'customers',
            'barang'
        ));
    }

    public function updatePenjualan(Request $request, Penjualan $penjualan)
    {
        $this->pastikanPenjualanHistoris($penjualan);

        $request->validate([
            'nomor_dokumen_asli' => [
                'required',
                'string',
                'max:255',
                Rule::unique('penjualan', 'nomor_dokumen_asli')->ignore($penjualan->id_penjualan, 'id_penjualan'),
            ],
            'tanggal_penjualan' => 'required|date',
            'id_customer' => 'required|exists:customers,id_customer',
            'mode_ppn' => 'required|in:tanpa_ppn,include,exclude',
            'butuh_faktur_pajak' => 'nullable|boolean',
            'nomor_faktur_pajak' => 'nullable|string|max:255',
            'tanggal_faktur_pajak' => 'nullable|date',
            'nama_faktur_pajak' => 'nullable|required_if:butuh_faktur_pajak,1|string|max:255',
            'npwp_faktur_pajak' => 'nullable|required_if:butuh_faktur_pajak,1|string|max:255',
            'alamat_faktur_pajak' => 'nullable|string',
            'jenis_penyesuaian_total' => 'nullable|in:tidak_ada,tambah,kurang',
            'nominal_penyesuaian_total' => 'nullable|numeric|min:0',
            'keterangan_penyesuaian_total' => 'nullable|string',
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

        DB::transaction(function () use ($request, $penjualan) {
            $penjualan->load('piutang');
            $totalDibayarLama = $penjualan->piutang ? (float) $penjualan->piutang->total_dibayar : 0;

            if ($request->metode_pembayaran === 'tunai' && $totalDibayarLama > 0) {
                throw ValidationException::withMessages([
                    'metode_pembayaran' => 'Invoice kredit yang sudah memiliki pembayaran piutang tidak bisa diubah menjadi tunai. Hapus/atur pembayaran piutang terlebih dahulu jika memang diperlukan.',
                ]);
            }

            $ringkasan = $this->hitungRingkasanPenjualanHistoris($request);
            $modePpn = $request->mode_ppn;
            $persentasePajak = $modePpn === 'tanpa_ppn' ? 0 : 11;
            $pajakDitambahkan = $modePpn === 'exclude';

            $statusPembayaran = $this->hitungStatusPembayaran($request->metode_pembayaran, $ringkasan['total_akhir'], $totalDibayarLama);

            $penjualan->update([
                'is_historical' => true,
                'affect_stock' => false,
                'nomor_dokumen_asli' => trim($request->nomor_dokumen_asli),
                'tanggal_penjualan' => $request->tanggal_penjualan,
                'id_customer' => $request->id_customer,
                'subtotal' => $ringkasan['subtotal'],
                'dpp_ppn' => $ringkasan['dpp_ppn'],
                'persentase_pajak' => $persentasePajak,
                'mode_ppn' => $modePpn,
                'nilai_pajak' => $ringkasan['nilai_pajak'],
                'pajak_ditambahkan' => $pajakDitambahkan,
                'butuh_faktur_pajak' => $request->boolean('butuh_faktur_pajak'),
                'nomor_faktur_pajak' => $request->boolean('butuh_faktur_pajak') ? $request->nomor_faktur_pajak : null,
                'tanggal_faktur_pajak' => $request->boolean('butuh_faktur_pajak') ? $request->tanggal_faktur_pajak : null,
                'nama_faktur_pajak' => $request->boolean('butuh_faktur_pajak') ? $request->nama_faktur_pajak : null,
                'npwp_faktur_pajak' => $request->boolean('butuh_faktur_pajak') ? $request->npwp_faktur_pajak : null,
                'alamat_faktur_pajak' => $request->boolean('butuh_faktur_pajak') ? $request->alamat_faktur_pajak : null,
                'total_sebelum_penyesuaian' => $ringkasan['total_sebelum_penyesuaian'],
                'jenis_penyesuaian_total' => $ringkasan['jenis_penyesuaian_total'],
                'nominal_penyesuaian_total' => $ringkasan['nominal_penyesuaian_total'],
                'keterangan_penyesuaian_total' => $ringkasan['keterangan_penyesuaian_total'],
                'total_akhir' => $ringkasan['total_akhir'],
                'metode_pembayaran' => $request->metode_pembayaran,
                'status_pembayaran' => $statusPembayaran,
                'tanggal_jatuh_tempo' => $request->metode_pembayaran === 'kredit' ? $request->tanggal_jatuh_tempo : null,
                'catatan' => $request->catatan,
            ]);

            $this->isiKolomOpsionalPenjualanHistoris($penjualan, [
                'subtotal_kena_ppn' => $ringkasan['subtotal_kena_ppn'],
                'subtotal_non_ppn' => $ringkasan['subtotal_non_ppn'],
            ]);

            $penjualan->detailPenjualan()->delete();

            foreach ($ringkasan['details'] as $detail) {
                $this->buatDetailPenjualanHistoris($penjualan, $detail['barang'], $detail['jumlah'], $detail['harga_jual'], $modePpn);
            }

            $this->sinkronkanPiutangHistorisSetelahEdit($penjualan, $request, $ringkasan['total_akhir'], $totalDibayarLama);
        });

        return redirect()
            ->route('invoice-historis.penjualan.show', [
                'penjualan' => $penjualan->id_penjualan,
                'back_url' => route('invoice-historis.index'),
            ])
            ->with('success', 'Invoice penjualan lama berhasil diperbarui lengkap tanpa memengaruhi stok.');
    }

    private function hitungRingkasanPenjualanHistoris(Request $request): array
    {
        $subtotalPenjualan = 0;
        $subtotalKenaPpn = 0;
        $subtotalNonPpn = 0;
        $details = [];

        foreach ($request->id_barang as $index => $idBarang) {
            $barang = Barang::findOrFail($idBarang);
            $jumlah = (int) $request->jumlah[$index];
            $hargaJual = (float) $request->harga_jual[$index];
            $subtotalDetail = $this->hitungSubtotalDetailPenjualan($barang, $jumlah, $hargaJual);
            $kenaPpn = (bool) ($barang->kena_ppn ?? true);

            $subtotalPenjualan += $subtotalDetail;
            if ($kenaPpn) $subtotalKenaPpn += $subtotalDetail;
            else $subtotalNonPpn += $subtotalDetail;

            $details[] = compact('barang', 'jumlah', 'hargaJual') + ['harga_jual' => $hargaJual];
        }

        if ($request->mode_ppn === 'tanpa_ppn') {
            $dppPpn = 0;
            $nilaiPajak = 0;
            $totalSebelumPenyesuaian = $subtotalPenjualan;
        } elseif ($request->mode_ppn === 'exclude') {
            $dppPpn = $subtotalKenaPpn;
            $nilaiPajak = $subtotalKenaPpn * 0.11;
            $totalSebelumPenyesuaian = $subtotalPenjualan + $nilaiPajak;
        } else {
            $dppPpn = $subtotalKenaPpn * 100 / 111;
            $nilaiPajak = $subtotalKenaPpn - $dppPpn;
            $totalSebelumPenyesuaian = $subtotalPenjualan;
        }

        $jenisPenyesuaian = $request->jenis_penyesuaian_total ?? 'tidak_ada';
        $nominalPenyesuaian = (float) ($request->nominal_penyesuaian_total ?? 0);

        if ($jenisPenyesuaian === 'tambah' && $nominalPenyesuaian > 0) {
            $totalAkhir = $totalSebelumPenyesuaian + $nominalPenyesuaian;
        } elseif ($jenisPenyesuaian === 'kurang' && $nominalPenyesuaian > 0) {
            $totalAkhir = max($totalSebelumPenyesuaian - $nominalPenyesuaian, 0);
        } else {
            $jenisPenyesuaian = 'tidak_ada';
            $nominalPenyesuaian = 0;
            $totalAkhir = $totalSebelumPenyesuaian;
        }

        return [
            'subtotal' => $subtotalPenjualan,
            'subtotal_kena_ppn' => $subtotalKenaPpn,
            'subtotal_non_ppn' => $subtotalNonPpn,
            'dpp_ppn' => $dppPpn,
            'nilai_pajak' => $nilaiPajak,
            'total_sebelum_penyesuaian' => $totalSebelumPenyesuaian,
            'jenis_penyesuaian_total' => $jenisPenyesuaian,
            'nominal_penyesuaian_total' => $nominalPenyesuaian,
            'keterangan_penyesuaian_total' => $jenisPenyesuaian === 'tidak_ada' ? null : $request->keterangan_penyesuaian_total,
            'total_akhir' => $totalAkhir,
            'details' => $details,
        ];
    }

    public function exportPenjualanExcel(Penjualan $penjualan)
    {
        $this->pastikanPenjualanHistoris($penjualan);

        return app(PenjualanController::class)->exportExcel($penjualan);
    }

    private function pastikanPembelianHistoris(Pembelian $pembelian): void
    {
        abort_unless((bool) $pembelian->is_historical, 404);
    }

    private function pastikanPenjualanHistoris(Penjualan $penjualan): void
    {
        abort_unless((bool) $penjualan->is_historical, 404);
    }

    private function hitungSubtotalDetailPenjualan(Barang $barang, int $jumlah, float $hargaJual): float
    {
        $tipePerhitunganHarga = $barang->tipe_perhitungan_harga ?? 'normal';

        if ($tipePerhitunganHarga === 'isi_kemasan') {
            $isiPerSatuan = (float) ($barang->isi_per_satuan ?? 1);

            return $jumlah * $isiPerSatuan * $hargaJual;
        }

        return $jumlah * $hargaJual;
    }

    private function buatDetailPenjualanHistoris(Penjualan $penjualan, Barang $barang, int $jumlah, float $hargaJual, string $modePpn = 'include'): void
    {
        $tipePerhitunganHarga = $barang->tipe_perhitungan_harga ?? 'normal';
        $satuanTransaksi = $barang->satuan;
        $satuanHitungHarga = $tipePerhitunganHarga === 'isi_kemasan' ? $barang->satuan_hitung_harga : $barang->satuan;
        $isiPerSatuan = $tipePerhitunganHarga === 'isi_kemasan' ? (float) ($barang->isi_per_satuan ?? 1) : 1;
        $subtotalDetail = $this->hitungSubtotalDetailPenjualan($barang, $jumlah, $hargaJual);
        $kenaPpn = (bool) ($barang->kena_ppn ?? true);

        if (!$kenaPpn || $modePpn === 'tanpa_ppn') {
            $dppPpn = 0;
            $nilaiPpn = 0;
        } elseif ($modePpn === 'exclude') {
            $dppPpn = $subtotalDetail;
            $nilaiPpn = $subtotalDetail * 0.11;
        } else {
            $dppPpn = $subtotalDetail * 100 / 111;
            $nilaiPpn = $subtotalDetail - $dppPpn;
        }

        $detail = DetailPenjualan::create([
            'id_penjualan' => $penjualan->id_penjualan,
            'id_barang' => $barang->id_barang,
            'jumlah' => $jumlah,
            'harga_jual' => $hargaJual,
            'tipe_perhitungan_harga' => $tipePerhitunganHarga,
            'satuan_transaksi' => $satuanTransaksi,
            'satuan_hitung_harga' => $satuanHitungHarga,
            'isi_per_satuan' => $isiPerSatuan,
            'subtotal' => $subtotalDetail,
        ]);

        $dataOpsional = [];
        if (\Illuminate\Support\Facades\Schema::hasColumn('detail_penjualan', 'kena_ppn')) $dataOpsional['kena_ppn'] = $kenaPpn;
        if (\Illuminate\Support\Facades\Schema::hasColumn('detail_penjualan', 'dpp_ppn')) $dataOpsional['dpp_ppn'] = $dppPpn;
        if (\Illuminate\Support\Facades\Schema::hasColumn('detail_penjualan', 'nilai_ppn')) $dataOpsional['nilai_ppn'] = $nilaiPpn;

        if (!empty($dataOpsional)) {
            $detail->forceFill($dataOpsional)->save();
        }
    }

    private function isiKolomOpsionalPenjualanHistoris(Penjualan $penjualan, array $data): void
    {
        $dataOpsional = [];
        foreach ($data as $kolom => $nilai) {
            if (\Illuminate\Support\Facades\Schema::hasColumn('penjualan', $kolom)) {
                $dataOpsional[$kolom] = $nilai;
            }
        }

        if (!empty($dataOpsional)) {
            $penjualan->forceFill($dataOpsional)->save();
        }
    }


    private function hitungStatusPembayaran(string $metodePembayaran, float $totalAkhir, float $totalDibayar): string
    {
        if ($metodePembayaran === 'tunai') {
            return 'lunas';
        }

        if ($totalDibayar >= $totalAkhir && $totalAkhir > 0) {
            return 'lunas';
        }

        if ($totalDibayar > 0) {
            return 'sebagian';
        }

        return 'belum_lunas';
    }

    private function hitungStatusPiutang(float $sisaPiutang, float $totalDibayar, ?string $tanggalJatuhTempo): string
    {
        if ($sisaPiutang <= 0) {
            return 'lunas';
        }

        if ($tanggalJatuhTempo && now()->toDateString() > $tanggalJatuhTempo) {
            return 'jatuh_tempo';
        }

        if ($totalDibayar > 0) {
            return 'sebagian_dibayar';
        }

        return 'belum_lunas';
    }

    private function sinkronkanPiutangHistorisSetelahEdit(Penjualan $penjualan, Request $request, float $totalAkhir, float $totalDibayarLama): void
    {
        $penjualan->load('piutang');

        if ($request->metode_pembayaran === 'tunai') {
            if ($penjualan->piutang && $totalDibayarLama <= 0) {
                $penjualan->piutang->delete();
            }

            return;
        }

        $sisaPiutang = max($totalAkhir - $totalDibayarLama, 0);

        $statusPiutang = $this->hitungStatusPiutang(
            $sisaPiutang,
            $totalDibayarLama,
            $request->tanggal_jatuh_tempo
        );

        if ($penjualan->piutang) {
            $penjualan->piutang->update([
                'nomor_invoice' => $penjualan->nomor_dokumen_asli ?: $penjualan->nomor_invoice,
                'id_customer' => $penjualan->id_customer,
                'total_piutang' => $totalAkhir,
                'total_dibayar' => $totalDibayarLama,
                'sisa_piutang' => $sisaPiutang,
                'tanggal_jatuh_tempo' => $request->tanggal_jatuh_tempo,
                'status_piutang' => $statusPiutang,
                'catatan' => 'Piutang diperbarui dari edit invoice penjualan lama',
            ]);

            return;
        }

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

    private function ubahKosongMenjadiNull(?string $value): ?string
    {
        $value = trim($value ?? '');

        return $value === '' ? null : $value;
    }
}
