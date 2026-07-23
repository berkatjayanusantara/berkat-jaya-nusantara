<?php

namespace App\Http\Controllers\Traits;

use App\Models\Barang;

trait BarangOptionsTrait
{
    private function generateKodeBarang(): string
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
            'pcs', 'box', 'dus', 'derigen', 'botol', 'pack', 'bal',
            'ball', 'karung', 'sak', 'kg', 'gram', 'liter', 'meter', 'roll',
            'kodi', 'set',
        ];
    }

    private function getSatuanHitungHargaOptions(): array
    {
        return [
            'kg', 'gram', 'liter', 'meter', 'pcs',
        ];
    }

    private function getJenisPpnOptions(): array
    {
        return [
            'non_ppn' => 'Non PPN',
            'ppn_normal' => 'PPN Normal',
            'ppn_dpp_nilai_lain' => 'PPN DPP Nilai Lain / Khusus',
        ];
    }

    private function normalisasiJenisPpn(?string $jenisPpn): string
    {
        if (array_key_exists($jenisPpn, $this->getJenisPpnOptions())) {
            return $jenisPpn;
        }

        return 'ppn_dpp_nilai_lain';
    }

    private function isBarangKenaPpn(string $jenisPpn): bool
    {
        return $jenisPpn !== 'non_ppn';
    }
}
