<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Traits\BarangOptionsTrait;

class BarangUpdateRequest extends FormRequest
{
    use BarangOptionsTrait;

    public function authorize(): bool
    {
        // Set authorization logic here
        return true;
    }

    public function rules(): array
    {
        return [
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
            'jenis_ppn' => [
                'required',
                Rule::in(array_keys($this->getJenisPpnOptions())),
            ],
            'keterangan' => 'nullable|string',
            'status_aktif' => 'required|boolean',
        ];
    }
}
