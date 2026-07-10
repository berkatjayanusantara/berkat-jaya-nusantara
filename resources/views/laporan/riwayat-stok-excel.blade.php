@php
$namaPerusahaan = 'CV. BERKAT JAYA NUSANTARA';
$alamatPerusahaan = 'Jl. Jelambar Utama 1 No. 6A RT. 007 RW. 004, Jakarta Barat 11460';
$teleponPerusahaan = '(021) 5664892, 5676277 | WA: 085691801290';

$periodeAwal = $tanggalAwal === 'awal' ? 'Awal' : $tanggalAwal;
$periodeAkhir = $tanggalAkhir === 'akhir' ? 'Akhir' : $tanggalAkhir;
$nettoPerubahan = $totalNettoPerubahan ?? (($totalSelisihPlus ?? 0) - ($totalSelisihMinus ?? 0));

$normalisasiJenisPpn = function ($item) {
if (!$item) {
return 'ppn_normal';
}

$jenisPpn = $item->jenis_ppn ?? null;

if (in_array($jenisPpn, ['non_ppn', 'ppn_normal', 'ppn_dpp_nilai_lain'], true)) {
return $jenisPpn;
}

$kenaPpnLegacy = (bool) ($item->kena_ppn ?? true);

return $kenaPpnLegacy ? 'ppn_normal' : 'non_ppn';
};

