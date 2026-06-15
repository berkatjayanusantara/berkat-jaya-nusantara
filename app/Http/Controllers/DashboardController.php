<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Customer;
use App\Models\DetailPembelian;
use App\Models\DetailPenjualan;
use App\Models\Pembelian;
use App\Models\Penjualan;
use App\Models\Piutang;
use App\Models\RiwayatStok;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();
        $batasStokRendah = 5;

        $totalBarang = Barang::count();
        $totalBarangAktif = Barang::where('status_aktif', true)->count();
        $totalBarangNonaktif = Barang::where('status_aktif', false)->count();
        $totalBarangNormal = Barang::where(function ($query) {
            $query->where('tipe_perhitungan_harga', 'normal')
                ->orWhereNull('tipe_perhitungan_harga');
        })->count();
        $totalBarangIsiKemasan = Barang::where('tipe_perhitungan_harga', 'isi_kemasan')->count();
        $totalStok = Barang::sum('stok_saat_ini');
        $totalBarangKosong = Barang::where('status_aktif', true)
            ->where('stok_saat_ini', '<=', 0)
            ->count();
        $totalBarangStokRendah = Barang::where('status_aktif', true)
            ->where('stok_saat_ini', '>', 0)
            ->where('stok_saat_ini', '<=', $batasStokRendah)
            ->count();

        $totalNilaiStok = Barang::selectRaw('SUM(COALESCE(stok_saat_ini, 0) * COALESCE(harga_beli_terakhir, 0)) as total')
            ->value('total') ?? 0;
        $totalEstimasiNilaiJual = Barang::selectRaw('SUM(COALESCE(stok_saat_ini, 0) * COALESCE(harga_jual_default, 0)) as total')
            ->value('total') ?? 0;

        $totalCustomer = Customer::count();
        $totalCustomerAktif = Customer::where('status_aktif', true)->count();
        $totalSupplier = Supplier::count();
        $totalSupplierAktif = Supplier::where('status_aktif', true)->count();

        $customerTerbaru = Customer::orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $pembelianHariIni = Pembelian::whereDate('tanggal_pembelian', $today)
            ->sum('total_akhir');
        $jumlahPembelianHariIni = Pembelian::whereDate('tanggal_pembelian', $today)
            ->count();
        $pembelianSistemBerjalan = Pembelian::where(function ($query) {
            $query->where('is_historical', false)
                ->orWhereNull('is_historical');
        })->count();
        $pembelianHistoris = Pembelian::where('is_historical', true)->count();
        $pembelianMempengaruhiStok = Pembelian::where(function ($query) {
            $query->where('affect_stock', true)
                ->orWhereNull('affect_stock');
        })->count();
        $pembelianTidakMempengaruhiStok = Pembelian::where('affect_stock', false)->count();
        $pembelianLengkap = Pembelian::where('status_penerimaan', 'lengkap')->count();
        $pembelianSebagian = Pembelian::where('status_penerimaan', 'sebagian')->count();
        $pembelianBelumDikirim = Pembelian::where('status_penerimaan', 'belum_dikirim')->count();
        $barangDipesanHariIni = DetailPembelian::whereHas('pembelian', function ($query) use ($today) {
            $query->whereDate('tanggal_pembelian', $today);
        })->sum('jumlah_dipesan');
        $barangDiterimaHariIni = DetailPembelian::whereHas('pembelian', function ($query) use ($today) {
            $query->whereDate('tanggal_pembelian', $today);
        })->sum('jumlah');

        $penjualanHariIni = Penjualan::whereDate('tanggal_penjualan', $today)
            ->sum('total_akhir');
        $jumlahPenjualanHariIni = Penjualan::whereDate('tanggal_penjualan', $today)
            ->count();
        $penjualanTunaiHariIni = Penjualan::whereDate('tanggal_penjualan', $today)
            ->where('metode_pembayaran', 'tunai')
            ->sum('total_akhir');
        $penjualanKreditHariIni = Penjualan::whereDate('tanggal_penjualan', $today)
            ->where('metode_pembayaran', 'kredit')
            ->sum('total_akhir');
        $barangTerjualHariIni = DetailPenjualan::whereHas('penjualan', function ($query) use ($today) {
            $query->whereDate('tanggal_penjualan', $today)
                ->where(function ($subQuery) {
                    $subQuery->where('affect_stock', true)
                        ->orWhereNull('affect_stock');
                });
        })->sum('jumlah');
        $detailPenjualanNormal = DetailPenjualan::where(function ($query) {
            $query->where('tipe_perhitungan_harga', 'normal')
                ->orWhereNull('tipe_perhitungan_harga');
        })->count();
        $detailPenjualanIsiKemasan = DetailPenjualan::where('tipe_perhitungan_harga', 'isi_kemasan')->count();

        $invoiceHariIni = Penjualan::whereDate('tanggal_penjualan', $today)->count();
        $invoiceSistemBerjalan = Penjualan::where(function ($query) {
            $query->where('is_historical', false)
                ->orWhereNull('is_historical');
        })->count();
        $invoiceHistoris = Penjualan::where('is_historical', true)->count();
        $invoiceHistorisHariIni = Penjualan::whereDate('tanggal_penjualan', $today)
            ->where('is_historical', true)
            ->count();

        $totalPiutangBelumLunas = Piutang::where('status_piutang', '!=', 'lunas')
            ->sum('sisa_piutang');
        $totalPiutang = Piutang::sum('total_piutang');
        $totalPiutangDibayar = Piutang::sum('total_dibayar');
        $jumlahPiutangBelumLunas = Piutang::where('status_piutang', '!=', 'lunas')->count();
        $jumlahPiutangLewatTempo = Piutang::where('status_piutang', '!=', 'lunas')
            ->whereDate('tanggal_jatuh_tempo', '<', $today)
            ->count();
        $sisaPiutangLewatTempo = Piutang::where('status_piutang', '!=', 'lunas')
            ->whereDate('tanggal_jatuh_tempo', '<', $today)
            ->sum('sisa_piutang');

        $stokMasukHariIni = RiwayatStok::whereDate('tanggal', $today)
            ->where('jenis_pergerakan', 'masuk')
            ->sum('jumlah');
        $stokKeluarHariIni = RiwayatStok::whereDate('tanggal', $today)
            ->where('jenis_pergerakan', 'keluar')
            ->sum('jumlah');
        $penyesuaianStokHariIni = RiwayatStok::whereDate('tanggal', $today)
            ->where('jenis_pergerakan', 'penyesuaian')
            ->count();
        $stockOpnameHariIni = RiwayatStok::whereDate('tanggal', $today)
            ->where('sumber_transaksi', 'like', 'STOCK-OPNAME%')
            ->count();

        $stokTerendah = Barang::where('status_aktif', true)
            ->orderBy('stok_saat_ini', 'asc')
            ->limit(5)
            ->get();

        $barangIsiKemasan = Barang::where('status_aktif', true)
            ->where('tipe_perhitungan_harga', 'isi_kemasan')
            ->orderBy('nama_barang')
            ->limit(5)
            ->get();

        $piutangJatuhTempo = Piutang::with(['customer', 'penjualan'])
            ->where('status_piutang', '!=', 'lunas')
            ->whereDate('tanggal_jatuh_tempo', '<=', now()->addDays(7)->toDateString())
            ->orderBy('tanggal_jatuh_tempo', 'asc')
            ->limit(5)
            ->get();

        $pembelianTerbaru = Pembelian::with(['supplier', 'detailPembelian'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $penjualanTerbaru = Penjualan::with(['customer', 'piutang'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $riwayatStokTerbaru = RiwayatStok::with(['barang', 'user'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $grafikPenjualanRaw = Penjualan::select(
            DB::raw('DATE(tanggal_penjualan) as tanggal'),
            DB::raw('SUM(total_akhir) as total')
        )
            ->whereDate('tanggal_penjualan', '>=', now()->subDays(6)->toDateString())
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->get()
            ->keyBy('tanggal');

        $grafikPenjualan7Hari = collect(range(0, 6))->map(function ($index) use ($grafikPenjualanRaw) {
            $tanggal = now()->subDays(6)->addDays($index)->toDateString();

            return (object) [
                'tanggal' => $tanggal,
                'total' => (float) ($grafikPenjualanRaw[$tanggal]->total ?? 0),
            ];
        });

        return view('dashboard', compact(
            'batasStokRendah',
            'totalBarang',
            'totalBarangAktif',
            'totalBarangNonaktif',
            'totalBarangNormal',
            'totalBarangIsiKemasan',
            'totalStok',
            'totalBarangKosong',
            'totalBarangStokRendah',
            'totalNilaiStok',
            'totalEstimasiNilaiJual',
            'totalCustomer',
            'totalCustomerAktif',
            'totalSupplier',
            'totalSupplierAktif',
            'customerTerbaru',
            'pembelianHariIni',
            'jumlahPembelianHariIni',
            'pembelianSistemBerjalan',
            'pembelianHistoris',
            'pembelianMempengaruhiStok',
            'pembelianTidakMempengaruhiStok',
            'pembelianLengkap',
            'pembelianSebagian',
            'pembelianBelumDikirim',
            'barangDipesanHariIni',
            'barangDiterimaHariIni',
            'penjualanHariIni',
            'jumlahPenjualanHariIni',
            'penjualanTunaiHariIni',
            'penjualanKreditHariIni',
            'barangTerjualHariIni',
            'detailPenjualanNormal',
            'detailPenjualanIsiKemasan',
            'invoiceHariIni',
            'invoiceSistemBerjalan',
            'invoiceHistoris',
            'invoiceHistorisHariIni',
            'totalPiutangBelumLunas',
            'totalPiutang',
            'totalPiutangDibayar',
            'jumlahPiutangBelumLunas',
            'jumlahPiutangLewatTempo',
            'sisaPiutangLewatTempo',
            'stokMasukHariIni',
            'stokKeluarHariIni',
            'penyesuaianStokHariIni',
            'stockOpnameHariIni',
            'stokTerendah',
            'barangIsiKemasan',
            'piutangJatuhTempo',
            'pembelianTerbaru',
            'penjualanTerbaru',
            'riwayatStokTerbaru',
            'grafikPenjualan7Hari'
        ));
    }
}
