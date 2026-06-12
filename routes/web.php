<?php

use App\Http\Controllers\BarangController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\PembelianController;
use App\Http\Controllers\PenjualanController;
use App\Http\Controllers\PiutangController;
use App\Http\Controllers\RiwayatStokController;
use App\Http\Controllers\InvoiceHistorisController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\StockOpnameController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/barang', [BarangController::class, 'index'])->name('barang.index');
    Route::get('/barang/create', [BarangController::class, 'create'])->name('barang.create');
    Route::post('/barang', [BarangController::class, 'store'])->name('barang.store');
    Route::get('/barang/{barang}/edit', [BarangController::class, 'edit'])->name('barang.edit');
    Route::put('/barang/{barang}', [BarangController::class, 'update'])->name('barang.update');
    Route::patch('/barang/{barang}/nonaktifkan', [BarangController::class, 'nonaktifkan'])->name('barang.nonaktifkan');

    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('/customers/create', [CustomerController::class, 'create'])->name('customers.create');
    Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
    Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
    Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
    Route::patch('/customers/{customer}/nonaktifkan', [CustomerController::class, 'nonaktifkan'])->name('customers.nonaktifkan');
    Route::post('/customers/quick-store', [CustomerController::class, 'quickStore'])->name('customers.quickStore');

    Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
    Route::get('/suppliers/create', [SupplierController::class, 'create'])->name('suppliers.create');
    Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
    Route::post('/suppliers/quick-store', [SupplierController::class, 'quickStore'])->name('suppliers.quickStore');
    Route::get('/suppliers/{supplier}/edit', [SupplierController::class, 'edit'])->name('suppliers.edit');
    Route::put('/suppliers/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');
    Route::patch('/suppliers/{supplier}/nonaktifkan', [SupplierController::class, 'nonaktifkan'])->name('suppliers.nonaktifkan');

    Route::get('/pembelian', [PembelianController::class, 'index'])->name('pembelian.index');
    Route::get('/pembelian/create', [PembelianController::class, 'create'])->name('pembelian.create');
    Route::post('/pembelian', [PembelianController::class, 'store'])->name('pembelian.store');
    Route::get('/pembelian/{pembelian}/export-excel', [PembelianController::class, 'exportExcel'])->name('pembelian.exportExcel');
    Route::get('/pembelian/{pembelian}', [PembelianController::class, 'show'])->name('pembelian.show');

    Route::get('/penjualan', [PenjualanController::class, 'index'])->name('penjualan.index');
    Route::get('/penjualan/create', [PenjualanController::class, 'create'])->name('penjualan.create');
    Route::post('/penjualan', [PenjualanController::class, 'store'])->name('penjualan.store');
    Route::get('/penjualan/{penjualan}/edit', [PenjualanController::class, 'edit'])->name('penjualan.edit');
    Route::put('/penjualan/{penjualan}', [PenjualanController::class, 'update'])->name('penjualan.update');
    Route::get('/penjualan/{penjualan}/export-excel', [PenjualanController::class, 'exportExcel'])->name('penjualan.exportExcel');
    Route::get('/penjualan/{penjualan}', [PenjualanController::class, 'show'])->name('penjualan.show');

    Route::get('/piutang', [PiutangController::class, 'index'])->name('piutang.index');
    Route::get('/piutang/{piutang}', [PiutangController::class, 'show'])->name('piutang.show');
    Route::get('/piutang/{piutang}/bayar', [PiutangController::class, 'bayar'])->name('piutang.bayar');
    Route::post('/piutang/{piutang}/bayar', [PiutangController::class, 'simpanPembayaran'])->name('piutang.simpanPembayaran');

    Route::get('/riwayat-stok', [RiwayatStokController::class, 'index'])->name('riwayat-stok.index');

    Route::get('/stock-opname', [StockOpnameController::class, 'create'])->name('stock-opname.create');
    Route::post('/stock-opname', [StockOpnameController::class, 'store'])->name('stock-opname.store');

    Route::get('/invoice-historis', [InvoiceHistorisController::class, 'index'])->name('invoice-historis.index');
    Route::get('/invoice-historis/pembelian/create', [InvoiceHistorisController::class, 'createPembelian'])->name('invoice-historis.pembelian.create');
    Route::post('/invoice-historis/pembelian', [InvoiceHistorisController::class, 'storePembelian'])->name('invoice-historis.pembelian.store');
    Route::get('/invoice-historis/penjualan/create', [InvoiceHistorisController::class, 'createPenjualan'])->name('invoice-historis.penjualan.create');
    Route::post('/invoice-historis/penjualan', [InvoiceHistorisController::class, 'storePenjualan'])->name('invoice-historis.penjualan.store');

    Route::prefix('laporan')->name('laporan.')->group(function () {
        Route::get('/penjualan', [LaporanController::class, 'penjualan'])->name('penjualan');
        Route::get('/penjualan/export-excel', [LaporanController::class, 'penjualanExportExcel'])->name('penjualan.exportExcel');
        Route::get('/penjualan/export-pdf', [LaporanController::class, 'penjualanExportPdf'])->name('penjualan.exportPdf');

        Route::get('/pembelian', [LaporanController::class, 'pembelian'])->name('pembelian');
        Route::get('/pembelian/export-excel', [LaporanController::class, 'pembelianExportExcel'])->name('pembelian.exportExcel');
        Route::get('/pembelian/export-pdf', [LaporanController::class, 'pembelianExportPdf'])->name('pembelian.exportPdf');

        Route::get('/piutang', [LaporanController::class, 'piutang'])->name('piutang');
        Route::get('/piutang/export-excel', [LaporanController::class, 'piutangExportExcel'])->name('piutang.exportExcel');
        Route::get('/piutang/export-pdf', [LaporanController::class, 'piutangExportPdf'])->name('piutang.exportPdf');

        Route::get('/stok-barang', [LaporanController::class, 'stokBarang'])->name('stokBarang');
        Route::get('/stok-barang/export-excel', [LaporanController::class, 'stokBarangExportExcel'])->name('stokBarang.exportExcel');
        Route::get('/stok-barang/export-pdf', [LaporanController::class, 'stokBarangExportPdf'])->name('stokBarang.exportPdf');

        Route::get('/riwayat-stok', [LaporanController::class, 'riwayatStok'])->name('riwayatStok');
        Route::get('/riwayat-stok/export-excel', [LaporanController::class, 'riwayatStokExportExcel'])->name('riwayatStok.exportExcel');
        Route::get('/riwayat-stok/export-pdf', [LaporanController::class, 'riwayatStokExportPdf'])->name('riwayatStok.exportPdf');
    });
});

require __DIR__ . '/auth.php';
