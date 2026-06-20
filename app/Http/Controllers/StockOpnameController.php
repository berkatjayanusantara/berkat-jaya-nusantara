<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\RiwayatStok;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockOpnameController extends Controller
{
    public function index(Request $request)
    {
        $batasStokRendah = (int) ($request->batas_stok_rendah ?? 5);
        $batasStokRendah = $batasStokRendah > 0 ? $batasStokRendah : 5;

        $query = Barang::query()
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

        $barangUntukRingkasan = (clone $query)->get();

        $ringkasan = $this->hitungRingkasanStockOpname($barangUntukRingkasan, $batasStokRendah);

        $barang = $query
            ->orderBy('nama_barang', 'asc')
            ->paginate(10)
            ->withQueryString();

        return view('stock-opname.index', array_merge([
            'barang' => $barang,
            'batasStokRendah' => $batasStokRendah,
        ], $ringkasan));
    }

    public function store(Request $request)
    {
        $request->validate([
            'selected' => ['required', 'array', 'min:1'],
            'selected.*' => ['integer'],
            'stok_fisik' => ['required', 'array'],
            'stok_fisik.*' => ['nullable', 'numeric', 'min:0'],
            'keterangan' => ['nullable', 'array'],
            'keterangan.*' => ['nullable', 'string', 'max:255'],
        ], [
            'selected.required' => 'Pilih minimal 1 barang yang ingin di-stock opname.',
            'selected.min' => 'Pilih minimal 1 barang yang ingin di-stock opname.',
            'stok_fisik.required' => 'Stok fisik wajib diisi.',
            'stok_fisik.*.numeric' => 'Stok fisik harus berupa angka.',
            'stok_fisik.*.min' => 'Stok fisik tidak boleh kurang dari 0.',
        ]);

        $selectedIds = collect($request->selected)
            ->map(fn($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if ($selectedIds->isEmpty()) {
            return back()
                ->withInput()
                ->with('error', 'Pilih minimal 1 barang yang ingin di-stock opname.');
        }

        $stokFisikInput = $request->stok_fisik ?? [];
        $keteranganInput = $request->keterangan ?? [];

        $nomorStockOpname = 'STOCK-OPNAME-' . now()->format('Ymd-His');

        $totalDiproses = 0;
        $totalBerubah = 0;
        $totalTidakBerubah = 0;
        $totalStokBertambah = 0;
        $totalStokBerkurang = 0;

        DB::transaction(function () use (
            $selectedIds,
            $stokFisikInput,
            $keteranganInput,
            $nomorStockOpname,
            &$totalDiproses,
            &$totalBerubah,
            &$totalTidakBerubah,
            &$totalStokBertambah,
            &$totalStokBerkurang
        ) {
            $barangList = Barang::whereIn('id_barang', $selectedIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id_barang');

            foreach ($selectedIds as $idBarang) {
                if (!$barangList->has($idBarang)) {
                    continue;
                }

                if (!array_key_exists($idBarang, $stokFisikInput)) {
                    continue;
                }

                $barang = $barangList->get($idBarang);

                $stokSebelum = (int) ($barang->stok_saat_ini ?? 0);
                $stokSesudah = (int) $stokFisikInput[$idBarang];

                $selisih = $stokSesudah - $stokSebelum;
                $jumlahSelisih = abs($selisih);

                $totalDiproses++;

                if ($selisih === 0) {
                    $totalTidakBerubah++;
                    continue;
                }

                $barang->update([
                    'stok_saat_ini' => $stokSesudah,
                ]);

                RiwayatStok::create([
                    'id_barang' => $barang->id_barang,
                    'jenis_pergerakan' => 'penyesuaian',
                    'jumlah' => $jumlahSelisih,
                    'stok_sebelum' => $stokSebelum,
                    'stok_sesudah' => $stokSesudah,
                    'sumber_transaksi' => $nomorStockOpname,
                    'keterangan' => $this->buatKeteranganStockOpname(
                        $barang,
                        $stokSebelum,
                        $stokSesudah,
                        $selisih,
                        $keteranganInput[$idBarang] ?? null
                    ),
                    'tanggal' => now()->toDateString(),
                    'id_user' => Auth::id(),
                ]);

                $totalBerubah++;

                if ($selisih > 0) {
                    $totalStokBertambah += $selisih;
                } else {
                    $totalStokBerkurang += abs($selisih);
                }
            }
        });

        if ($totalBerubah <= 0) {
            return redirect()
                ->route('stock-opname.index')
                ->with('warning', 'Stock opname berhasil dicek, tetapi tidak ada perubahan stok.');
        }

        return redirect()
            ->route('stock-opname.index')
            ->with(
                'success',
                'Stock opname berhasil disimpan. ' .
                    'Nomor: ' . $nomorStockOpname . '. ' .
                    'Barang diproses: ' . $totalDiproses . '. ' .
                    'Stok berubah: ' . $totalBerubah . '. ' .
                    'Tidak berubah: ' . $totalTidakBerubah . '. ' .
                    'Total bertambah: ' . number_format($totalStokBertambah, 0, ',', '.') . '. ' .
                    'Total berkurang: ' . number_format($totalStokBerkurang, 0, ',', '.') . '.'
            );
    }

    private function hitungRingkasanStockOpname($barang, int $batasStokRendah): array
    {
        $totalBarang = $barang->count();

        $totalStok = $barang->sum(function ($item) {
            return (int) ($item->stok_saat_ini ?? 0);
        });

        $totalBarangAktif = $barang
            ->where('status_aktif', true)
            ->count();

        $totalBarangNonaktif = $barang
            ->where('status_aktif', false)
            ->count();

        $totalBarangKosong = $barang
            ->filter(function ($item) {
                return (int) ($item->stok_saat_ini ?? 0) <= 0;
            })
            ->count();

        $totalBarangStokRendah = $barang
            ->filter(function ($item) use ($batasStokRendah) {
                $stok = (int) ($item->stok_saat_ini ?? 0);

                return $stok > 0 && $stok <= $batasStokRendah;
            })
            ->count();

        $totalBarangTersedia = $barang
            ->filter(function ($item) use ($batasStokRendah) {
                return (int) ($item->stok_saat_ini ?? 0) > $batasStokRendah;
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
            'totalEstimasiNilaiJual'
        );
    }

    private function normalisasiJenisPpnBarang($item): string
    {
        if (!$item) {
            return 'ppn_normal';
        }

        $jenisPpn = $item->jenis_ppn ?? null;

        if (in_array($jenisPpn, ['non_ppn', 'ppn_normal', 'ppn_dpp_nilai_lain'], true)) {
            return $jenisPpn;
        }

        $kenaPpnLegacy = (bool) ($item->kena_ppn ?? true);

        return $kenaPpnLegacy ? 'ppn_normal' : 'non_ppn';
    }

    private function buatKeteranganStockOpname($barang, int $stokSebelum, int $stokSesudah, int $selisih, ?string $catatanManual = null): string
    {
        $arah = $selisih > 0 ? 'bertambah' : 'berkurang';

        $keterangan = 'Stock opname: stok sistem ' .
            number_format($stokSebelum, 0, ',', '.') .
            ' menjadi ' .
            number_format($stokSesudah, 0, ',', '.') .
            ', selisih ' .
            $arah .
            ' ' .
            number_format(abs($selisih), 0, ',', '.') .
            ' ' .
            strtoupper($barang->satuan ?? '');

        if ($catatanManual) {
            $keterangan .= '. Catatan: ' . $catatanManual;
        }

        return $keterangan;
    }
}
