<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use Illuminate\Http\Request;
use App\Http\Requests\BarangStoreRequest;
use App\Http\Requests\BarangUpdateRequest;
use App\Http\Controllers\Traits\BarangOptionsTrait;

class BarangController extends Controller
{
    use BarangOptionsTrait;

    public function __construct()
    {
        // Example authorization, assuming 'admin' role has necessary permissions
        // $this->middleware('can:manage-barang')->except(['index']);
    }

    public function index(Request $request)
    {
        $search = $request->search;

        $barang = Barang::query()
            ->when($search, function ($query, $search) {
                $query->where('nama_barang', 'like', "%{$search}%")
                    ->orWhere('kode_barang', 'like', "%{$search}%")
                    ->orWhere('satuan', 'like', "%{$search}%")
                    ->orWhere('satuan_hitung_harga', 'like', "%{$search}%")
                    ->orWhere('jenis_ppn', 'like', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('barang.index', compact('barang', 'search'));
    }

    public function create()
    {
        return view('barang.create', [
            'kodeBarang' => $this->generateKodeBarang(),
            'satuanOptions' => $this->getSatuanOptions(),
            'satuanHitungOptions' => $this->getSatuanHitungHargaOptions(),
            'jenisPpnOptions' => $this->getJenisPpnOptions(),
        ]);
    }

    public function store(BarangStoreRequest $request)
    {
        $tipePerhitunganHarga = $request->tipe_perhitungan_harga;
        $jenisPpn = $this->normalisasiJenisPpn($request->jenis_ppn);

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
            'jenis_ppn' => $jenisPpn,
            'kena_ppn' => $this->isBarangKenaPpn($jenisPpn),
            'keterangan' => $request->keterangan,
            'status_aktif' => true,
        ]);

        return redirect()
            ->route('barang.index')
            ->with('success', 'Data barang berhasil ditambahkan.');
    }

    public function edit(Barang $barang)
    {
        return view('barang.edit', [
            'barang' => $barang,
            'satuanOptions' => $this->getSatuanOptions(),
            'satuanHitungOptions' => $this->getSatuanHitungHargaOptions(),
            'jenisPpnOptions' => $this->getJenisPpnOptions(),
        ]);
    }

    public function update(BarangUpdateRequest $request, Barang $barang)
    {
        $tipePerhitunganHarga = $request->tipe_perhitungan_harga;
        $jenisPpn = $this->normalisasiJenisPpn($request->jenis_ppn);

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
            'jenis_ppn' => $jenisPpn,
            'kena_ppn' => $this->isBarangKenaPpn($jenisPpn),
            'keterangan' => $request->keterangan,
            'status_aktif' => $request->status_aktif,
        ]);

        return redirect()
            ->route('barang.index')
            ->with('success', 'Data barang berhasil diperbarui.');
    }

    public function nonaktifkan(Barang $barang)
    {
        $barang->update(['status_aktif' => false]);

        return redirect()
            ->route('barang.index')
            ->with('success', 'Barang berhasil dinonaktifkan.');
    }
}
