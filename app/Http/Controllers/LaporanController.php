<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Customer;
use App\Models\Pembelian;
use App\Models\Penjualan;
use App\Models\Piutang;
use App\Models\RiwayatStok;
use App\Models\Supplier;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    public function penjualan(Request $request)
    {
        $query = $this->queryLaporanPenjualan($request);

        $penjualanUntukTotal = (clone $query)->get();
        $ringkasan = $this->hitungRingkasanPenjualan($penjualanUntukTotal);

        $penjualan = $query
            ->orderBy('tanggal_penjualan', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(5)
            ->withQueryString();

        $customers = Customer::where('status_aktif', true)
            ->orderBy('nama_customer')
            ->get();

        return view('laporan.penjualan', array_merge([
            'penjualan' => $penjualan,
            'customers' => $customers,
        ], $ringkasan));
    }

    public function penjualanExportExcel(Request $request)
    {
        $penjualan = $this->queryLaporanPenjualan($request)
            ->orderBy('tanggal_penjualan', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $ringkasan = $this->hitungRingkasanPenjualan($penjualan);

        $tanggalAwal = $request->tanggal_awal ?: 'awal';
        $tanggalAkhir = $request->tanggal_akhir ?: 'akhir';

        $fileName = $this->namaFileLaporan('Laporan-Penjualan', $tanggalAwal, $tanggalAkhir, 'xls');

        return response()
            ->view('laporan.penjualan-excel', array_merge([
                'penjualan'    => $penjualan,
                'tanggalAwal'  => $tanggalAwal,
                'tanggalAkhir' => $tanggalAkhir,
            ], $ringkasan))
            ->header('Content-Type', 'application/vnd.ms-excel; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"')
            ->header('Cache-Control', 'max-age=0');
    }

    public function penjualanExportPdf(Request $request)
    {
        $penjualan = $this->queryLaporanPenjualan($request)
            ->orderBy('tanggal_penjualan', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $ringkasan = $this->hitungRingkasanPenjualan($penjualan);

        $tanggalAwal = $request->tanggal_awal ?: 'awal';
        $tanggalAkhir = $request->tanggal_akhir ?: 'akhir';

        $fileName = $this->namaFileLaporan('Laporan-Penjualan', $tanggalAwal, $tanggalAkhir, 'pdf');

        $pdf = Pdf::loadView('laporan.penjualan-pdf', array_merge([
            'penjualan'    => $penjualan,
            'tanggalAwal'  => $tanggalAwal,
            'tanggalAkhir' => $tanggalAkhir,
        ], $ringkasan))->setPaper('a4', 'landscape');

        return $pdf->download($fileName);
    }

    public function pembelian(Request $request)
    {
        $query = $this->queryLaporanPembelian($request);

        $pembelianUntukTotal = (clone $query)->get();
        $ringkasan = $this->hitungRingkasanPembelian($pembelianUntukTotal);

        $pembelian = $query
            ->orderBy('tanggal_pembelian', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(5)
            ->withQueryString();

        $suppliers = Supplier::where('status_aktif', true)
            ->orderBy('nama_supplier')
            ->get();

        return view('laporan.pembelian', array_merge([
            'pembelian' => $pembelian,
            'suppliers' => $suppliers,
        ], $ringkasan));
    }

    public function pembelianExportExcel(Request $request)
    {
        $pembelian = $this->queryLaporanPembelian($request)
            ->orderBy('tanggal_pembelian', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $ringkasan = $this->hitungRingkasanPembelian($pembelian);

        $tanggalAwal = $request->tanggal_awal ?: 'awal';
        $tanggalAkhir = $request->tanggal_akhir ?: 'akhir';

        $fileName = $this->namaFileLaporan('Laporan-Pembelian', $tanggalAwal, $tanggalAkhir, 'xls');

        return response()
            ->view('laporan.pembelian-excel', array_merge([
                'pembelian' => $pembelian,
                'tanggalAwal' => $tanggalAwal,
                'tanggalAkhir' => $tanggalAkhir,
            ], $ringkasan))
            ->header('Content-Type', 'application/vnd.ms-excel; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"')
            ->header('Cache-Control', 'max-age=0');
    }

    public function pembelianExportPdf(Request $request)
    {
        $pembelian = $this->queryLaporanPembelian($request)
            ->orderBy('tanggal_pembelian', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $ringkasan = $this->hitungRingkasanPembelian($pembelian);

        $tanggalAwal = $request->tanggal_awal ?: 'awal';
        $tanggalAkhir = $request->tanggal_akhir ?: 'akhir';

        $fileName = $this->namaFileLaporan('Laporan-Pembelian', $tanggalAwal, $tanggalAkhir, 'pdf');

        $pdf = Pdf::loadView('laporan.pembelian-pdf', array_merge([
            'pembelian' => $pembelian,
            'tanggalAwal' => $tanggalAwal,
            'tanggalAkhir' => $tanggalAkhir,
        ], $ringkasan))->setPaper('a4', 'landscape');

        return $pdf->download($fileName);
    }

    public function piutang(Request $request)
    {
        $query = $this->queryLaporanPiutang($request);

        $piutangUntukTotal = (clone $query)->get();
        $ringkasan = $this->hitungRingkasanPiutang($piutangUntukTotal);

        $piutang = $query
            ->orderBy('tanggal_jatuh_tempo', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate(5)
            ->withQueryString();

        $customers = Customer::where('status_aktif', true)
            ->orderBy('nama_customer')
            ->get();

        return view('laporan.piutang', array_merge([
            'piutang' => $piutang,
            'customers' => $customers,
        ], $ringkasan));
    }

    public function piutangExportExcel(Request $request)
    {
        $piutang = $this->queryLaporanPiutang($request)
            ->orderBy('tanggal_jatuh_tempo', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        $ringkasan = $this->hitungRingkasanPiutang($piutang);

        $tanggalAwal = $request->tanggal_awal ?: 'awal';
        $tanggalAkhir = $request->tanggal_akhir ?: 'akhir';

        $fileName = $this->namaFileLaporan('Laporan-Piutang', $tanggalAwal, $tanggalAkhir, 'xls');

        return response()
            ->view('laporan.piutang-excel', array_merge([
                'piutang' => $piutang,
                'tanggalAwal' => $tanggalAwal,
                'tanggalAkhir' => $tanggalAkhir,
            ], $ringkasan))
            ->header('Content-Type', 'application/vnd.ms-excel; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"')
            ->header('Cache-Control', 'max-age=0');
    }

    public function piutangExportPdf(Request $request)
    {
        $piutang = $this->queryLaporanPiutang($request)
            ->orderBy('tanggal_jatuh_tempo', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        $ringkasan = $this->hitungRingkasanPiutang($piutang);

        $tanggalAwal = $request->tanggal_awal ?: 'awal';
        $tanggalAkhir = $request->tanggal_akhir ?: 'akhir';

        $fileName = $this->namaFileLaporan('Laporan-Piutang', $tanggalAwal, $tanggalAkhir, 'pdf');

        $pdf = Pdf::loadView('laporan.piutang-pdf', array_merge([
            'piutang' => $piutang,
            'tanggalAwal' => $tanggalAwal,
            'tanggalAkhir' => $tanggalAkhir,
        ], $ringkasan))->setPaper('a4', 'landscape');

        return $pdf->download($fileName);
    }

    public function stokBarang(Request $request)
    {
        $batasStokRendah = (int) ($request->batas_stok_rendah ?? 5);
        $batasStokRendah = $batasStokRendah > 0 ? $batasStokRendah : 5;

        $query = $this->queryLaporanStokBarang($request, $batasStokRendah);

        $barangUntukTotal = (clone $query)->get();
        $ringkasan = $this->hitungRingkasanStokBarang($barangUntukTotal, $batasStokRendah);

        $barang = $query
            ->orderBy('nama_barang', 'asc')
            ->paginate(5)
            ->withQueryString();

        return view('laporan.stok-barang', array_merge([
            'barang' => $barang,
            'batasStokRendah' => $batasStokRendah,
        ], $ringkasan));
    }

    public function stokBarangExportExcel(Request $request)
    {
        $batasStokRendah = (int) ($request->batas_stok_rendah ?? 5);
        $batasStokRendah = $batasStokRendah > 0 ? $batasStokRendah : 5;

        $barang = $this->queryLaporanStokBarang($request, $batasStokRendah)
            ->orderBy('nama_barang', 'asc')
            ->get();

        $ringkasan = $this->hitungRingkasanStokBarang($barang, $batasStokRendah);
        $fileName = $this->namaFileLaporan('Laporan-Stok-Barang', 'semua', now()->format('Y-m-d'), 'xls');

        return response()
            ->view('laporan.stok-barang-excel', array_merge([
                'barang' => $barang,
                'batasStokRendah' => $batasStokRendah,
            ], $ringkasan))
            ->header('Content-Type', 'application/vnd.ms-excel; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"')
            ->header('Cache-Control', 'max-age=0');
    }

    public function stokBarangExportPdf(Request $request)
    {
        $batasStokRendah = (int) ($request->batas_stok_rendah ?? 5);
        $batasStokRendah = $batasStokRendah > 0 ? $batasStokRendah : 5;

        $barang = $this->queryLaporanStokBarang($request, $batasStokRendah)
            ->orderBy('nama_barang', 'asc')
            ->get();

        $ringkasan = $this->hitungRingkasanStokBarang($barang, $batasStokRendah);
        $fileName = $this->namaFileLaporan('Laporan-Stok-Barang', 'semua', now()->format('Y-m-d'), 'pdf');

        $pdf = Pdf::loadView('laporan.stok-barang-pdf', array_merge([
            'barang' => $barang,
            'batasStokRendah' => $batasStokRendah,
        ], $ringkasan))->setPaper('a4', 'landscape');

        return $pdf->download($fileName);
    }

    public function riwayatStok(Request $request)
    {
        $query = $this->queryLaporanRiwayatStok($request);

        $riwayatUntukTotal = (clone $query)->get();
        $ringkasan = $this->hitungRingkasanRiwayatStok($riwayatUntukTotal);

        $riwayatStok = $query
            ->orderBy('tanggal', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(5)
            ->withQueryString();

        $barang = Barang::orderBy('nama_barang')->get();

        return view('laporan.riwayat-stok', array_merge([
            'riwayatStok' => $riwayatStok,
            'barang' => $barang,
        ], $ringkasan));
    }

    public function riwayatStokExportExcel(Request $request)
    {
        $riwayatStok = $this->queryLaporanRiwayatStok($request)
            ->orderBy('tanggal', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $ringkasan = $this->hitungRingkasanRiwayatStok($riwayatStok);

        $tanggalAwal = $request->tanggal_awal ?: 'awal';
        $tanggalAkhir = $request->tanggal_akhir ?: 'akhir';

        $fileName = $this->namaFileLaporan('Laporan-Riwayat-Stok', $tanggalAwal, $tanggalAkhir, 'xls');

        return response()
            ->view('laporan.riwayat-stok-excel', array_merge([
                'riwayatStok' => $riwayatStok,
                'tanggalAwal' => $tanggalAwal,
                'tanggalAkhir' => $tanggalAkhir,
            ], $ringkasan))
            ->header('Content-Type', 'application/vnd.ms-excel; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"')
            ->header('Cache-Control', 'max-age=0');
    }

    public function riwayatStokExportPdf(Request $request)
    {
        $riwayatStok = $this->queryLaporanRiwayatStok($request)
            ->orderBy('tanggal', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $ringkasan = $this->hitungRingkasanRiwayatStok($riwayatStok);

        $tanggalAwal = $request->tanggal_awal ?: 'awal';
        $tanggalAkhir = $request->tanggal_akhir ?: 'akhir';

        $fileName = $this->namaFileLaporan('Laporan-Riwayat-Stok', $tanggalAwal, $tanggalAkhir, 'pdf');

        $pdf = Pdf::loadView('laporan.riwayat-stok-pdf', array_merge([
            'riwayatStok' => $riwayatStok,
            'tanggalAwal' => $tanggalAwal,
            'tanggalAkhir' => $tanggalAkhir,
        ], $ringkasan))->setPaper('a4', 'landscape');

        return $pdf->download($fileName);
    }

    private function queryLaporanPenjualan(Request $request)
    {
        return Penjualan::with([
            'customer',
            'user',
            'piutang',
            'detailPenjualan.barang',
        ])
            ->when($request->tanggal_awal, function ($query) use ($request) {
                $query->whereDate('tanggal_penjualan', '>=', $request->tanggal_awal);
            })
            ->when($request->tanggal_akhir, function ($query) use ($request) {
                $query->whereDate('tanggal_penjualan', '<=', $request->tanggal_akhir);
            })
            ->when($request->id_customer, function ($query) use ($request) {
                $query->where('id_customer', $request->id_customer);
            })
            ->when($request->metode_pembayaran, function ($query) use ($request) {
                $query->where('metode_pembayaran', $request->metode_pembayaran);
            })
            ->when($request->status_pembayaran, function ($query) use ($request) {
                $query->where('status_pembayaran', $request->status_pembayaran);
            })
            ->when($request->tipe_invoice === 'historis', function ($query) {
                $query->where('is_historical', true);
            })
            ->when($request->tipe_invoice === 'sistem', function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->where('is_historical', false)
                        ->orWhereNull('is_historical');
                });
            })
            ->when($request->pengaruh_stok === 'mempengaruhi', function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->where('affect_stock', true)
                        ->orWhereNull('affect_stock');
                });
            })
            ->when($request->pengaruh_stok === 'tidak_mempengaruhi', function ($query) {
                $query->where('affect_stock', false);
            })
            ->when($request->mode_ppn === 'tanpa_ppn', function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->where('mode_ppn', 'tanpa_ppn')
                        ->orWhere(function ($fallbackQuery) {
                            $fallbackQuery->whereNull('mode_ppn')
                                ->where(function ($pajakQuery) {
                                    $pajakQuery->whereNull('persentase_pajak')
                                        ->orWhere('persentase_pajak', '<=', 0);
                                });
                        });
                });
            })
            ->when($request->mode_ppn === 'include', function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->where('mode_ppn', 'include')
                        ->orWhere(function ($fallbackQuery) {
                            $fallbackQuery->whereNull('mode_ppn')
                                ->where('persentase_pajak', '>', 0)
                                ->where(function ($modeQuery) {
                                    $modeQuery->where('pajak_ditambahkan', false)
                                        ->orWhereNull('pajak_ditambahkan');
                                });
                        });
                });
            })
            ->when($request->mode_ppn === 'exclude', function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->where('mode_ppn', 'exclude')
                        ->orWhere(function ($fallbackQuery) {
                            $fallbackQuery->whereNull('mode_ppn')
                                ->where('persentase_pajak', '>', 0)
                                ->where('pajak_ditambahkan', true);
                        });
                });
            })
            ->when($request->butuh_faktur_pajak === 'ya', function ($query) {
                $query->where('butuh_faktur_pajak', true);
            })
            ->when($request->butuh_faktur_pajak === 'tidak', function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->where('butuh_faktur_pajak', false)
                        ->orWhereNull('butuh_faktur_pajak');
                });
            })
            ->when($request->jenis_penyesuaian_total === 'tidak_ada', function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->where('jenis_penyesuaian_total', 'tidak_ada')
                        ->orWhereNull('jenis_penyesuaian_total')
                        ->orWhere('nominal_penyesuaian_total', '<=', 0);
                });
            })
            ->when(in_array($request->jenis_penyesuaian_total, ['tambah', 'kurang'], true), function ($query) use ($request) {
                $query->where('jenis_penyesuaian_total', $request->jenis_penyesuaian_total)
                    ->where('nominal_penyesuaian_total', '>', 0);
            })
            ->when($request->tipe_harga === 'normal', function ($query) {
                $query->whereHas('detailPenjualan', function ($detailQuery) {
                    $detailQuery->where(function ($subQuery) {
                        $subQuery->where('tipe_perhitungan_harga', 'normal')
                            ->orWhereNull('tipe_perhitungan_harga');
                    });
                });
            })
            ->when($request->tipe_harga === 'isi_kemasan', function ($query) {
                $query->whereHas('detailPenjualan', function ($detailQuery) {
                    $detailQuery->where('tipe_perhitungan_harga', 'isi_kemasan');
                });
            })
            ->when($request->status_ppn_detail === 'kena_ppn', function ($query) {
                $query->whereHas('detailPenjualan', function ($detailQuery) {
                    $detailQuery->where('kena_ppn', true);
                });
            })
            ->when($request->status_ppn_detail === 'non_ppn', function ($query) {
                $query->whereHas('detailPenjualan', function ($detailQuery) {
                    $detailQuery->where(function ($subQuery) {
                        $subQuery->where('kena_ppn', false)
                            ->orWhereNull('kena_ppn');
                    });
                });
            })
            ->when($request->search, function ($query) use ($request) {
                $search = $request->search;

                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('nomor_invoice', 'like', "%{$search}%")
                        ->orWhere('nomor_dokumen_asli', 'like', "%{$search}%")
                        ->orWhere('nomor_faktur_pajak', 'like', "%{$search}%")
                        ->orWhere('nama_faktur_pajak', 'like', "%{$search}%")
                        ->orWhere('npwp_faktur_pajak', 'like', "%{$search}%")
                        ->orWhere('alamat_faktur_pajak', 'like', "%{$search}%")
                        ->orWhere('catatan', 'like', "%{$search}%")
                        ->orWhereHas('customer', function ($customerQuery) use ($search) {
                            $customerQuery->where('nama_customer', 'like', "%{$search}%")
                                ->orWhere('nomor_telepon', 'like', "%{$search}%")
                                ->orWhere('npwp', 'like', "%{$search}%")
                                ->orWhere('alamat', 'like', "%{$search}%");
                        })
                        ->orWhereHas('detailPenjualan.barang', function ($barangQuery) use ($search) {
                            $barangQuery->where('nama_barang', 'like', "%{$search}%")
                                ->orWhere('kode_barang', 'like', "%{$search}%")
                                ->orWhere('satuan', 'like', "%{$search}%")
                                ->orWhere('satuan_hitung_harga', 'like', "%{$search}%");
                        });
                });
            });
    }

    private function queryLaporanPembelian(Request $request)
    {
        return Pembelian::with(['supplier', 'user', 'detailPembelian.barang'])
            ->when($request->tanggal_awal, function ($query) use ($request) {
                $query->whereDate('tanggal_pembelian', '>=', $request->tanggal_awal);
            })
            ->when($request->tanggal_akhir, function ($query) use ($request) {
                $query->whereDate('tanggal_pembelian', '<=', $request->tanggal_akhir);
            })
            ->when($request->id_supplier, function ($query) use ($request) {
                $query->where('id_supplier', $request->id_supplier);
            })
            ->when($request->status_penerimaan, function ($query) use ($request) {
                $query->where('status_penerimaan', $request->status_penerimaan);
            })
            ->when($request->tipe_invoice === 'historis', function ($query) {
                $query->where('is_historical', true);
            })
            ->when($request->tipe_invoice === 'sistem', function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->where('is_historical', false)
                        ->orWhereNull('is_historical');
                });
            })
            ->when($request->pengaruh_stok === 'mempengaruhi', function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->where('affect_stock', true)
                        ->orWhereNull('affect_stock');
                });
            })
            ->when($request->pengaruh_stok === 'tidak_mempengaruhi', function ($query) {
                $query->where('affect_stock', false);
            })
            ->when($request->pajak_pembelian === 'dengan_pajak', function ($query) {
                $query->where('nilai_pajak', '>', 0);
            })
            ->when($request->pajak_pembelian === 'tanpa_pajak', function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->whereNull('nilai_pajak')
                        ->orWhere('nilai_pajak', '<=', 0);
                });
            })
            ->when($request->penyesuaian_total === 'ada_penyesuaian', function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->where('biaya_lain', '>', 0)
                        ->orWhere('potongan_diskon', '>', 0);
                });
            })
            ->when($request->penyesuaian_total === 'biaya_lain', function ($query) {
                $query->where('biaya_lain', '>', 0);
            })
            ->when($request->penyesuaian_total === 'diskon', function ($query) {
                $query->where('potongan_diskon', '>', 0);
            })
            ->when($request->penyesuaian_total === 'tanpa_penyesuaian', function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->where(function ($biayaQuery) {
                        $biayaQuery->whereNull('biaya_lain')
                            ->orWhere('biaya_lain', '<=', 0);
                    })->where(function ($diskonQuery) {
                        $diskonQuery->whereNull('potongan_diskon')
                            ->orWhere('potongan_diskon', '<=', 0);
                    });
                });
            })
            ->when($request->search, function ($query) use ($request) {
                $search = $request->search;

                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('nomor_pembelian', 'like', "%{$search}%")
                        ->orWhere('nomor_dokumen_asli', 'like', "%{$search}%")
                        ->orWhere('nomor_delivery_order', 'like', "%{$search}%")
                        ->orWhere('nomor_surat_jalan', 'like', "%{$search}%")
                        ->orWhere('catatan', 'like', "%{$search}%")
                        ->orWhere('keterangan_penyesuaian_total', 'like', "%{$search}%")
                        ->orWhereHas('supplier', function ($supplierQuery) use ($search) {
                            $supplierQuery->where('nama_supplier', 'like', "%{$search}%")
                                ->orWhere('nomor_telepon', 'like', "%{$search}%")
                                ->orWhere('npwp', 'like', "%{$search}%")
                                ->orWhere('alamat', 'like', "%{$search}%");
                        })
                        ->orWhereHas('detailPembelian.barang', function ($barangQuery) use ($search) {
                            $barangQuery->where('kode_barang', 'like', "%{$search}%")
                                ->orWhere('nama_barang', 'like', "%{$search}%");
                        });
                });
            });
    }

    private function queryLaporanPiutang(Request $request)
    {
        return Piutang::with(['customer', 'penjualan'])
            ->when($request->tanggal_awal, function ($query) use ($request) {
                $query->whereDate('tanggal_jatuh_tempo', '>=', $request->tanggal_awal);
            })
            ->when($request->tanggal_akhir, function ($query) use ($request) {
                $query->whereDate('tanggal_jatuh_tempo', '<=', $request->tanggal_akhir);
            })
            ->when($request->id_customer, function ($query) use ($request) {
                $query->where('id_customer', $request->id_customer);
            })
            ->when($request->status_piutang, function ($query) use ($request) {
                $query->where('status_piutang', $request->status_piutang);
            })
            ->when($request->jatuh_tempo === 'lewat', function ($query) {
                $query->where('status_piutang', '!=', 'lunas')
                    ->whereDate('tanggal_jatuh_tempo', '<', now()->toDateString());
            })
            ->when($request->jatuh_tempo === 'belum', function ($query) {
                $query->where('status_piutang', '!=', 'lunas')
                    ->whereDate('tanggal_jatuh_tempo', '>=', now()->toDateString());
            })
            ->when($request->tipe_invoice === 'historis', function ($query) {
                $query->whereHas('penjualan', function ($penjualanQuery) {
                    $penjualanQuery->where('is_historical', true);
                });
            })
            ->when($request->tipe_invoice === 'sistem', function ($query) {
                $query->whereHas('penjualan', function ($penjualanQuery) {
                    $penjualanQuery->where(function ($subQuery) {
                        $subQuery->where('is_historical', false)
                            ->orWhereNull('is_historical');
                    });
                });
            })
            ->when($request->search, function ($query) use ($request) {
                $search = $request->search;

                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('nomor_invoice', 'like', "%{$search}%")
                        ->orWhereHas('penjualan', function ($penjualanQuery) use ($search) {
                            $penjualanQuery->where('nomor_invoice', 'like', "%{$search}%")
                                ->orWhere('nomor_dokumen_asli', 'like', "%{$search}%");
                        })
                        ->orWhereHas('customer', function ($customerQuery) use ($search) {
                            $customerQuery->where('nama_customer', 'like', "%{$search}%")
                                ->orWhere('nomor_telepon', 'like', "%{$search}%")
                                ->orWhere('npwp', 'like', "%{$search}%")
                                ->orWhere('alamat', 'like', "%{$search}%");
                        });
                });
            });
    }

    private function queryLaporanStokBarang(Request $request, int $batasStokRendah)
    {
        return Barang::query()
            ->when($request->status_barang !== null && $request->status_barang !== '', function ($query) use ($request) {
                $query->where('status_aktif', $request->status_barang);
            })
            ->when($request->kondisi_stok === 'kosong', function ($query) {
                $query->where('stok_saat_ini', '<=', 0);
            })
            ->when($request->kondisi_stok === 'rendah', function ($query) use ($batasStokRendah) {
                $query->where('stok_saat_ini', '>', 0)
                    ->where('stok_saat_ini', '<=', $batasStokRendah);
            })
            ->when($request->kondisi_stok === 'tersedia', function ($query) use ($batasStokRendah) {
                $query->where('stok_saat_ini', '>', $batasStokRendah);
            })
            ->when($request->tipe_harga === 'normal', function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->where('tipe_perhitungan_harga', 'normal')
                        ->orWhereNull('tipe_perhitungan_harga');
                });
            })
            ->when($request->tipe_harga === 'isi_kemasan', function ($query) {
                $query->where('tipe_perhitungan_harga', 'isi_kemasan');
            })
            ->when($request->status_ppn === 'non_ppn', function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->where('jenis_ppn', 'non_ppn')
                        ->orWhere(function ($legacyQuery) {
                            $legacyQuery->whereNull('jenis_ppn')
                                ->where('kena_ppn', false);
                        });
                });
            })
            ->when($request->status_ppn === 'ppn_normal', function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->where('jenis_ppn', 'ppn_normal')
                        ->orWhere(function ($legacyQuery) {
                            $legacyQuery->whereNull('jenis_ppn')
                                ->where(function ($kenaPpnQuery) {
                                    $kenaPpnQuery->where('kena_ppn', true)
                                        ->orWhereNull('kena_ppn');
                                });
                        });
                });
            })
            ->when($request->status_ppn === 'ppn_dpp_nilai_lain', function ($query) {
                $query->where('jenis_ppn', 'ppn_dpp_nilai_lain');
            })
            ->when($request->status_ppn === 'kena_ppn', function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->whereIn('jenis_ppn', ['ppn_normal', 'ppn_dpp_nilai_lain'])
                        ->orWhere(function ($legacyQuery) {
                            $legacyQuery->whereNull('jenis_ppn')
                                ->where(function ($kenaPpnQuery) {
                                    $kenaPpnQuery->where('kena_ppn', true)
                                        ->orWhereNull('kena_ppn');
                                });
                        });
                });
            })
            ->when($request->search, function ($query) use ($request) {
                $search = $request->search;

                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('kode_barang', 'like', "%{$search}%")
                        ->orWhere('nama_barang', 'like', "%{$search}%")
                        ->orWhere('satuan', 'like', "%{$search}%")
                        ->orWhere('satuan_hitung_harga', 'like', "%{$search}%")
                        ->orWhere('tipe_perhitungan_harga', 'like', "%{$search}%")
                        ->orWhere('jenis_ppn', 'like', "%{$search}%")
                        ->orWhere('keterangan', 'like', "%{$search}%");
                });
            });
    }

    private function queryLaporanRiwayatStok(Request $request)
    {
        return RiwayatStok::with(['barang', 'user'])
            ->when($request->tanggal_awal, function ($query) use ($request) {
                $query->whereDate('tanggal', '>=', $request->tanggal_awal);
            })
            ->when($request->tanggal_akhir, function ($query) use ($request) {
                $query->whereDate('tanggal', '<=', $request->tanggal_akhir);
            })
            ->when($request->id_barang, function ($query) use ($request) {
                $query->where('id_barang', $request->id_barang);
            })
            ->when($request->jenis_pergerakan, function ($query) use ($request) {
                $query->where('jenis_pergerakan', $request->jenis_pergerakan);
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
            ->when($request->status_ppn_barang === 'non_ppn', function ($query) {
                $query->whereHas('barang', function ($barangQuery) {
                    $barangQuery->where(function ($subQuery) {
                        $subQuery->where('jenis_ppn', 'non_ppn')
                            ->orWhere(function ($legacyQuery) {
                                $legacyQuery->whereNull('jenis_ppn')
                                    ->where('kena_ppn', false);
                            });
                    });
                });
            })
            ->when($request->status_ppn_barang === 'ppn_normal', function ($query) {
                $query->whereHas('barang', function ($barangQuery) {
                    $barangQuery->where(function ($subQuery) {
                        $subQuery->where('jenis_ppn', 'ppn_normal')
                            ->orWhere(function ($legacyQuery) {
                                $legacyQuery->whereNull('jenis_ppn')
                                    ->where(function ($kenaPpnQuery) {
                                        $kenaPpnQuery->where('kena_ppn', true)
                                            ->orWhereNull('kena_ppn');
                                    });
                            });
                    });
                });
            })
            ->when($request->status_ppn_barang === 'ppn_dpp_nilai_lain', function ($query) {
                $query->whereHas('barang', function ($barangQuery) {
                    $barangQuery->where('jenis_ppn', 'ppn_dpp_nilai_lain');
                });
            })
            ->when($request->status_ppn_barang === 'kena_ppn', function ($query) {
                $query->whereHas('barang', function ($barangQuery) {
                    $barangQuery->where(function ($subQuery) {
                        $subQuery->whereIn('jenis_ppn', ['ppn_normal', 'ppn_dpp_nilai_lain'])
                            ->orWhere(function ($legacyQuery) {
                                $legacyQuery->whereNull('jenis_ppn')
                                    ->where(function ($kenaPpnQuery) {
                                        $kenaPpnQuery->where('kena_ppn', true)
                                            ->orWhereNull('kena_ppn');
                                    });
                            });
                    });
                });
            })
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
                                ->orWhere('jenis_ppn', 'like', "%{$search}%")
                                ->orWhere('keterangan', 'like', "%{$search}%");
                        })
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('nama_user', 'like', "%{$search}%")
                                ->orWhere('username', 'like', "%{$search}%");
                        });
                });
            });
    }

    private function hitungRingkasanPenjualan($penjualan): array
    {
        $totalTransaksi = $penjualan->count();
        $totalSubtotal = $penjualan->sum('subtotal');
        $totalSubtotalKenaPpn = $penjualan->sum('subtotal_kena_ppn');
        $totalSubtotalNonPpn = $penjualan->sum('subtotal_non_ppn');
        $totalDppPpn = $penjualan->sum('dpp_ppn');
        $totalPajak = $penjualan->sum('nilai_pajak');
        $totalSebelumPenyesuaian = $penjualan->sum(function ($item) {
            return (float) ($item->total_sebelum_penyesuaian ?? $item->total_akhir ?? 0);
        });
        $totalAkhir = $penjualan->sum('total_akhir');

        $totalTunai = $penjualan
            ->where('metode_pembayaran', 'tunai')
            ->sum('total_akhir');

        $totalKredit = $penjualan
            ->where('metode_pembayaran', 'kredit')
            ->sum('total_akhir');

        $totalHistoris = $penjualan
            ->filter(function ($item) {
                return (bool) ($item->is_historical ?? false);
            })
            ->count();

        $totalSistemBerjalan = $penjualan
            ->filter(function ($item) {
                return !(bool) ($item->is_historical ?? false);
            })
            ->count();

        $totalMempengaruhiStok = $penjualan
            ->filter(function ($item) {
                return (bool) ($item->affect_stock ?? true);
            })
            ->count();

        $totalTidakMempengaruhiStok = $penjualan
            ->filter(function ($item) {
                return !(bool) ($item->affect_stock ?? true);
            })
            ->count();

        $totalPiutang = $penjualan->sum(function ($item) {
            return $item->piutang->total_piutang ?? 0;
        });

        $totalDibayar = $penjualan->sum(function ($item) {
            return $item->piutang->total_dibayar ?? 0;
        });

        $totalSisaPiutang = $penjualan->sum(function ($item) {
            return $item->piutang->sisa_piutang ?? 0;
        });

        $totalPpnTanpaPpn      = 0;
        $totalPpnInclude       = 0;
        $totalPpnExclude       = 0;
        $totalNilaiPajakInclude = 0;
        $totalNilaiPajakExclude = 0;
        $totalButuhFakturPajak = 0;
        $totalTidakButuhFakturPajak = 0;
        $totalPenyesuaianTambah = 0;
        $totalPenyesuaianKurang = 0;

        $totalItemBarang        = 0;
        $totalJumlahTerjual     = 0;
        $totalBarangNormal      = 0;
        $totalBarangIsiKemasan  = 0;
        $totalNilaiBarangNormal = 0;
        $totalNilaiBarangIsiKemasan = 0;
        $totalItemKenaPpn       = 0;
        $totalItemNonPpn        = 0;
        $totalDppPpnDetail      = 0;
        $totalNilaiPpnDetail    = 0;

        foreach ($penjualan as $item) {
            $modePpn = $this->normalisasiModePpnLaporan($item->mode_ppn ?? null, $item);

            if ($modePpn === 'tanpa_ppn') {
                $totalPpnTanpaPpn++;
            } elseif ($modePpn === 'exclude') {
                $totalPpnExclude++;
                $totalNilaiPajakExclude += (float) ($item->nilai_pajak ?? 0);
            } else {
                $totalPpnInclude++;
                $totalNilaiPajakInclude += (float) ($item->nilai_pajak ?? 0);
            }

            if ((bool) ($item->butuh_faktur_pajak ?? false)) {
                $totalButuhFakturPajak++;
            } else {
                $totalTidakButuhFakturPajak++;
            }

            $jenisPenyesuaian = $item->jenis_penyesuaian_total ?? 'tidak_ada';
            $nominalPenyesuaian = (float) ($item->nominal_penyesuaian_total ?? 0);

            if ($jenisPenyesuaian === 'tambah' && $nominalPenyesuaian > 0) {
                $totalPenyesuaianTambah += $nominalPenyesuaian;
            } elseif ($jenisPenyesuaian === 'kurang' && $nominalPenyesuaian > 0) {
                $totalPenyesuaianKurang += $nominalPenyesuaian;
            }

            foreach ($item->detailPenjualan as $detail) {
                $totalItemBarang++;
                $totalJumlahTerjual += (float) $detail->jumlah;

                if (($detail->tipe_perhitungan_harga ?? 'normal') === 'isi_kemasan') {
                    $totalBarangIsiKemasan++;
                    $totalNilaiBarangIsiKemasan += (float) $detail->subtotal;
                } else {
                    $totalBarangNormal++;
                    $totalNilaiBarangNormal += (float) $detail->subtotal;
                }

                if ((bool) ($detail->kena_ppn ?? false)) {
                    $totalItemKenaPpn++;
                } else {
                    $totalItemNonPpn++;
                }

                $totalDppPpnDetail += (float) ($detail->dpp_ppn ?? 0);
                $totalNilaiPpnDetail += (float) ($detail->nilai_ppn ?? 0);
            }
        }

        $totalPenyesuaianBersih = $totalPenyesuaianTambah - $totalPenyesuaianKurang;

        return compact(
            'totalTransaksi',
            'totalSubtotal',
            'totalSubtotalKenaPpn',
            'totalSubtotalNonPpn',
            'totalDppPpn',
            'totalPajak',
            'totalSebelumPenyesuaian',
            'totalAkhir',
            'totalTunai',
            'totalKredit',
            'totalHistoris',
            'totalSistemBerjalan',
            'totalMempengaruhiStok',
            'totalTidakMempengaruhiStok',
            'totalPiutang',
            'totalDibayar',
            'totalSisaPiutang',
            'totalPpnTanpaPpn',
            'totalPpnInclude',
            'totalPpnExclude',
            'totalNilaiPajakInclude',
            'totalNilaiPajakExclude',
            'totalButuhFakturPajak',
            'totalTidakButuhFakturPajak',
            'totalPenyesuaianTambah',
            'totalPenyesuaianKurang',
            'totalPenyesuaianBersih',
            'totalItemBarang',
            'totalJumlahTerjual',
            'totalBarangNormal',
            'totalBarangIsiKemasan',
            'totalNilaiBarangNormal',
            'totalNilaiBarangIsiKemasan',
            'totalItemKenaPpn',
            'totalItemNonPpn',
            'totalDppPpnDetail',
            'totalNilaiPpnDetail'
        );
    }

    private function normalisasiModePpnLaporan(?string $modePpn, $penjualan = null): string
    {
        if (in_array($modePpn, ['tanpa_ppn', 'include', 'exclude'], true)) {
            return $modePpn;
        }

        if (!$penjualan) {
            return 'tanpa_ppn';
        }

        if ((float) ($penjualan->persentase_pajak ?? 0) <= 0) {
            return 'tanpa_ppn';
        }

        return (bool) ($penjualan->pajak_ditambahkan ?? false) ? 'exclude' : 'include';
    }

    private function hitungRingkasanPembelian($pembelian): array
    {
        $totalTransaksi = $pembelian->count();
        $totalSubtotal = $pembelian->sum('subtotal');
        $totalPajak = $pembelian->sum('nilai_pajak');
        $totalBiayaLain = $pembelian->sum(function ($item) {
            return (float) ($item->biaya_lain ?? 0);
        });
        $totalPotonganDiskon = $pembelian->sum(function ($item) {
            return (float) ($item->potongan_diskon ?? 0);
        });
        $totalAkhir = $pembelian->sum('total_akhir');
        $totalSebelumDiskon = $totalSubtotal + $totalPajak + $totalBiayaLain;

        $totalDipesan = 0;
        $totalDiterima = 0;
        $totalDetailBarang = 0;
        $totalDetailDiterima = 0;
        $totalNilaiPajakDitambahkan = 0;
        $totalNilaiPajakTampilSaja = 0;

        foreach ($pembelian as $item) {
            foreach ($item->detailPembelian as $detail) {
                $jumlahDipesan = $detail->jumlah_dipesan ?? $detail->jumlah;
                $jumlahDiterima = $detail->jumlah;

                $totalDetailBarang++;
                $totalDipesan += $jumlahDipesan;
                $totalDiterima += $jumlahDiterima;

                if ($jumlahDiterima > 0) {
                    $totalDetailDiterima++;
                }
            }

            if ((bool) ($item->pajak_ditambahkan ?? true)) {
                $totalNilaiPajakDitambahkan += (float) ($item->nilai_pajak ?? 0);
            } else {
                $totalNilaiPajakTampilSaja += (float) ($item->nilai_pajak ?? 0);
            }
        }

        $totalSisa = max($totalDipesan - $totalDiterima, 0);

        $totalLengkap = $pembelian
            ->where('status_penerimaan', 'lengkap')
            ->count();

        $totalSebagian = $pembelian
            ->where('status_penerimaan', 'sebagian')
            ->count();

        $totalBelumDikirim = $pembelian
            ->where('status_penerimaan', 'belum_dikirim')
            ->count();

        $totalHistoris = $pembelian
            ->filter(function ($item) {
                return (bool) ($item->is_historical ?? false);
            })
            ->count();

        $totalSistemBerjalan = $pembelian
            ->filter(function ($item) {
                return !(bool) ($item->is_historical ?? false);
            })
            ->count();

        $totalMemengaruhiStok = $pembelian
            ->filter(function ($item) {
                return (bool) ($item->affect_stock ?? true);
            })
            ->count();

        $totalTidakMemengaruhiStok = $pembelian
            ->filter(function ($item) {
                return !(bool) ($item->affect_stock ?? true);
            })
            ->count();

        $totalPajakDitambahkan = $pembelian
            ->filter(function ($item) {
                return (bool) ($item->pajak_ditambahkan ?? true);
            })
            ->count();

        $totalPajakTampilSaja = $pembelian
            ->filter(function ($item) {
                return !(bool) ($item->pajak_ditambahkan ?? true);
            })
            ->count();

        $totalDenganPajak = $pembelian
            ->filter(function ($item) {
                return (float) ($item->nilai_pajak ?? 0) > 0;
            })
            ->count();

        $totalTanpaPajak = max($totalTransaksi - $totalDenganPajak, 0);

        $totalDenganPenyesuaian = $pembelian
            ->filter(function ($item) {
                return (float) ($item->biaya_lain ?? 0) > 0 || (float) ($item->potongan_diskon ?? 0) > 0;
            })
            ->count();

        return compact(
            'totalTransaksi',
            'totalSubtotal',
            'totalPajak',
            'totalBiayaLain',
            'totalPotonganDiskon',
            'totalSebelumDiskon',
            'totalAkhir',
            'totalDipesan',
            'totalDiterima',
            'totalSisa',
            'totalLengkap',
            'totalSebagian',
            'totalBelumDikirim',
            'totalHistoris',
            'totalSistemBerjalan',
            'totalMemengaruhiStok',
            'totalTidakMemengaruhiStok',
            'totalDetailBarang',
            'totalDetailDiterima',
            'totalPajakDitambahkan',
            'totalPajakTampilSaja',
            'totalNilaiPajakDitambahkan',
            'totalNilaiPajakTampilSaja',
            'totalDenganPajak',
            'totalTanpaPajak',
            'totalDenganPenyesuaian'
        );
    }

    private function hitungRingkasanPiutang($piutang): array
    {
        $totalData = $piutang->count();
        $totalPiutang = $piutang->sum('total_piutang');
        $totalDibayar = $piutang->sum('total_dibayar');
        $totalSisa = $piutang->sum('sisa_piutang');

        $totalBelumLunas = $piutang
            ->where('status_piutang', 'belum_lunas')
            ->count();

        $totalSebagian = $piutang
            ->where('status_piutang', 'sebagian_dibayar')
            ->count();

        $totalLunas = $piutang
            ->where('status_piutang', 'lunas')
            ->count();

        $totalJatuhTempo = $piutang
            ->where('status_piutang', 'jatuh_tempo')
            ->count();

        $totalLewatJatuhTempo = $piutang
            ->filter(function ($item) {
                return $item->status_piutang !== 'lunas'
                    && $item->tanggal_jatuh_tempo
                    && $item->tanggal_jatuh_tempo->lt(today());
            })
            ->count();

        $totalBelumLewatJatuhTempo = $piutang
            ->filter(function ($item) {
                return $item->status_piutang !== 'lunas'
                    && $item->tanggal_jatuh_tempo
                    && $item->tanggal_jatuh_tempo->gte(today());
            })
            ->count();

        $totalHistoris = $piutang
            ->filter(function ($item) {
                return (bool) ($item->penjualan->is_historical ?? false);
            })
            ->count();

        $totalSistemBerjalan = $piutang
            ->filter(function ($item) {
                return !(bool) ($item->penjualan->is_historical ?? false);
            })
            ->count();

        $persentaseTertagih = $totalPiutang > 0
            ? round(($totalDibayar / $totalPiutang) * 100, 2)
            : 0;

        return compact(
            'totalData',
            'totalPiutang',
            'totalDibayar',
            'totalSisa',
            'totalBelumLunas',
            'totalSebagian',
            'totalLunas',
            'totalJatuhTempo',
            'totalLewatJatuhTempo',
            'totalBelumLewatJatuhTempo',
            'totalHistoris',
            'totalSistemBerjalan',
            'persentaseTertagih'
        );
    }

    private function hitungRingkasanStokBarang($barang, int $batasStokRendah): array
    {
        $totalBarang = $barang->count();
        $totalStok = $barang->sum('stok_saat_ini');

        $totalBarangAktif = $barang
            ->where('status_aktif', true)
            ->count();

        $totalBarangNonaktif = $barang
            ->where('status_aktif', false)
            ->count();

        $totalBarangKosong = $barang
            ->where('stok_saat_ini', '<=', 0)
            ->count();

        $totalBarangStokRendah = $barang
            ->filter(function ($item) use ($batasStokRendah) {
                return $item->stok_saat_ini > 0
                    && $item->stok_saat_ini <= $batasStokRendah;
            })
            ->count();

        $totalBarangTersedia = $barang
            ->filter(function ($item) use ($batasStokRendah) {
                return $item->stok_saat_ini > $batasStokRendah;
            })
            ->count();

        $totalBarangNormal = $barang
            ->filter(function ($item) {
                return ($item->tipe_perhitungan_harga ?? 'normal') === 'normal';
            })
            ->count();

        $totalBarangIsiKemasan = $barang
            ->filter(function ($item) {
                return ($item->tipe_perhitungan_harga ?? 'normal') === 'isi_kemasan';
            })
            ->count();

        $totalBarangNonPpn = $barang
            ->filter(function ($item) {
                return $this->normalisasiJenisPpnBarang($item) === 'non_ppn';
            })
            ->count();

        $totalBarangPpnNormal = $barang
            ->filter(function ($item) {
                return $this->normalisasiJenisPpnBarang($item) === 'ppn_normal';
            })
            ->count();

        $totalBarangPpnDppNilaiLain = $barang
            ->filter(function ($item) {
                return $this->normalisasiJenisPpnBarang($item) === 'ppn_dpp_nilai_lain';
            })
            ->count();

        $totalBarangKenaPpn = $totalBarangPpnNormal + $totalBarangPpnDppNilaiLain;

        $totalNilaiStok = 0;
        $totalEstimasiNilaiJual = 0;
        $totalJumlahSatuanHarga = 0;

        foreach ($barang as $item) {
            $stokSaatIni = (float) ($item->stok_saat_ini ?? 0);
            $hargaBeli = (float) ($item->harga_beli_terakhir ?? 0);
            $hargaJual = (float) ($item->harga_jual_default ?? 0);
            $tipePerhitungan = $item->tipe_perhitungan_harga ?? 'normal';

            $isiPerSatuan = $tipePerhitungan === 'isi_kemasan'
                ? (float) ($item->isi_per_satuan ?? 1)
                : 1;

            $jumlahSatuanHarga = $stokSaatIni * $isiPerSatuan;

            $totalNilaiStok += $stokSaatIni * $hargaBeli;
            $totalEstimasiNilaiJual += $jumlahSatuanHarga * $hargaJual;
            $totalJumlahSatuanHarga += $jumlahSatuanHarga;
        }

        $totalEstimasiLabaKotor = $totalEstimasiNilaiJual - $totalNilaiStok;

        return compact(
            'totalBarang',
            'totalStok',
            'totalBarangAktif',
            'totalBarangNonaktif',
            'totalBarangKosong',
            'totalBarangStokRendah',
            'totalBarangTersedia',
            'totalBarangNormal',
            'totalBarangIsiKemasan',
            'totalBarangKenaPpn',
            'totalBarangNonPpn',
            'totalBarangPpnNormal',
            'totalBarangPpnDppNilaiLain',
            'totalJumlahSatuanHarga',
            'totalNilaiStok',
            'totalEstimasiNilaiJual',
            'totalEstimasiLabaKotor'
        );
    }

    private function normalisasiJenisPpnBarang($item): string
    {
        $jenisPpn = $item->jenis_ppn ?? null;

        if (in_array($jenisPpn, ['non_ppn', 'ppn_normal', 'ppn_dpp_nilai_lain'], true)) {
            return $jenisPpn;
        }

        $kenaPpnLegacy = (bool) ($item->kena_ppn ?? true);

        return $kenaPpnLegacy ? 'ppn_normal' : 'non_ppn';
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

        $totalSelisihPlus = $riwayatStok
            ->sum(function ($item) {
                $selisih = (int) ($item->stok_sesudah ?? 0) - (int) ($item->stok_sebelum ?? 0);
                return $selisih > 0 ? $selisih : 0;
            });

        $totalSelisihMinus = $riwayatStok
            ->sum(function ($item) {
                $selisih = (int) ($item->stok_sesudah ?? 0) - (int) ($item->stok_sebelum ?? 0);
                return $selisih < 0 ? abs($selisih) : 0;
            });

        $totalNettoPerubahan = $totalSelisihPlus - $totalSelisihMinus;

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
            ->count();

        $totalBarangIsiKemasan = $riwayatStok
            ->filter(function ($item) {
                return ($item->barang->tipe_perhitungan_harga ?? 'normal') === 'isi_kemasan';
            })
            ->count();

        $totalBarangNonPpn = $riwayatStok
            ->filter(function ($item) {
                return $this->normalisasiJenisPpnBarang($item->barang) === 'non_ppn';
            })
            ->count();

        $totalBarangPpnNormal = $riwayatStok
            ->filter(function ($item) {
                return $this->normalisasiJenisPpnBarang($item->barang) === 'ppn_normal';
            })
            ->count();

        $totalBarangPpnDppNilaiLain = $riwayatStok
            ->filter(function ($item) {
                return $this->normalisasiJenisPpnBarang($item->barang) === 'ppn_dpp_nilai_lain';
            })
            ->count();

        $totalBarangKenaPpn = $totalBarangPpnNormal + $totalBarangPpnDppNilaiLain;

        return compact(
            'totalData',
            'totalMasuk',
            'totalKeluar',
            'totalPenyesuaian',
            'totalJumlahPenyesuaian',
            'totalOpname',
            'totalNonOpname',
            'totalSelisihPlus',
            'totalSelisihMinus',
            'totalNettoPerubahan',
            'totalTransaksiMasuk',
            'totalTransaksiKeluar',
            'totalBarangUnik',
            'totalBarangNormal',
            'totalBarangIsiKemasan',
            'totalBarangKenaPpn',
            'totalBarangNonPpn',
            'totalBarangPpnNormal',
            'totalBarangPpnDppNilaiLain'
        );
    }

    private function namaFileLaporan(string $prefix, string $tanggalAwal, string $tanggalAkhir, string $extension): string
    {
        $awal = $tanggalAwal ?: 'awal';
        $akhir = $tanggalAkhir ?: 'akhir';

        $namaFile = $prefix . '-' . $awal . '-sd-' . $akhir;
        $namaFile = preg_replace('/[^A-Za-z0-9\-_]+/', '-', $namaFile);
        $namaFile = trim(preg_replace('/-+/', '-', $namaFile), '-');
        $namaFile .= '-' . time();

        return $namaFile . '.' . $extension;
    }
}
