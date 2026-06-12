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
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

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
        $customers = Customer::where('status_aktif', true)
            ->orderBy('nama_customer')
            ->get();

        $barang = Barang::where('status_aktif', true)
            ->orderBy('nama_barang')
            ->get();

        return view('penjualan.create', compact(
            'customers',
            'barang'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nomor_invoice' => [
                'required',
                'string',
                'max:100',
                Rule::unique('penjualan', 'nomor_invoice'),
            ],
            'tanggal_penjualan' => 'required|date',
            'id_customer' => 'required|exists:customers,id_customer',
            'persentase_pajak' => 'nullable|numeric|min:0|max:100',
            'pajak_ditambahkan' => 'nullable|in:0,1',
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
                $barang = Barang::where('id_barang', $idBarang)
                    ->lockForUpdate()
                    ->firstOrFail();

                $jumlah = (int) $request->jumlah[$index];

                if ($jumlah > $barang->stok_saat_ini) {
                    throw ValidationException::withMessages([
                        'stok' => 'Stok barang ' . $barang->nama_barang . ' tidak mencukupi. Stok tersedia: ' . $barang->stok_saat_ini . ' ' . $barang->satuan,
                    ]);
                }

                $hargaJual = (float) $request->harga_jual[$index];
                $subtotalPenjualan += $this->hitungSubtotalDetail($barang, $jumlah, $hargaJual);
            }

            $persentasePajak = (float) ($request->persentase_pajak ?? 0);
            $nilaiPajak = $subtotalPenjualan * ($persentasePajak / 100);

            $pajakDitambahkan = (bool) $request->boolean('pajak_ditambahkan');

            $totalAkhir = $pajakDitambahkan
                ? $subtotalPenjualan + $nilaiPajak
                : $subtotalPenjualan;

            $statusPembayaran = $request->metode_pembayaran === 'tunai'
                ? 'lunas'
                : 'belum_lunas';

            $nomorInvoice = trim($request->nomor_invoice);

            $penjualan = Penjualan::create([
                'nomor_invoice' => $nomorInvoice,
                'tanggal_penjualan' => $request->tanggal_penjualan,
                'id_customer' => $request->id_customer,
                'subtotal' => $subtotalPenjualan,
                'persentase_pajak' => $persentasePajak,
                'nilai_pajak' => $nilaiPajak,
                'pajak_ditambahkan' => $pajakDitambahkan,
                'total_akhir' => $totalAkhir,
                'metode_pembayaran' => $request->metode_pembayaran,
                'status_pembayaran' => $statusPembayaran,
                'tanggal_jatuh_tempo' => $request->tanggal_jatuh_tempo,
                'catatan' => $request->catatan,
                'dibuat_oleh' => Auth::id(),
            ]);

            foreach ($request->id_barang as $index => $idBarang) {
                $barang = Barang::where('id_barang', $idBarang)
                    ->lockForUpdate()
                    ->firstOrFail();

                $jumlah = (int) $request->jumlah[$index];
                $hargaJual = (float) $request->harga_jual[$index];

                if ($jumlah > $barang->stok_saat_ini) {
                    throw ValidationException::withMessages([
                        'stok' => 'Stok barang ' . $barang->nama_barang . ' tidak mencukupi. Stok tersedia: ' . $barang->stok_saat_ini . ' ' . $barang->satuan,
                    ]);
                }

                $tipePerhitunganHarga = $barang->tipe_perhitungan_harga ?? 'normal';
                $satuanTransaksi = $barang->satuan;
                $satuanHitungHarga = $tipePerhitunganHarga === 'isi_kemasan'
                    ? $barang->satuan_hitung_harga
                    : $barang->satuan;

                $isiPerSatuan = $tipePerhitunganHarga === 'isi_kemasan'
                    ? (float) $barang->isi_per_satuan
                    : 1;

                $subtotalDetail = $this->hitungSubtotalDetail($barang, $jumlah, $hargaJual);

                DetailPenjualan::create([
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

    public function edit(Penjualan $penjualan)
    {
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

        return view('penjualan.edit', compact(
            'penjualan',
            'customers',
            'barang'
        ));
    }

    public function update(Request $request, Penjualan $penjualan)
    {
        $request->validate([
            'nomor_invoice' => [
                'required',
                'string',
                'max:100',
                Rule::unique('penjualan', 'nomor_invoice')
                    ->ignore($penjualan->id_penjualan, 'id_penjualan'),
            ],
            'tanggal_penjualan' => 'required|date',
            'id_customer' => 'required|exists:customers,id_customer',
            'persentase_pajak' => 'nullable|numeric|min:0|max:100',
            'pajak_ditambahkan' => 'nullable|in:0,1',
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
            $penjualan->load([
                'detailPenjualan',
                'piutang.pembayaranPiutang',
            ]);

            $totalDibayarLama = $penjualan->piutang
                ? (float) $penjualan->piutang->total_dibayar
                : 0;

            if ($request->metode_pembayaran === 'tunai' && $totalDibayarLama > 0) {
                throw ValidationException::withMessages([
                    'metode_pembayaran' => 'Penjualan kredit yang sudah memiliki pembayaran piutang tidak bisa diubah menjadi tunai. Hapus/atur pembayaran piutang terlebih dahulu jika memang diperlukan.',
                ]);
            }

            $affectStock = $penjualan->affect_stock ?? true;

            if ($affectStock) {
                foreach ($penjualan->detailPenjualan as $detailLama) {
                    $barangLama = Barang::where('id_barang', $detailLama->id_barang)
                        ->lockForUpdate()
                        ->first();

                    if (!$barangLama) {
                        continue;
                    }

                    $stokSebelum = $barangLama->stok_saat_ini;
                    $stokSesudah = $stokSebelum + $detailLama->jumlah;

                    $barangLama->update([
                        'stok_saat_ini' => $stokSesudah,
                    ]);

                    RiwayatStok::create([
                        'id_barang' => $barangLama->id_barang,
                        'tanggal' => $request->tanggal_penjualan,
                        'jenis_pergerakan' => 'penyesuaian',
                        'jumlah' => $detailLama->jumlah,
                        'stok_sebelum' => $stokSebelum,
                        'stok_sesudah' => $stokSesudah,
                        'sumber_transaksi' => $penjualan->nomor_invoice,
                        'keterangan' => 'Pengembalian stok karena edit penjualan',
                        'dibuat_oleh' => Auth::id(),
                        'created_at' => now(),
                    ]);
                }
            }

            $subtotalPenjualan = 0;

            foreach ($request->id_barang as $index => $idBarang) {
                $barang = Barang::where('id_barang', $idBarang)
                    ->lockForUpdate()
                    ->firstOrFail();

                $jumlah = (int) $request->jumlah[$index];
                $hargaJual = (float) $request->harga_jual[$index];

                if ($affectStock && $jumlah > $barang->stok_saat_ini) {
                    throw ValidationException::withMessages([
                        'stok' => 'Stok barang ' . $barang->nama_barang . ' tidak mencukupi. Stok tersedia: ' . $barang->stok_saat_ini . ' ' . $barang->satuan,
                    ]);
                }

                $subtotalPenjualan += $this->hitungSubtotalDetail($barang, $jumlah, $hargaJual);
            }

            $persentasePajak = (float) ($request->persentase_pajak ?? 0);
            $nilaiPajak = $subtotalPenjualan * ($persentasePajak / 100);

            $pajakDitambahkan = (bool) $request->boolean('pajak_ditambahkan');

            $totalAkhir = $pajakDitambahkan
                ? $subtotalPenjualan + $nilaiPajak
                : $subtotalPenjualan;

            $statusPembayaran = $this->hitungStatusPembayaran(
                $request->metode_pembayaran,
                $totalAkhir,
                $totalDibayarLama
            );

            $penjualan->update([
                'nomor_invoice' => trim($request->nomor_invoice),
                'tanggal_penjualan' => $request->tanggal_penjualan,
                'id_customer' => $request->id_customer,
                'subtotal' => $subtotalPenjualan,
                'persentase_pajak' => $persentasePajak,
                'nilai_pajak' => $nilaiPajak,
                'pajak_ditambahkan' => $pajakDitambahkan,
                'total_akhir' => $totalAkhir,
                'metode_pembayaran' => $request->metode_pembayaran,
                'status_pembayaran' => $statusPembayaran,
                'tanggal_jatuh_tempo' => $request->metode_pembayaran === 'kredit'
                    ? $request->tanggal_jatuh_tempo
                    : null,
                'catatan' => $request->catatan,
            ]);

            $penjualan->detailPenjualan()->delete();

            foreach ($request->id_barang as $index => $idBarang) {
                $barang = Barang::where('id_barang', $idBarang)
                    ->lockForUpdate()
                    ->firstOrFail();

                $jumlah = (int) $request->jumlah[$index];
                $hargaJual = (float) $request->harga_jual[$index];

                if ($affectStock && $jumlah > $barang->stok_saat_ini) {
                    throw ValidationException::withMessages([
                        'stok' => 'Stok barang ' . $barang->nama_barang . ' tidak mencukupi. Stok tersedia: ' . $barang->stok_saat_ini . ' ' . $barang->satuan,
                    ]);
                }

                $tipePerhitunganHarga = $barang->tipe_perhitungan_harga ?? 'normal';
                $satuanTransaksi = $barang->satuan;
                $satuanHitungHarga = $tipePerhitunganHarga === 'isi_kemasan'
                    ? $barang->satuan_hitung_harga
                    : $barang->satuan;

                $isiPerSatuan = $tipePerhitunganHarga === 'isi_kemasan'
                    ? (float) $barang->isi_per_satuan
                    : 1;

                $subtotalDetail = $this->hitungSubtotalDetail($barang, $jumlah, $hargaJual);

                DetailPenjualan::create([
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

                if ($affectStock) {
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
                        'keterangan' => 'Stok keluar dari edit penjualan',
                        'dibuat_oleh' => Auth::id(),
                        'created_at' => now(),
                    ]);
                }
            }

            $this->sinkronkanPiutangSetelahEdit($penjualan, $request, $totalAkhir, $totalDibayarLama);
        });

        return redirect()
            ->route('penjualan.show', $penjualan->id_penjualan)
            ->with('success', 'Transaksi penjualan berhasil diperbarui.');
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

    public function exportExcel(Penjualan $penjualan)
    {
        $penjualan->load([
            'customer',
            'user',
            'detailPenjualan.barang',
            'piutang',
        ]);

        $fileName = 'Invoice-' . $penjualan->nomor_invoice . '.xls';

        return response()
            ->view('penjualan.export-excel', compact('penjualan'))
            ->header('Content-Type', 'application/vnd.ms-excel; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"')
            ->header('Cache-Control', 'max-age=0');
    }

    private function hitungSubtotalDetail(Barang $barang, int $jumlah, float $hargaJual): float
    {
        $tipePerhitunganHarga = $barang->tipe_perhitungan_harga ?? 'normal';

        if ($tipePerhitunganHarga === 'isi_kemasan') {
            $isiPerSatuan = (float) ($barang->isi_per_satuan ?? 1);

            return $jumlah * $isiPerSatuan * $hargaJual;
        }

        return $jumlah * $hargaJual;
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

    private function sinkronkanPiutangSetelahEdit(Penjualan $penjualan, Request $request, float $totalAkhir, float $totalDibayarLama): void
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
                'nomor_invoice' => $penjualan->nomor_invoice,
                'id_customer' => $penjualan->id_customer,
                'total_piutang' => $totalAkhir,
                'total_dibayar' => $totalDibayarLama,
                'sisa_piutang' => $sisaPiutang,
                'tanggal_jatuh_tempo' => $request->tanggal_jatuh_tempo,
                'status_piutang' => $statusPiutang,
                'catatan' => 'Piutang diperbarui dari edit transaksi penjualan',
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
            'catatan' => 'Piutang otomatis dari edit transaksi penjualan kredit',
        ]);
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

    private function generateNomorInvoice(bool $lock = false)
    {
        $tanggal = now()->format('Ymd');
        $prefix = 'INV-' . $tanggal . '-';

        $query = Penjualan::where('nomor_invoice', 'like', $prefix . '%')
            ->orderBy('nomor_invoice', 'desc');

        if ($lock) {
            $query->lockForUpdate();
        }

        $lastPenjualan = $query->first();

        if (!$lastPenjualan) {
            return $prefix . '0001';
        }

        $lastNumber = (int) substr($lastPenjualan->nomor_invoice, -4);
        $newNumber = $lastNumber + 1;

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