$labelJenisPpn = function ($jenisPpn) {
return match ($jenisPpn) {
'non_ppn' => 'Non PPN',
'ppn_normal' => 'PPN Normal',
'ppn_dpp_nilai_lain' => 'PPN DPP Nilai Lain',
default => 'PPN Normal',
};
};
@endphp

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Laporan Riwayat Stok</title>

    <style>
        table {
            border-collapse: collapse;
        }

        .company-title {
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            background-color: #eff6ff;
        }

        .company-info {
            text-align: center;
            font-weight: bold;
        }

        .title {
            font-size: 18px;
            font-weight: bold;
            text-align: center;
            background-color: #dbeafe;
        }

        .subtitle {
            text-align: center;
            font-weight: bold;
        }

        .section-header {
            font-weight: bold;
            background-color: #dbeafe;
        }

        .header {
            font-weight: bold;
            background-color: #eeeeee;
            text-align: center;
        }

        .bold {
            font-weight: bold;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-format {
            mso-number-format: "\@";
        }

        .number-format {
            mso-number-format: "#,##0.###";
        }

        .total-row {
            font-weight: bold;
            background-color: #eff6ff;
        }

        .success {
            background-color: #dcfce7;
        }

        .warning {
            background-color: #fef3c7;
        }

        .danger {
            background-color: #fee2e2;
        }

        .info {
            background-color: #dbeafe;
        }

        .purple {
            background-color: #ede9fe;
        }

        .muted {
            background-color: #f3f4f6;
        }
    </style>
</head>

<body>
    <table border="1">
        <tr>
            <td colspan="23" class="company-title">{{ $namaPerusahaan }}</td>
        </tr>

        <tr>
            <td colspan="23" class="company-info">{{ $alamatPerusahaan }}</td>
        </tr>

        <tr>
            <td colspan="23" class="company-info">Telp: {{ $teleponPerusahaan }}</td>
        </tr>

        <tr>
            <td colspan="23" class="title">LAPORAN RIWAYAT STOK</td>
        </tr>

        <tr>
            <td colspan="23" class="subtitle">Periode: {{ $periodeAwal }} s/d {{ $periodeAkhir }}</td>
        </tr>

        <tr>
            <td colspan="23" class="subtitle">Dicetak: {{ now()->format('d-m-Y H:i') }}</td>
        </tr>

        <tr>
            <td colspan="23"></td>
        </tr>

        <tr class="section-header">
            <td colspan="23">Ringkasan Laporan</td>
        </tr>

        <tr>
            <td class="bold">Total Data</td>
            <td class="text-center number-format">{{ $totalData ?? 0 }}</td>

            <td class="bold">Barang Unik</td>
            <td class="text-center number-format">{{ $totalBarangUnik ?? 0 }}</td>

            <td class="bold">Total Barang Masuk</td>
            <td class="text-center number-format">{{ $totalMasuk ?? 0 }}</td>

            <td class="bold">Transaksi Masuk</td>
            <td class="text-center number-format">{{ $totalTransaksiMasuk ?? 0 }}</td>

            <td class="bold">Total Barang Keluar</td>
            <td class="text-center number-format">{{ $totalKeluar ?? 0 }}</td>

            <td class="bold">Transaksi Keluar</td>
            <td class="text-center number-format">{{ $totalTransaksiKeluar ?? 0 }}</td>

            <td class="bold">Jumlah Penyesuaian</td>
            <td class="text-center number-format">{{ $totalPenyesuaian ?? 0 }}</td>

            <td class="bold">Qty Penyesuaian</td>
            <td class="text-center number-format">{{ $totalJumlahPenyesuaian ?? 0 }}</td>

            <td class="bold">Stock Opname</td>
            <td class="text-center number-format">{{ $totalOpname ?? 0 }}</td>

            <td class="bold">Non Opname</td>
            <td colspan="4" class="text-center number-format">{{ $totalNonOpname ?? 0 }}</td>
        </tr>

        <tr>
            <td class="bold">Selisih Bertambah</td>
            <td colspan="3" class="text-center number-format">{{ $totalSelisihPlus ?? 0 }}</td>

            <td class="bold">Selisih Berkurang</td>
            <td colspan="3" class="text-center number-format">{{ $totalSelisihMinus ?? 0 }}</td>

            <td class="bold">Netto Perubahan</td>
            <td colspan="3" class="text-center number-format">{{ $nettoPerubahan }}</td>

            <td class="bold">Tipe Normal</td>
            <td colspan="3" class="text-center number-format">{{ $totalBarangNormal ?? 0 }}</td>

            <td class="bold">Tipe Isi Kemasan</td>
            <td colspan="6" class="text-center number-format">{{ $totalBarangIsiKemasan ?? 0 }}</td>
        </tr>

        <tr>
            <td class="bold">Barang Kena PPN</td>
            <td colspan="4" class="text-center number-format">{{ $totalBarangKenaPpn ?? 0 }}</td>

            <td class="bold">Barang Non PPN</td>
            <td colspan="4" class="text-center number-format">{{ $totalBarangNonPpn ?? 0 }}</td>

            <td class="bold">PPN Normal</td>
            <td colspan="4" class="text-center number-format">{{ $totalBarangPpnNormal ?? 0 }}</td>

            <td class="bold">PPN DPP Nilai Lain</td>
            <td colspan="8" class="text-center number-format">{{ $totalBarangPpnDppNilaiLain ?? 0 }}</td>
        </tr>

        <tr>
            <td colspan="23"></td>
        </tr>

        <tr class="header">
            <td>No</td>
            <td>Tanggal</td>
            <td>Waktu Input</td>
            <td>Kode Barang</td>
            <td>Nama Barang</td>
            <td>Satuan Transaksi</td>
            <td>Tipe Harga</td>
            <td>Satuan Hitung Harga</td>
            <td>Isi Per Satuan</td>
            <td>Status PPN Barang</td>
            <td>Jenis PPN</td>
            <td>Status Barang</td>
            <td>Jenis Pergerakan</td>
            <td>Tipe Riwayat</td>
            <td>Jumlah</td>
            <td>Stok Sebelum</td>
            <td>Stok Sesudah</td>
            <td>Selisih</td>
            <td>Arah Selisih</td>
            <td>Sumber Transaksi</td>
            <td>Keterangan</td>
            <td>Dibuat Oleh</td>
            <td>Keterangan Barang</td>
        </tr>

        @foreach ($riwayatStok as $item)
        @php
        $barangItem = $item->barang;
        $stokSebelum = (int) ($item->stok_sebelum ?? 0);
        $stokSesudah = (int) ($item->stok_sesudah ?? 0);
        $selisih = $stokSesudah - $stokSebelum;
        $isOpname = str_starts_with((string) $item->sumber_transaksi, 'STOCK-OPNAME');

        $tipePerhitungan = $barangItem->tipe_perhitungan_harga ?? 'normal';
        $satuan = $barangItem->satuan ?? '-';
        $satuanHitung = $barangItem->satuan_hitung_harga ?? $satuan;
        $isiPerSatuan = (float) ($barangItem->isi_per_satuan ?? 1);
        $statusBarang = (bool) ($barangItem->status_aktif ?? true) ? 'Aktif' : 'Nonaktif';

        $jenisPpn = $normalisasiJenisPpn($barangItem);
        $statusPpnText = $jenisPpn === 'non_ppn' ? 'Non PPN' : 'Kena PPN';
        $jenisPpnText = $labelJenisPpn($jenisPpn);

        if ($item->jenis_pergerakan === 'masuk') {
        $jenisLabel = 'Masuk';
        $jenisClass = 'success';
        } elseif ($item->jenis_pergerakan === 'keluar') {
        $jenisLabel = 'Keluar';
        $jenisClass = 'danger';
        } else {
        $jenisLabel = 'Penyesuaian';
        $jenisClass = 'warning';
        }

        if ($selisih > 0) {
        $arahSelisih = 'Bertambah';
        $selisihClass = 'success';
        } elseif ($selisih < 0) {
            $arahSelisih='Berkurang' ;
            $selisihClass='danger' ;
            } else {
            $arahSelisih='Tetap' ;
            $selisihClass='muted' ;
            }

            $jenisPpnClass=match ($jenisPpn) { 'non_ppn'=> 'muted',
            'ppn_normal' => 'info',
            'ppn_dpp_nilai_lain' => 'purple',
            default => 'info',
            };
            @endphp

            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>

                <td class="text-center">
                    {{ $item->tanggal ? $item->tanggal->format('d-m-Y') : '-' }}
                </td>

                <td class="text-center">
                    {{ $item->created_at ? $item->created_at->format('d-m-Y H:i') : '-' }}
                </td>

                <td class="text-format">{{ $barangItem->kode_barang ?? '-' }}</td>

                <td>{{ $barangItem->nama_barang ?? '-' }}</td>

                <td class="text-center">{{ strtoupper($satuan) }}</td>

                <td class="text-center">
                    {{ $tipePerhitungan === 'isi_kemasan' ? 'Isi Kemasan' : 'Normal' }}
                </td>

                <td class="text-center">{{ strtoupper($satuanHitung) }}</td>

                <td class="text-center number-format">{{ $isiPerSatuan }}</td>

                <td class="text-center {{ $jenisPpn === 'non_ppn' ? 'muted' : 'success' }}">
                    {{ $statusPpnText }}
                </td>

                <td class="text-center {{ $jenisPpnClass }}">
                    {{ $jenisPpnText }}
                </td>

                <td class="text-center {{ $statusBarang === 'Aktif' ? 'success' : 'danger' }}">
                    {{ $statusBarang }}
                </td>

                <td class="text-center {{ $jenisClass }}">{{ $jenisLabel }}</td>

                <td class="text-center {{ $isOpname ? 'info' : '' }}">
                    {{ $isOpname ? 'Stock Opname' : 'Transaksi' }}
                </td>

                <td class="text-center number-format">{{ $item->jumlah ?? 0 }}</td>

                <td class="text-center number-format">{{ $stokSebelum }}</td>

                <td class="text-center number-format">{{ $stokSesudah }}</td>

                <td class="text-center number-format {{ $selisihClass }}">{{ $selisih }}</td>

                <td class="text-center {{ $selisihClass }}">{{ $arahSelisih }}</td>

                <td class="text-format">{{ $item->sumber_transaksi ?? '-' }}</td>

                <td>{{ $item->keterangan ?? '-' }}</td>

                <td>{{ $item->user->nama_user ?? '-' }}</td>

                <td>{{ $barangItem->keterangan ?? '-' }}</td>
            </tr>
            @endforeach

            <tr>
                <td colspan="23"></td>
            </tr>

            <tr class="total-row">
                <td colspan="11" class="bold">TOTAL DATA</td>
                <td colspan="12" class="text-center number-format">{{ $totalData ?? 0 }}</td>
            </tr>

            <tr class="total-row">
                <td colspan="11" class="bold">TOTAL BARANG MASUK</td>
                <td colspan="12" class="text-center number-format">{{ $totalMasuk ?? 0 }}</td>
            </tr>

            <tr class="total-row">
                <td colspan="11" class="bold">TOTAL BARANG KELUAR</td>
                <td colspan="12" class="text-center number-format">{{ $totalKeluar ?? 0 }}</td>
            </tr>

            <tr class="total-row">
                <td colspan="11" class="bold">JUMLAH PENYESUAIAN</td>
                <td colspan="12" class="text-center number-format">{{ $totalPenyesuaian ?? 0 }}</td>
            </tr>

            <tr class="total-row">
                <td colspan="11" class="bold">JUMLAH STOCK OPNAME</td>
                <td colspan="12" class="text-center number-format">{{ $totalOpname ?? 0 }}</td>
            </tr>

            <tr class="total-row">
                <td colspan="11" class="bold">TOTAL SELISIH BERTAMBAH</td>
                <td colspan="12" class="text-center number-format">{{ $totalSelisihPlus ?? 0 }}</td>
            </tr>

            <tr class="total-row">
                <td colspan="11" class="bold">TOTAL SELISIH BERKURANG</td>
                <td colspan="12" class="text-center number-format">{{ $totalSelisihMinus ?? 0 }}</td>
            </tr>

            <tr class="total-row">
                <td colspan="11" class="bold">NETTO PERUBAHAN</td>
                <td colspan="12" class="text-center number-format">{{ $nettoPerubahan }}</td>
            </tr>

            <tr class="total-row">
                <td colspan="11" class="bold">BARANG KENA PPN</td>
                <td colspan="12" class="text-center number-format">{{ $totalBarangKenaPpn ?? 0 }}</td>
            </tr>

            <tr class="total-row">
                <td colspan="11" class="bold">BARANG NON PPN</td>
                <td colspan="12" class="text-center number-format">{{ $totalBarangNonPpn ?? 0 }}</td>
            </tr>

            <tr class="total-row">
                <td colspan="11" class="bold">BARANG PPN NORMAL</td>
                <td colspan="12" class="text-center number-format">{{ $totalBarangPpnNormal ?? 0 }}</td>
            </tr>

            <tr class="total-row">
                <td colspan="11" class="bold">BARANG PPN DPP NILAI LAIN</td>
                <td colspan="12" class="text-center number-format">{{ $totalBarangPpnDppNilaiLain ?? 0 }}</td>
            </tr>
    </table>
</body>

</html>