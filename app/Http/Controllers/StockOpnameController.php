<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\RiwayatStok;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockOpnameController extends Controller
{
    public function create(Request $request)
    {
        $search = $request->search;
        $filterStok = $request->filter_stok;
        $filterTipeHarga = $request->filter_tipe_harga;
        $filterPpn = $request->filter_ppn;
        $batasStokRendah = 5;

        $query = Barang::query()
            ->where('status_aktif', true)
            ->when($search, function ($query, $search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('kode_barang', 'like', "%{$search}%")
                        ->orWhere('nama_barang', 'like', "%{$search}%")
                        ->orWhere('satuan', 'like', "%{$search}%")
                        ->orWhere('satuan_hitung_harga', 'like', "%{$search}%")
                        ->orWhere('keterangan', 'like', "%{$search}%");
                });
            })
            ->when($filterStok === 'kosong', function ($query) {
                $query->where('stok_saat_ini', '<=', 0);
            })
            ->when($filterStok === 'rendah', function ($query) use ($batasStokRendah) {
                $query->where('stok_saat_ini', '>', 0)
                    ->where('stok_saat_ini', '<=', $batasStokRendah);
            })
            ->when($filterStok === 'tersedia', function ($query) use ($batasStokRendah) {
                $query->where('stok_saat_ini', '>', $batasStokRendah);
            })
            ->when($filterTipeHarga === 'normal', function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->where('tipe_perhitungan_harga', 'normal')
                        ->orWhereNull('tipe_perhitungan_harga');
                });
            })
            ->when($filterTipeHarga === 'isi_kemasan', function ($query) {
                $query->where('tipe_perhitungan_harga', 'isi_kemasan');
            })
            ->when($filterPpn === 'kena_ppn', function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->where('kena_ppn', true)
                        ->orWhereNull('kena_ppn');
                });
            })
            ->when($filterPpn === 'non_ppn', function ($query) {
                $query->where('kena_ppn', false);
            });

        $barangUntukRingkasan = (clone $query)->get();
        $ringkasan = $this->hitungRingkasanBarangOpname($barangUntukRingkasan, $batasStokRendah);

        $barang = $query
            ->orderBy('nama_barang')
            ->paginate(10)
            ->withQueryString();

        $semuaBarangAktif = Barang::where('status_aktif', true)
            ->orderBy('nama_barang')
            ->get();

        return view('stock-opname.create', array_merge([
            'barang' => $barang,
            'semuaBarangAktif' => $semuaBarangAktif,
            'search' => $search,
            'filterStok' => $filterStok,
            'filterTipeHarga' => $filterTipeHarga,
            'filterPpn' => $filterPpn,
            'batasStokRendah' => $batasStokRendah,
        ], $ringkasan));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'mode_opname' => ['required', 'in:single,batch'],
            'tanggal' => ['required', 'date'],
            'id_barang' => ['nullable', 'required_if:mode_opname,single', 'exists:barang,id_barang'],
            'stok_fisik' => ['nullable', 'required_if:mode_opname,single', 'integer', 'min:0'],
            'keterangan' => ['nullable', 'string', 'max:500'],
            'items' => ['nullable', 'required_if:mode_opname,batch', 'array'],
            'items.*.id_barang' => ['required_with:items', 'exists:barang,id_barang'],
            'items.*.diproses' => ['nullable', 'in:0,1'],
            'items.*.stok_fisik' => ['nullable', 'integer', 'min:0'],
        ], [
            'mode_opname.required' => 'Mode stock opname wajib dipilih.',
            'mode_opname.in' => 'Mode stock opname tidak valid.',
            'tanggal.required' => 'Tanggal opname wajib diisi.',
            'tanggal.date' => 'Tanggal opname tidak valid.',
            'id_barang.required_if' => 'Barang wajib dipilih.',
            'id_barang.exists' => 'Barang tidak ditemukan.',
            'stok_fisik.required_if' => 'Stok fisik wajib diisi.',
            'stok_fisik.integer' => 'Stok fisik harus berupa angka bulat.',
            'stok_fisik.min' => 'Stok fisik tidak boleh kurang dari 0.',
            'keterangan.max' => 'Keterangan maksimal 500 karakter.',
            'items.required_if' => 'Minimal pilih satu barang untuk stock opname massal.',
            'items.array' => 'Format data barang tidak valid.',
            'items.*.id_barang.required_with' => 'Barang pada daftar opname tidak valid.',
            'items.*.id_barang.exists' => 'Salah satu barang pada daftar opname tidak ditemukan.',
            'items.*.stok_fisik.integer' => 'Stok fisik pada daftar opname harus berupa angka bulat.',
            'items.*.stok_fisik.min' => 'Stok fisik pada daftar opname tidak boleh kurang dari 0.',
        ]);

        if ($validated['mode_opname'] === 'batch') {
            return $this->storeBatch($request, $validated);
        }

        return $this->storeSingle($validated);
    }

    private function storeSingle(array $validated)
    {
        $nomorOpname = $this->generateNomorStockOpname();

        DB::transaction(function () use ($validated, $nomorOpname) {
            $barang = Barang::where('id_barang', $validated['id_barang'])
                ->where('status_aktif', true)
                ->lockForUpdate()
                ->firstOrFail();

            $this->simpanStockOpnameItem(
                $barang,
                $validated['tanggal'],
                (int) $validated['stok_fisik'],
                $validated['keterangan'] ?? null,
                $nomorOpname
            );
        });

        return redirect()
            ->route('stock-opname.create')
            ->with('success', 'Stock opname berhasil disimpan. Nomor opname: ' . $nomorOpname);
    }

    private function storeBatch(Request $request, array $validated)
    {
        $items = collect($request->input('items', []))
            ->filter(function ($item) {
                return (string) ($item['diproses'] ?? '0') === '1';
            })
            ->values();

        if ($items->isEmpty()) {
            throw ValidationException::withMessages([
                'items' => 'Pilih minimal satu barang yang ingin diproses pada stock opname massal.',
            ]);
        }

        foreach ($items as $index => $item) {
            if (!array_key_exists('stok_fisik', $item) || $item['stok_fisik'] === null || $item['stok_fisik'] === '') {
                throw ValidationException::withMessages([
                    'items' => 'Stok fisik wajib diisi untuk semua barang yang dipilih pada stock opname massal.',
                ]);
            }
        }

        $nomorOpname = $this->generateNomorStockOpname();
        $jumlahDiproses = 0;
        $jumlahBerubah = 0;

        DB::transaction(function () use ($items, $validated, $nomorOpname, &$jumlahDiproses, &$jumlahBerubah) {
            foreach ($items as $item) {
                $barang = Barang::where('id_barang', $item['id_barang'])
                    ->where('status_aktif', true)
                    ->lockForUpdate()
                    ->firstOrFail();

                $hasil = $this->simpanStockOpnameItem(
                    $barang,
                    $validated['tanggal'],
                    (int) $item['stok_fisik'],
                    $validated['keterangan'] ?? null,
                    $nomorOpname
                );

                $jumlahDiproses++;

                if ($hasil['selisih'] !== 0) {
                    $jumlahBerubah++;
                }
            }
        });

        return redirect()
            ->route('stock-opname.create')
            ->with('success', 'Stock opname massal berhasil disimpan. Nomor opname: ' . $nomorOpname . '. Barang diproses: ' . $jumlahDiproses . ', berubah: ' . $jumlahBerubah . '.');
    }

    private function simpanStockOpnameItem(Barang $barang, string $tanggal, int $stokFisik, ?string $keteranganUser, string $nomorOpname): array
    {
        $stokSebelum = (int) $barang->stok_saat_ini;
        $stokSesudah = $stokFisik;
        $selisih = $stokSesudah - $stokSebelum;

        if ($selisih !== 0) {
            $barang->update([
                'stok_saat_ini' => $stokSesudah,
            ]);
        }

        $statusKeterangan = match (true) {
            $selisih > 0 => 'Stok fisik lebih banyak dari stok sistem. Selisih: +' . $selisih,
            $selisih < 0 => 'Stok fisik lebih sedikit dari stok sistem. Selisih: ' . $selisih,
            default => 'Stok fisik sama dengan stok sistem. Tidak ada perubahan stok.',
        };

        $keteranganUser = trim((string) $keteranganUser);
        $keteranganAkhir = $keteranganUser !== ''
            ? $keteranganUser . ' | ' . $statusKeterangan
            : 'Stock opname. ' . $statusKeterangan;

        RiwayatStok::create([
            'id_barang' => $barang->id_barang,
            'tanggal' => $tanggal,
            'jenis_pergerakan' => 'penyesuaian',
            'jumlah' => abs($selisih),
            'stok_sebelum' => $stokSebelum,
            'stok_sesudah' => $stokSesudah,
            'sumber_transaksi' => $nomorOpname,
            'keterangan' => $keteranganAkhir,
            'dibuat_oleh' => Auth::id(),
            'created_at' => now(),
        ]);

        return [
            'stok_sebelum' => $stokSebelum,
            'stok_sesudah' => $stokSesudah,
            'selisih' => $selisih,
        ];
    }

    private function hitungRingkasanBarangOpname($barang, int $batasStokRendah): array
    {
        $totalBarang = $barang->count();
        $totalStokSistem = $barang->sum('stok_saat_ini');

        $totalStokKosong = $barang
            ->where('stok_saat_ini', '<=', 0)
            ->count();

        $totalStokRendah = $barang
            ->filter(function ($item) use ($batasStokRendah) {
                return $item->stok_saat_ini > 0
                    && $item->stok_saat_ini <= $batasStokRendah;
            })
            ->count();

        $totalStokTersedia = $barang
            ->filter(function ($item) use ($batasStokRendah) {
                return $item->stok_saat_ini > $batasStokRendah;
            })
            ->count();

        $totalNormal = $barang
            ->filter(function ($item) {
                return ($item->tipe_perhitungan_harga ?? 'normal') === 'normal';
            })
            ->count();

        $totalIsiKemasan = $barang
            ->filter(function ($item) {
                return ($item->tipe_perhitungan_harga ?? 'normal') === 'isi_kemasan';
            })
            ->count();

        $totalKenaPpn = $barang
            ->filter(function ($item) {
                return (bool) ($item->kena_ppn ?? true);
            })
            ->count();

        $totalNonPpn = $barang
            ->filter(function ($item) {
                return !(bool) ($item->kena_ppn ?? true);
            })
            ->count();

        return compact(
            'totalBarang',
            'totalStokSistem',
            'totalStokKosong',
            'totalStokRendah',
            'totalStokTersedia',
            'totalNormal',
            'totalIsiKemasan',
            'totalKenaPpn',
            'totalNonPpn'
        );
    }

    private function generateNomorStockOpname(): string
    {
        return 'STOCK-OPNAME-' . now()->format('YmdHis') . '-' . str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT);
    }
}
