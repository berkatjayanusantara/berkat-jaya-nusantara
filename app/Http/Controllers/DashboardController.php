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
        $tanggalTujuhHariKeDepan = now()->addDays(7)->toDateString();
        $batasStokRendah = 5;

        /*
        |--------------------------------------------------------------------------
        | Master Barang
        |--------------------------------------------------------------------------
        | Dashboard mengikuti update stok barang terbaru:
        | - status aktif/nonaktif
        | - tipe harga normal/isi kemasan
        | - status PPN barang
        | - estimasi nilai jual barang isi kemasan memakai rumus:
        |   stok x isi_per_satuan x harga_jual_default
        */
        $totalBarang = Barang::count();
        $totalBarangAktif = Barang::where('status_aktif', true)->count();
        $totalBarangNonaktif = Barang::where('status_aktif', false)->count();

        $totalBarangNormal = Barang::where(function ($query) {
            $query->where('tipe_perhitungan_harga', 'normal')
                ->orWhereNull('tipe_perhitungan_harga');
        })->count();

        $totalBarangIsiKemasan = Barang::where('tipe_perhitungan_harga', 'isi_kemasan')->count();

        $totalBarangKenaPpn = Barang::where(function ($query) {
            $query->where('kena_ppn', true)
                ->orWhereNull('kena_ppn');
        })->count();

        $totalBarangNonPpn = Barang::where('kena_ppn', false)->count();
        $totalStok = Barang::sum('stok_saat_ini');

        $totalBarangKosong = Barang::where('status_aktif', true)
            ->where('stok_saat_ini', '<=', 0)
            ->count();

        $totalBarangStokRendah = Barang::where('status_aktif', true)
            ->where('stok_saat_ini', '>', 0)
            ->where('stok_saat_ini', '<=', $batasStokRendah)
            ->count();

        $totalBarangStokAman = Barang::where('status_aktif', true)
            ->where('stok_saat_ini', '>', $batasStokRendah)
            ->count();

        $barangUntukNilai = Barang::select([
            'stok_saat_ini',
            'harga_beli_terakhir',
            'harga_jual_default',
            'tipe_perhitungan_harga',
            'isi_per_satuan',
        ])->get();

        $totalNilaiStok = $barangUntukNilai->sum(function ($item) {
            return (float) ($item->stok_saat_ini ?? 0) * (float) ($item->harga_beli_terakhir ?? 0);
        });

        $totalEstimasiNilaiJual = $barangUntukNilai->sum(function ($item) {
            $stok = (float) ($item->stok_saat_ini ?? 0);
            $hargaJual = (float) ($item->harga_jual_default ?? 0);
            $isiPerSatuan = ($item->tipe_perhitungan_harga ?? 'normal') === 'isi_kemasan'
                ? (float) ($item->isi_per_satuan ?? 1)
                : 1;

            return $stok * $isiPerSatuan * $hargaJual;
        });

        $totalPotensiMargin = $totalEstimasiNilaiJual - $totalNilaiStok;

        $stokTerendah = Barang::where('status_aktif', true)
            ->orderBy('stok_saat_ini', 'asc')
            ->orderBy('nama_barang')
            ->limit(5)
            ->get();

        $barangIsiKemasan = Barang::where('status_aktif', true)
            ->where('tipe_perhitungan_harga', 'isi_kemasan')
            ->orderBy('nama_barang')
            ->limit(5)
            ->get();

        $barangNonPpn = Barang::where('status_aktif', true)
            ->where('kena_ppn', false)
            ->orderBy('nama_barang')
            ->limit(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Customer & Supplier
        |--------------------------------------------------------------------------
        */
        $totalCustomer = Customer::count();
        $totalCustomerAktif = Customer::where('status_aktif', true)->count();
        $totalCustomerNonaktif = Customer::where('status_aktif', false)->count();

        $totalSupplier = Supplier::count();
        $totalSupplierAktif = Supplier::where('status_aktif', true)->count();
        $totalSupplierNonaktif = Supplier::where('status_aktif', false)->count();

        $customerTerbaru = Customer::orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Pembelian
        |--------------------------------------------------------------------------
        | Mengikuti update pembelian:
        | - invoice sistem & historis
        | - nomor dokumen asli untuk historis
        | - delivery order dan surat jalan
        | - status penerimaan lengkap/sebagian/belum dikirim
        | - affect_stock untuk membedakan yang mengubah stok dan tidak
        */
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

        $pembelianTerbaru = Pembelian::with(['supplier', 'detailPembelian'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $pembelianBelumSelesai = Pembelian::with(['supplier'])
            ->whereIn('status_penerimaan', ['sebagian', 'belum_dikirim'])
            ->orderBy('tanggal_pembelian', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Penjualan
        |--------------------------------------------------------------------------
        */
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

        $ppnPenjualanHariIni = Penjualan::whereDate('tanggal_penjualan', $today)
            ->sum('nilai_pajak');

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

        $penjualanTerbaru = Penjualan::with(['customer', 'piutang'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Piutang
        |--------------------------------------------------------------------------
        */
        $totalPiutang = Piutang::sum('total_piutang');
        $totalPiutangDibayar = Piutang::sum('total_dibayar');
        $totalPiutangBelumLunas = Piutang::where('status_piutang', '!=', 'lunas')
            ->sum('sisa_piutang');

        $jumlahPiutangBelumLunas = Piutang::where('status_piutang', '!=', 'lunas')->count();

        $jumlahPiutangLewatTempo = Piutang::where('status_piutang', '!=', 'lunas')
            ->whereNotNull('tanggal_jatuh_tempo')
            ->whereDate('tanggal_jatuh_tempo', '<', $today)
            ->count();

        $sisaPiutangLewatTempo = Piutang::where('status_piutang', '!=', 'lunas')
            ->whereNotNull('tanggal_jatuh_tempo')
            ->whereDate('tanggal_jatuh_tempo', '<', $today)
            ->sum('sisa_piutang');

        $persentasePiutangTertagih = $totalPiutang > 0
            ? round(($totalPiutangDibayar / $totalPiutang) * 100, 2)
            : 0;

        $piutangJatuhTempo = Piutang::with(['customer', 'penjualan'])
            ->where('status_piutang', '!=', 'lunas')
            ->whereNotNull('tanggal_jatuh_tempo')
            ->whereDate('tanggal_jatuh_tempo', '<=', $tanggalTujuhHariKeDepan)
            ->orderBy('tanggal_jatuh_tempo', 'asc')
            ->limit(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Riwayat Stok & Stock Opname
        |--------------------------------------------------------------------------
        */
        $riwayatHariIni = RiwayatStok::whereDate('tanggal', $today)->get();

        $stokMasukHariIni = $riwayatHariIni
            ->where('jenis_pergerakan', 'masuk')
            ->sum('jumlah');

        $stokKeluarHariIni = $riwayatHariIni
            ->where('jenis_pergerakan', 'keluar')
            ->sum('jumlah');

        $penyesuaianStokHariIni = $riwayatHariIni
            ->where('jenis_pergerakan', 'penyesuaian')
            ->count();

        $stockOpnameHariIni = $riwayatHariIni
            ->filter(function ($item) {
                return str_starts_with((string) $item->sumber_transaksi, 'STOCK-OPNAME');
            });

        $totalBarisStockOpnameHariIni = $stockOpnameHariIni->count();
        $totalNomorStockOpnameHariIni = $stockOpnameHariIni->pluck('sumber_transaksi')->unique()->count();

        $totalSelisihPlusHariIni = $riwayatHariIni
            ->filter(function ($item) {
                return $item->jenis_pergerakan === 'penyesuaian'
                    && ((int) $item->stok_sesudah - (int) $item->stok_sebelum) > 0;
            })
            ->sum(function ($item) {
                return (int) $item->stok_sesudah - (int) $item->stok_sebelum;
            });

        $totalSelisihMinusHariIni = $riwayatHariIni
            ->filter(function ($item) {
                return $item->jenis_pergerakan === 'penyesuaian'
                    && ((int) $item->stok_sesudah - (int) $item->stok_sebelum) < 0;
            })
            ->sum(function ($item) {
                return abs((int) $item->stok_sesudah - (int) $item->stok_sebelum);
            });

        $nettoPerubahanStokHariIni = $stokMasukHariIni
            - $stokKeluarHariIni
            + $totalSelisihPlusHariIni
            - $totalSelisihMinusHariIni;

        $riwayatStokTerbaru = RiwayatStok::with(['barang', 'user'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $stockOpnameTerbaru = RiwayatStok::with(['barang', 'user'])
            ->where('sumber_transaksi', 'like', 'STOCK-OPNAME%')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Grafik Penjualan 7 Hari Terakhir
        |--------------------------------------------------------------------------
        */
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
            'today',
            'batasStokRendah',
            'totalBarang',
            'totalBarangAktif',
            'totalBarangNonaktif',
            'totalBarangNormal',
            'totalBarangIsiKemasan',
            'totalBarangKenaPpn',
            'totalBarangNonPpn',
            'totalStok',
            'totalBarangKosong',
            'totalBarangStokRendah',
            'totalBarangStokAman',
            'totalNilaiStok',
            'totalEstimasiNilaiJual',
            'totalPotensiMargin',
            'stokTerendah',
            'barangIsiKemasan',
            'barangNonPpn',
            'totalCustomer',
            'totalCustomerAktif',
            'totalCustomerNonaktif',
            'totalSupplier',
            'totalSupplierAktif',
            'totalSupplierNonaktif',
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
            'pembelianTerbaru',
            'pembelianBelumSelesai',
            'penjualanHariIni',
            'jumlahPenjualanHariIni',
            'penjualanTunaiHariIni',
            'penjualanKreditHariIni',
            'ppnPenjualanHariIni',
            'barangTerjualHariIni',
            'detailPenjualanNormal',
            'detailPenjualanIsiKemasan',
            'invoiceHariIni',
            'invoiceSistemBerjalan',
            'invoiceHistoris',
            'invoiceHistorisHariIni',
            'penjualanTerbaru',
            'totalPiutang',
            'totalPiutangDibayar',
            'totalPiutangBelumLunas',
            'jumlahPiutangBelumLunas',
            'jumlahPiutangLewatTempo',
            'sisaPiutangLewatTempo',
            'persentasePiutangTertagih',
            'piutangJatuhTempo',
            'stokMasukHariIni',
            'stokKeluarHariIni',
            'penyesuaianStokHariIni',
            'totalBarisStockOpnameHariIni',
            'totalNomorStockOpnameHariIni',
            'totalSelisihPlusHariIni',
            'totalSelisihMinusHariIni',
            'nettoPerubahanStokHariIni',
            'riwayatStokTerbaru',
            'stockOpnameTerbaru',
            'grafikPenjualan7Hari'
        ));
    }
}
