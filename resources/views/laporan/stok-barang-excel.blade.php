@php
$namaPerusahaan = 'CV. BERKAT JAYA NUSANTARA';
$alamatPerusahaan = 'Jl. Jelambar Utama 1 No. 6A RT. 007 RW. 004, Jakarta Barat 11460';
$teleponPerusahaan = '(021) 5664892, 5676277';
$totalPotensiMargin = $totalEstimasiLabaKotor ?? (($totalEstimasiNilaiJual ?? 0) - ($totalNilaiStok ?? 0));
@endphp

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Laporan Stok Barang</title>

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

        .currency {
            mso-number-format: "#,##0";
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
    </style>
</head>

<body>
    <table border="1">
        <tr>
            <td colspan="20" class="company-title">{{ $namaPerusahaan }}</td>
        </tr>

        <tr>
            <td colspan="20" class="company-info">{{ $alamatPerusahaan }}</td>
        </tr>

        <tr>
            <td colspan="20" class="company-info">Telp: {{ $teleponPerusahaan }}</td>
        </tr>

        <tr>
            <td colspan="20" class="title">LAPORAN STOK BARANG</td>
        </tr>

        <tr>
            <td colspan="20" class="subtitle">Batas Stok Rendah: {{ $batasStokRendah ?? 5 }}</td>
        </tr>

        <tr>
            <td colspan="20" class="subtitle">Dicetak: {{ now()->format('d-m-Y H:i') }}</td>
        </tr>

        <tr>
            <td colspan="20"></td>
        </tr>

        <tr class="section-header">
            <td colspan="20">Ringkasan Laporan</td>
        </tr>

        <tr>
            <td class="bold">Total Jenis Barang</td>
            <td class="text-center">{{ $totalBarang ?? 0 }}</td>

            <td class="bold">Aktif</td>
            <td class="text-center">{{ $totalBarangAktif ?? 0 }}</td>

            <td class="bold">Nonaktif</td>
            <td class="text-center">{{ $totalBarangNonaktif ?? 0 }}</td>

            <td class="bold">Total Stok</td>
            <td class="text-center number-format">{{ $totalStok ?? 0 }}</td>

            <td class="bold">Qty Harga</td>
            <td class="text-center number-format">{{ $totalJumlahSatuanHarga ?? 0 }}</td>

            <td class="bold">Kosong</td>
            <td class="text-center">{{ $totalBarangKosong ?? 0 }}</td>

            <td class="bold">Stok Rendah</td>
            <td class="text-center">{{ $totalBarangStokRendah ?? 0 }}</td>

            <td class="bold">Tersedia</td>
            <td colspan="5" class="text-center">{{ $totalBarangTersedia ?? 0 }}</td>
        </tr>

        <tr>
            <td class="bold">Barang Normal</td>
            <td colspan="3" class="text-center">{{ $totalBarangNormal ?? 0 }}</td>

            <td class="bold">Barang Isi Kemasan</td>
            <td colspan="3" class="text-center">{{ $totalBarangIsiKemasan ?? 0 }}</td>

            <td class="bold">Kena PPN</td>
            <td colspan="3" class="text-center">{{ $totalBarangKenaPpn ?? 0 }}</td>

            <td class="bold">Non PPN</td>
            <td colspan="7" class="text-center">{{ $totalBarangNonPpn ?? 0 }}</td>
        </tr>

        <tr>
            <td class="bold">Estimasi Nilai Stok</td>
            <td colspan="5" class="text-right currency">{{ $totalNilaiStok ?? 0 }}</td>

            <td class="bold">Estimasi Nilai Jual</td>
            <td colspan="5" class="text-right currency">{{ $totalEstimasiNilaiJual ?? 0 }}</td>

            <td class="bold">Estimasi Margin Kotor</td>
            <td colspan="7" class="text-right currency">{{ $totalPotensiMargin }}</td>
        </tr>

        <tr>
            <td colspan="20"></td>
        </tr>

        <tr class="header">
            <td>No</td>
            <td>Kode Barang</td>
            <td>Nama Barang</td>
            <td>Satuan Stok</td>
            <td>Tipe Perhitungan Harga</td>
            <td>Satuan Hitung Harga</td>
            <td>Isi Per Satuan</td>
            <td>Status PPN</td>
            <td>Stok Saat Ini</td>
            <td>Qty Hitung Harga</td>
            <td>Harga Beli Terakhir</td>
            <td>Nilai Stok</td>
            <td>Harga Jual Default</td>
            <td>Satuan Harga Jual</td>
            <td>Estimasi Nilai Jual</td>
            <td>Estimasi Margin</td>
            <td>Status Stok</td>
            <td>Status Barang</td>
            <td>Keterangan Perhitungan</td>
            <td>Keterangan Barang</td>
        </tr>

        @foreach ($barang as $item)
        @php
        $stokSaatIni = (float) ($item->stok_saat_ini ?? 0);
        $hargaBeli = (float) ($item->harga_beli_terakhir ?? 0);
        $hargaJual = (float) ($item->harga_jual_default ?? 0);

        $tipePerhitungan = $item->tipe_perhitungan_harga ?? 'normal';
        $satuan = $item->satuan ?? '-';
        $satuanHitung = $item->satuan_hitung_harga ?? $satuan;
        $isiPerSatuan = $tipePerhitungan === 'isi_kemasan' ? (float) ($item->isi_per_satuan ?? 1) : 1;
        $jumlahSatuanHarga = $stokSaatIni * $isiPerSatuan;

        $nilaiStok = $stokSaatIni * $hargaBeli;
        $estimasiNilaiJual = $jumlahSatuanHarga * $hargaJual;
        $estimasiMargin = $estimasiNilaiJual - $nilaiStok;
        $kenaPpn = (bool) ($item->kena_ppn ?? true);

        if ($stokSaatIni <= 0) {
            $statusStok='Kosong' ;
            $statusClass='danger' ;
            } elseif ($stokSaatIni <=($batasStokRendah ?? 5)) {
            $statusStok='Stok Rendah' ;
            $statusClass='warning' ;
            } else {
            $statusStok='Tersedia' ;
            $statusClass='success' ;
            }

            $statusBarang=$item->status_aktif ? 'Aktif' : 'Nonaktif';
            $statusPpnText = $kenaPpn ? 'Kena PPN' : 'Non PPN';
            $satuanHargaJual = $tipePerhitungan === 'isi_kemasan' ? $satuanHitung : $satuan;

            if ($tipePerhitungan === 'isi_kemasan') {
            $tipeText = 'Isi Kemasan';
            $keteranganPerhitungan = '1 ' . strtoupper($satuan) . ' = ' . rtrim(rtrim(number_format($isiPerSatuan, 3, ',', '.'), '0'), ',') . ' ' . strtoupper($satuanHitung) . '. Harga jual dihitung per ' . strtoupper($satuanHitung) . '.';
            } else {
            $tipeText = 'Normal';
            $keteranganPerhitungan = 'Harga jual dihitung normal per ' . strtoupper($satuan) . '.';
            }
            @endphp

            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td class="text-format">{{ $item->kode_barang }}</td>
                <td>{{ $item->nama_barang }}</td>
                <td class="text-center">{{ strtoupper($satuan) }}</td>
                <td class="text-center">{{ $tipeText }}</td>
                <td class="text-center">{{ strtoupper($satuanHitung) }}</td>
                <td class="text-center number-format">{{ $isiPerSatuan }}</td>
                <td class="text-center">{{ $statusPpnText }}</td>
                <td class="text-center number-format">{{ $stokSaatIni }}</td>
                <td class="text-center number-format">{{ $jumlahSatuanHarga }}</td>
                <td class="text-right currency">{{ $hargaBeli }}</td>
                <td class="text-right currency">{{ $nilaiStok }}</td>
                <td class="text-right currency">{{ $hargaJual }}</td>
                <td class="text-center">{{ strtoupper($satuanHargaJual) }}</td>
                <td class="text-right currency">{{ $estimasiNilaiJual }}</td>
                <td class="text-right currency">{{ $estimasiMargin }}</td>
                <td class="text-center {{ $statusClass }}">{{ $statusStok }}</td>
                <td class="text-center {{ $item->status_aktif ? 'success' : 'danger' }}">{{ $statusBarang }}</td>
                <td>{{ $keteranganPerhitungan }}</td>
                <td>{{ $item->keterangan ?? '-' }}</td>
            </tr>
            @endforeach

            <tr>
                <td colspan="20"></td>
            </tr>

            <tr class="total-row">
                <td colspan="10" class="bold">TOTAL JENIS BARANG</td>
                <td colspan="10" class="text-center">{{ $totalBarang ?? 0 }}</td>
            </tr>

            <tr class="total-row">
                <td colspan="10" class="bold">TOTAL STOK</td>
                <td colspan="10" class="text-center number-format">{{ $totalStok ?? 0 }}</td>
            </tr>

            <tr class="total-row">
                <td colspan="10" class="bold">TOTAL QTY HITUNG HARGA</td>
                <td colspan="10" class="text-center number-format">{{ $totalJumlahSatuanHarga ?? 0 }}</td>
            </tr>

            <tr class="total-row">
                <td colspan="10" class="bold">JUMLAH BARANG KOSONG</td>
                <td colspan="10" class="text-center">{{ $totalBarangKosong ?? 0 }}</td>
            </tr>

            <tr class="total-row">
                <td colspan="10" class="bold">JUMLAH BARANG STOK RENDAH</td>
                <td colspan="10" class="text-center">{{ $totalBarangStokRendah ?? 0 }}</td>
            </tr>

            <tr class="total-row">
                <td colspan="10" class="bold">ESTIMASI NILAI STOK</td>
                <td colspan="10" class="text-right currency">{{ $totalNilaiStok ?? 0 }}</td>
            </tr>

            <tr class="total-row">
                <td colspan="10" class="bold">ESTIMASI NILAI JUAL</td>
                <td colspan="10" class="text-right currency">{{ $totalEstimasiNilaiJual ?? 0 }}</td>
            </tr>

            <tr class="total-row">
                <td colspan="10" class="bold">ESTIMASI MARGIN KOTOR</td>
                <td colspan="10" class="text-right currency">{{ $totalPotensiMargin }}</td>
            </tr>
    </table>
</body>

</html>