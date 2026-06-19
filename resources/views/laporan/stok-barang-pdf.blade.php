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
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 7.3px;
            color: #111827;
        }

        .company {
            text-align: center;
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .company-info {
            text-align: center;
            font-size: 8px;
            color: #4b5563;
            margin-bottom: 2px;
        }

        .title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin-top: 6px;
            margin-bottom: 3px;
        }

        .subtitle {
            text-align: center;
            font-size: 9px;
            margin-bottom: 10px;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        .summary-table td {
            border: 1px solid #d1d5db;
            padding: 4px;
            vertical-align: top;
        }

        .summary-label {
            font-size: 7px;
            color: #4b5563;
        }

        .summary-value {
            font-size: 9px;
            font-weight: bold;
            margin-top: 2px;
        }

        .small-text {
            font-size: 6.6px;
            color: #4b5563;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th {
            border: 1px solid #9ca3af;
            background-color: #e5e7eb;
            padding: 4px 2px;
            font-weight: bold;
            text-align: center;
        }

        .data-table td {
            border: 1px solid #d1d5db;
            padding: 3px 2px;
            vertical-align: top;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .status-kosong {
            color: #b91c1c;
            font-weight: bold;
        }

        .status-rendah {
            color: #92400e;
            font-weight: bold;
        }

        .status-tersedia {
            color: #047857;
            font-weight: bold;
        }

        .status-nonaktif {
            color: #b91c1c;
            font-weight: bold;
        }

        .status-aktif {
            color: #047857;
            font-weight: bold;
        }

        .status-ppn {
            color: #1d4ed8;
            font-weight: bold;
        }

        .footer {
            margin-top: 10px;
            font-size: 8px;
            color: #6b7280;
            text-align: right;
        }
    </style>
</head>

<body>
    <div class="company">{{ $namaPerusahaan }}</div>
    <div class="company-info">{{ $alamatPerusahaan }}</div>
    <div class="company-info">Telp: {{ $teleponPerusahaan }}</div>

    <div class="title">LAPORAN STOK BARANG</div>

    <div class="subtitle">
        Batas Stok Rendah: {{ $batasStokRendah ?? 5 }}
        |
        Dicetak: {{ now()->format('d-m-Y H:i') }}
    </div>

    <table class="summary-table">
        <tr>
            <td>
                <div class="summary-label">Total Jenis Barang</div>
                <div class="summary-value">{{ $totalBarang ?? 0 }}</div>
                <div class="small-text">Aktif: {{ $totalBarangAktif ?? 0 }} | Nonaktif: {{ $totalBarangNonaktif ?? 0 }}</div>
            </td>

            <td>
                <div class="summary-label">Total Stok</div>
                <div class="summary-value">{{ number_format($totalStok ?? 0, 0, ',', '.') }}</div>
                <div class="small-text">Qty harga: {{ number_format($totalJumlahSatuanHarga ?? 0, 0, ',', '.') }}</div>
            </td>

            <td>
                <div class="summary-label">Barang Kosong</div>
                <div class="summary-value">{{ $totalBarangKosong ?? 0 }}</div>
                <div class="small-text">Stok rendah: {{ $totalBarangStokRendah ?? 0 }}</div>
            </td>

            <td>
                <div class="summary-label">Stok Tersedia</div>
                <div class="summary-value">{{ $totalBarangTersedia ?? 0 }}</div>
                <div class="small-text">Batas rendah: {{ $batasStokRendah ?? 5 }}</div>
            </td>
        </tr>

        <tr>
            <td>
                <div class="summary-label">Tipe Harga</div>
                <div class="summary-value">Normal: {{ $totalBarangNormal ?? 0 }}</div>
                <div class="small-text">Isi kemasan: {{ $totalBarangIsiKemasan ?? 0 }}</div>
            </td>

            <td>
                <div class="summary-label">Status PPN</div>
                <div class="summary-value">Kena: {{ $totalBarangKenaPpn ?? 0 }}</div>
                <div class="small-text">Non: {{ $totalBarangNonPpn ?? 0 }}</div>
            </td>

            <td>
                <div class="summary-label">Estimasi Nilai Stok</div>
                <div class="summary-value">Rp {{ number_format($totalNilaiStok ?? 0, 0, ',', '.') }}</div>
            </td>

            <td>
                <div class="summary-label">Estimasi Nilai Jual</div>
                <div class="summary-value">Rp {{ number_format($totalEstimasiNilaiJual ?? 0, 0, ',', '.') }}</div>
                <div class="small-text">Margin: Rp {{ number_format($totalPotensiMargin, 0, ',', '.') }}</div>
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 3%;">No</th>
                <th style="width: 7%;">Kode</th>
                <th style="width: 15%;">Nama Barang</th>
                <th style="width: 6%;">Satuan</th>
                <th style="width: 11%;">Hitung Harga</th>
                <th style="width: 5%;">PPN</th>
                <th style="width: 6%;">Stok</th>
                <th style="width: 7%;">Qty Harga</th>
                <th style="width: 8%;">Harga Beli</th>
                <th style="width: 9%;">Nilai Stok</th>
                <th style="width: 8%;">Harga Jual</th>
                <th style="width: 9%;">Est. Jual</th>
                <th style="width: 8%;">Margin</th>
                <th style="width: 8%;">Status</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($barang as $item)
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
                $statusClass='status-kosong' ;
                } elseif ($stokSaatIni <=($batasStokRendah ?? 5)) {
                $statusStok='Rendah' ;
                $statusClass='status-rendah' ;
                } else {
                $statusStok='Tersedia' ;
                $statusClass='status-tersedia' ;
                }

                $statusBarangClass=$item->status_aktif ? 'status-aktif' : 'status-nonaktif';

                if ($tipePerhitungan === 'isi_kemasan') {
                $perhitunganText = 'Isi Kemasan';
                $perhitunganDetail = '1 ' . strtoupper($satuan) . ' = ' . rtrim(rtrim(number_format($isiPerSatuan, 3, ',', '.'), '0'), ',') . ' ' . strtoupper($satuanHitung);
                $satuanHarga = $satuanHitung;
                } else {
                $perhitunganText = 'Normal';
                $perhitunganDetail = 'Per ' . strtoupper($satuan);
                $satuanHarga = $satuan;
                }
                @endphp

                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>

                    <td>{{ $item->kode_barang }}</td>

                    <td>
                        {{ $item->nama_barang }}

                        @if (!$item->status_aktif)
                        <br>
                        <span class="{{ $statusBarangClass }}">Nonaktif</span>
                        @endif
                    </td>

                    <td class="text-center">{{ strtoupper($satuan) }}</td>

                    <td>
                        {{ $perhitunganText }}
                        <br>
                        <span class="small-text">{{ $perhitunganDetail }}</span>
                    </td>

                    <td class="text-center">
                        <span class="{{ $kenaPpn ? 'status-ppn' : '' }}">
                            {{ $kenaPpn ? 'Kena' : 'Non' }}
                        </span>
                    </td>

                    <td class="text-center">{{ number_format($stokSaatIni, 0, ',', '.') }}</td>

                    <td class="text-center">
                        {{ rtrim(rtrim(number_format($jumlahSatuanHarga, 3, ',', '.'), '0'), ',') }}
                        <br>
                        <span class="small-text">{{ strtoupper($satuanHarga) }}</span>
                    </td>

                    <td class="text-right">Rp {{ number_format($hargaBeli, 0, ',', '.') }}</td>

                    <td class="text-right">Rp {{ number_format($nilaiStok, 0, ',', '.') }}</td>

                    <td class="text-right">
                        Rp {{ number_format($hargaJual, 0, ',', '.') }}
                        <br>
                        <span class="small-text">/ {{ strtoupper($satuanHarga) }}</span>
                    </td>

                    <td class="text-right">Rp {{ number_format($estimasiNilaiJual, 0, ',', '.') }}</td>

                    <td class="text-right">Rp {{ number_format($estimasiMargin, 0, ',', '.') }}</td>

                    <td class="text-center">
                        <span class="{{ $statusClass }}">{{ $statusStok }}</span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="14" class="text-center">
                        Data stok barang belum tersedia.
                    </td>
                </tr>
                @endforelse
        </tbody>
    </table>

    <div class="footer">
        Laporan ini dibuat otomatis oleh sistem Berkat Jaya Nusantara.
    </div>
</body>

</html>