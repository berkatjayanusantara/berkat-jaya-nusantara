@php
$namaPerusahaan = 'CV. BERKAT JAYA NUSANTARA';
$alamatPerusahaan = 'Jl. Jelambar Utama 1 No. 6A RT. 007 RW. 004, Jakarta Barat 11460';
$teleponPerusahaan = '(021) 5664892, 5676277';

$periodeAwal = $tanggalAwal === 'awal' ? 'Awal' : $tanggalAwal;
$periodeAkhir = $tanggalAkhir === 'akhir' ? 'Akhir' : $tanggalAkhir;
$nettoPerubahan = $totalNettoPerubahan ?? (($totalSelisihPlus ?? 0) - ($totalSelisihMinus ?? 0));
@endphp

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Laporan Riwayat Stok</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 7.5px;
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

        .status-masuk {
            color: #047857;
            font-weight: bold;
        }

        .status-keluar {
            color: #b91c1c;
            font-weight: bold;
        }

        .status-penyesuaian {
            color: #92400e;
            font-weight: bold;
        }

        .status-opname {
            color: #1d4ed8;
            font-weight: bold;
        }

        .status-transaksi {
            color: #374151;
            font-weight: bold;
        }

        .selisih-plus {
            color: #047857;
            font-weight: bold;
        }

        .selisih-minus {
            color: #b91c1c;
            font-weight: bold;
        }

        .selisih-netral {
            color: #374151;
            font-weight: bold;
        }

        .small-text {
            font-size: 6.8px;
            color: #4b5563;
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

    <div class="title">LAPORAN RIWAYAT STOK</div>

    <div class="subtitle">
        Periode: {{ $periodeAwal }} s/d {{ $periodeAkhir }}
        |
        Dicetak: {{ now()->format('d-m-Y H:i') }}
    </div>

    <table class="summary-table">
        <tr>
            <td>
                <div class="summary-label">Total Data</div>
                <div class="summary-value">{{ number_format($totalData ?? 0, 0, ',', '.') }}</div>
                <div class="small-text">Barang unik: {{ number_format($totalBarangUnik ?? 0, 0, ',', '.') }}</div>
            </td>

            <td>
                <div class="summary-label">Total Barang Masuk</div>
                <div class="summary-value">+{{ number_format($totalMasuk ?? 0, 0, ',', '.') }}</div>
                <div class="small-text">{{ number_format($totalTransaksiMasuk ?? 0, 0, ',', '.') }} transaksi</div>
            </td>

            <td>
                <div class="summary-label">Total Barang Keluar</div>
                <div class="summary-value">-{{ number_format($totalKeluar ?? 0, 0, ',', '.') }}</div>
                <div class="small-text">{{ number_format($totalTransaksiKeluar ?? 0, 0, ',', '.') }} transaksi</div>
            </td>

            <td>
                <div class="summary-label">Netto Perubahan</div>
                <div class="summary-value">
                    {{ $nettoPerubahan > 0 ? '+' : '' }}{{ number_format($nettoPerubahan, 0, ',', '.') }}
                </div>
            </td>
        </tr>

        <tr>
            <td>
                <div class="summary-label">Jumlah Penyesuaian</div>
                <div class="summary-value">{{ number_format($totalPenyesuaian ?? 0, 0, ',', '.') }}</div>
                <div class="small-text">Qty: {{ number_format($totalJumlahPenyesuaian ?? 0, 0, ',', '.') }}</div>
            </td>

            <td>
                <div class="summary-label">Stock Opname</div>
                <div class="summary-value">{{ number_format($totalOpname ?? 0, 0, ',', '.') }}</div>
                <div class="small-text">Non-opname: {{ number_format($totalNonOpname ?? 0, 0, ',', '.') }}</div>
            </td>

            <td>
                <div class="summary-label">Selisih Bertambah</div>
                <div class="summary-value">+{{ number_format($totalSelisihPlus ?? 0, 0, ',', '.') }}</div>
            </td>

            <td>
                <div class="summary-label">Selisih Berkurang</div>
                <div class="summary-value">-{{ number_format($totalSelisihMinus ?? 0, 0, ',', '.') }}</div>
            </td>
        </tr>

        <tr>
            <td>
                <div class="summary-label">Tipe Harga Normal</div>
                <div class="summary-value">{{ number_format($totalBarangNormal ?? 0, 0, ',', '.') }}</div>
            </td>

            <td>
                <div class="summary-label">Tipe Isi Kemasan</div>
                <div class="summary-value">{{ number_format($totalBarangIsiKemasan ?? 0, 0, ',', '.') }}</div>
            </td>

            <td>
                <div class="summary-label">Barang Kena PPN</div>
                <div class="summary-value">{{ number_format($totalBarangKenaPpn ?? 0, 0, ',', '.') }}</div>
            </td>

            <td>
                <div class="summary-label">Barang Non PPN</div>
                <div class="summary-value">{{ number_format($totalBarangNonPpn ?? 0, 0, ',', '.') }}</div>
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 3%;">No</th>
                <th style="width: 7%;">Tanggal</th>
                <th style="width: 8%;">Kode</th>
                <th style="width: 14%;">Barang</th>
                <th style="width: 7%;">Jenis</th>
                <th style="width: 6%;">Tipe</th>
                <th style="width: 6%;">Jumlah</th>
                <th style="width: 7%;">Sebelum</th>
                <th style="width: 7%;">Sesudah</th>
                <th style="width: 7%;">Selisih</th>
                <th style="width: 10%;">Hitung</th>
                <th style="width: 10%;">Sumber</th>
                <th style="width: 8%;">Keterangan</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($riwayatStok as $item)
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
            $kenaPpn = (bool) ($barangItem->kena_ppn ?? true);

            if ($item->jenis_pergerakan === 'masuk') {
            $jenisLabel = 'Masuk';
            $jenisClass = 'status-masuk';
            } elseif ($item->jenis_pergerakan === 'keluar') {
            $jenisLabel = 'Keluar';
            $jenisClass = 'status-keluar';
            } else {
            $jenisLabel = 'Penyesuaian';
            $jenisClass = 'status-penyesuaian';
            }

            if ($selisih > 0) {
            $selisihText = '+' . number_format($selisih, 0, ',', '.');
            $selisihClass = 'selisih-plus';
            } elseif ($selisih < 0) {
                $selisihText=number_format($selisih, 0, ',' , '.' );
                $selisihClass='selisih-minus' ;
                } else {
                $selisihText='0' ;
                $selisihClass='selisih-netral' ;
                }
                @endphp

                <tr>
                <td class="text-center">{{ $loop->iteration }}</td>

                <td class="text-center">
                    {{ $item->tanggal ? $item->tanggal->format('d-m-Y') : '-' }}
                    <br>
                    <span class="small-text">{{ $item->created_at ? $item->created_at->format('H:i') : '-' }}</span>
                </td>

                <td>{{ $barangItem->kode_barang ?? '-' }}</td>

                <td>
                    {{ $barangItem->nama_barang ?? '-' }}
                    <br>
                    <span class="small-text">
                        {{ strtoupper($satuan) }} | {{ $kenaPpn ? 'Kena PPN' : 'Non PPN' }}
                    </span>
                </td>

                <td class="text-center">
                    <span class="{{ $jenisClass }}">{{ $jenisLabel }}</span>
                </td>

                <td class="text-center">
                    @if ($isOpname)
                    <span class="status-opname">Opname</span>
                    @else
                    <span class="status-transaksi">Transaksi</span>
                    @endif
                </td>

                <td class="text-right">{{ number_format($item->jumlah ?? 0, 0, ',', '.') }}</td>

                <td class="text-right">{{ number_format($stokSebelum, 0, ',', '.') }}</td>

                <td class="text-right">{{ number_format($stokSesudah, 0, ',', '.') }}</td>

                <td class="text-right">
                    <span class="{{ $selisihClass }}">{{ $selisihText }}</span>
                </td>

                <td>
                    @if ($tipePerhitungan === 'isi_kemasan')
                    Isi Kemasan
                    <br>
                    <span class="small-text">
                        1 {{ strtoupper($satuan) }} =
                        {{ rtrim(rtrim(number_format($isiPerSatuan, 3, ',', '.'), '0'), ',') }}
                        {{ strtoupper($satuanHitung) }}
                    </span>
                    @else
                    Normal
                    <br>
                    <span class="small-text">Per {{ strtoupper($satuan) }}</span>
                    @endif
                </td>

                <td>{{ $item->sumber_transaksi ?? '-' }}</td>

                <td>{{ $item->keterangan ?? '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="13" class="text-center">
                        Data laporan riwayat stok belum tersedia.
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