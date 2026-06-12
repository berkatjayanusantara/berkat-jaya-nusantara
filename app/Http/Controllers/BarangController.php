<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BarangController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;

        $barang = Barang::query()
            ->when($search, function ($query, $search) {
                $query->where('nama_barang', 'like', "%{$search}%")
                    ->orWhere('kode_barang', 'like', "%{$search}%")
                    ->orWhere('satuan', 'like', "%{$search}%")
                    ->orWhere('satuan_hitung_harga', 'like', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('barang.index', compact('barang', 'search'));
    }

    public function create()
    {
        $kodeBarang = $this->generateKodeBarang();
        $satuanOptions = $this->getSatuanOptions();
        $satuanHitungOptions = $this->getSatuanHitungHargaOptions();

        return view('barang.create', compact(
            'kodeBarang',
            'satuanOptions',
            'satuanHitungOptions'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_barang' => 'required|string|max:255',
            'satuan' => [
                'required',
                'string',
                'max:50',
                Rule::in($this->getSatuanOptions()),
            ],
            'stok_saat_ini' => 'required|integer|min:0',
            'harga_beli_terakhir' => 'required|numeric|min:0',
            'harga_jual_default' => 'required|numeric|min:0',
            'tipe_perhitungan_harga' => [
                'required',
                Rule::in(['normal', 'isi_kemasan']),
            ],
            'satuan_hitung_harga' => [
                'nullable',
                'required_if:tipe_perhitungan_harga,isi_kemasan',
                'string',
                'max:50',
                Rule::in($this->getSatuanHitungHargaOptions()),
            ],
            'isi_per_satuan' => [
                'nullable',
                'required_if:tipe_perhitungan_harga,isi_kemasan',
                'numeric',
                'min:0.001',
            ],
            'keterangan' => 'nullable|string',
        ]);

        $tipePerhitunganHarga = $request->tipe_perhitungan_harga;

        Barang::create([
            'kode_barang' => $this->generateKodeBarang(),
            'nama_barang' => trim($request->nama_barang),
            'satuan' => $request->satuan,
            'stok_saat_ini' => $request->stok_saat_ini,
            'harga_beli_terakhir' => $request->harga_beli_terakhir,
            'harga_jual_default' => $request->harga_jual_default,
            'tipe_perhitungan_harga' => $tipePerhitunganHarga,
            'satuan_hitung_harga' => $tipePerhitunganHarga === 'isi_kemasan'
                ? $request->satuan_hitung_harga
                : null,
            'isi_per_satuan' => $tipePerhitunganHarga === 'isi_kemasan'
                ? $request->isi_per_satuan
                : 1,
            'keterangan' => $request->keterangan,
            'status_aktif' => true,
        ]);

        return redirect()
            ->route('barang.index')
            ->with('success', 'Data barang berhasil ditambahkan.');
    }

    public function edit(Barang $barang)
    {
        $satuanOptions = $this->getSatuanOptions();
        $satuanHitungOptions = $this->getSatuanHitungHargaOptions();

        return view('barang.edit', compact(
            'barang',
            'satuanOptions',
            'satuanHitungOptions'
        ));
    }

    public function update(Request $request, Barang $barang)
    {
        $request->validate([
            'nama_barang' => 'required|string|max:255',
            'satuan' => [
                'required',
                'string',
                'max:50',
                Rule::in($this->getSatuanOptions()),
            ],
            'stok_saat_ini' => 'required|integer|min:0',
            'harga_beli_terakhir' => 'required|numeric|min:0',
            'harga_jual_default' => 'required|numeric|min:0',
            'tipe_perhitungan_harga' => [
                'required',
                Rule::in(['normal', 'isi_kemasan']),
            ],
            'satuan_hitung_harga' => [
                'nullable',
                'required_if:tipe_perhitungan_harga,isi_kemasan',
                'string',
                'max:50',
                Rule::in($this->getSatuanHitungHargaOptions()),
            ],
            'isi_per_satuan' => [
                'nullable',
                'required_if:tipe_perhitungan_harga,isi_kemasan',
                'numeric',
                'min:0.001',
            ],
            'keterangan' => 'nullable|string',
            'status_aktif' => 'required|boolean',
        ]);

        $tipePerhitunganHarga = $request->tipe_perhitungan_harga;

        $barang->update([
            'nama_barang' => trim($request->nama_barang),
            'satuan' => $request->satuan,
            'stok_saat_ini' => $request->stok_saat_ini,
            'harga_beli_terakhir' => $request->harga_beli_terakhir,
            'harga_jual_default' => $request->harga_jual_default,
            'tipe_perhitungan_harga' => $tipePerhitunganHarga,
            'satuan_hitung_harga' => $tipePerhitunganHarga === 'isi_kemasan'
                ? $request->satuan_hitung_harga
                : null,
            'isi_per_satuan' => $tipePerhitunganHarga === 'isi_kemasan'
                ? $request->isi_per_satuan
                : 1,
            'keterangan' => $request->keterangan,
            'status_aktif' => $request->status_aktif,
        ]);

        return redirect()
            ->route('barang.index')
            ->with('success', 'Data barang berhasil diperbarui.');
    }

    public function nonaktifkan(Barang $barang)
    {
        $barang->update([
            'status_aktif' => false,
        ]);

        return redirect()
            ->route('barang.index')
            ->with('success', 'Barang berhasil dinonaktifkan.');
    }

    private function generateKodeBarang()
    {
        $lastBarang = Barang::orderBy('id_barang', 'desc')->first();

        if (!$lastBarang) {
            return 'BRG-0001';
        }

        $lastNumber = (int) substr($lastBarang->kode_barang, 4);
        $newNumber = $lastNumber + 1;

        return 'BRG-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    private function getSatuanOptions(): array
    {
        return [
            'pcs',
            'box',
            'dus',
            'pack',
            'bal',
            'ball',
            'karung',
            'sak',
            'kg',
            'gram',
            'liter',
            'meter',
            'roll',
            'kodi',
            'set',
        ];
    }

    private function getSatuanHitungHargaOptions(): array
    {
        return [
            'kg',
            'gram',
            'liter',
            'meter',
            'pcs',
        ];
    }
}
