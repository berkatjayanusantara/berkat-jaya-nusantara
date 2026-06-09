<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\RiwayatStok;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockOpnameController extends Controller
{
    public function create(Request $request)
    {
        $search = $request->search;

        $barang = Barang::query()
            ->where('status_aktif', true)
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('kode_barang', 'like', "%{$search}%")
                        ->orWhere('nama_barang', 'like', "%{$search}%");
                });
            })
            ->orderBy('nama_barang')
            ->paginate(10)
            ->withQueryString();

        return view('stock-opname.create', compact('barang', 'search'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => ['required', 'date'],
            'id_barang' => ['required', 'exists:barang,id_barang'],
            'stok_fisik' => ['required', 'integer', 'min:0'],
            'keterangan' => ['nullable', 'string', 'max:500'],
        ], [
            'tanggal.required' => 'Tanggal opname wajib diisi.',
            'id_barang.required' => 'Barang wajib dipilih.',
            'id_barang.exists' => 'Barang tidak ditemukan.',
            'stok_fisik.required' => 'Stok fisik wajib diisi.',
            'stok_fisik.integer' => 'Stok fisik harus berupa angka.',
            'stok_fisik.min' => 'Stok fisik tidak boleh kurang dari 0.',
            'keterangan.max' => 'Keterangan maksimal 500 karakter.',
        ]);

        DB::transaction(function () use ($validated) {
            $barang = Barang::where('id_barang', $validated['id_barang'])
                ->lockForUpdate()
                ->firstOrFail();

            $stokSebelum = (int) $barang->stok_saat_ini;
            $stokSesudah = (int) $validated['stok_fisik'];
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

            RiwayatStok::create([
                'id_barang' => $barang->id_barang,
                'tanggal' => $validated['tanggal'],
                'jenis_pergerakan' => 'penyesuaian',
                'jumlah' => abs($selisih),
                'stok_sebelum' => $stokSebelum,
                'stok_sesudah' => $stokSesudah,
                'sumber_transaksi' => 'STOCK-OPNAME-' . now()->format('YmdHis'),
                'keterangan' => $validated['keterangan'] ?: 'Stock opname. ' . $statusKeterangan,
                'dibuat_oleh' => Auth::id(),
                'created_at' => now(),
            ]);
        });

        return redirect()
            ->route('stock-opname.create')
            ->with('success', 'Stock opname berhasil disimpan dan riwayat stok sudah dicatat.');
    }
}
