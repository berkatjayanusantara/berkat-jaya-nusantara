<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Pembelian;
use App\Models\Penjualan;
use App\Models\Piutang;
use App\Models\Customer;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();

        $totalBarang = Barang::count();

        $totalStok = Barang::sum('stok_saat_ini');

        $totalCustomer = Customer::count();

        $totalSupplier = Supplier::count();

        $customerTerbaru = Customer::orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $pembelianHariIni = Pembelian::whereDate('tanggal_pembelian', $today)
            ->sum('total_akhir');

        $penjualanHariIni = Penjualan::whereDate('tanggal_penjualan', $today)
            ->sum('total_akhir');

        $totalPiutangBelumLunas = Piutang::where('status_piutang', '!=', 'lunas')
            ->sum('sisa_piutang');

        $invoiceHariIni = Penjualan::whereDate('tanggal_penjualan', $today)
            ->count();

        $stokTerendah = Barang::where('status_aktif', true)
            ->orderBy('stok_saat_ini', 'asc')
            ->limit(5)
            ->get();

        $piutangJatuhTempo = Piutang::with('customer')
            ->where('status_piutang', '!=', 'lunas')
            ->whereDate('tanggal_jatuh_tempo', '<=', now()->addDays(7)->toDateString())
            ->orderBy('tanggal_jatuh_tempo', 'asc')
            ->limit(5)
            ->get();

        $pembelianTerbaru = Pembelian::with('supplier')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $penjualanTerbaru = Penjualan::with('customer')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $grafikPenjualan7Hari = Penjualan::select(
            DB::raw('DATE(tanggal_penjualan) as tanggal'),
            DB::raw('SUM(total_akhir) as total')
        )
            ->whereDate('tanggal_penjualan', '>=', now()->subDays(6)->toDateString())
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->get();

        return view('dashboard', compact(
            'totalBarang',
            'totalStok',
            'totalCustomer',
            'totalSupplier',
            'customerTerbaru',
            'pembelianHariIni',
            'penjualanHariIni',
            'totalPiutangBelumLunas',
            'invoiceHariIni',
            'stokTerendah',
            'piutangJatuhTempo',
            'pembelianTerbaru',
            'penjualanTerbaru',
            'grafikPenjualan7Hari'
        ));
    }
}
