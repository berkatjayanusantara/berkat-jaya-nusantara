<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Laporan Riwayat Stok</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #111827;
        }

        h2 {
            margin: 0;
            font-size: 18px;
        }

        .subtitle {
            margin-top: 4px;
            margin-bottom: 14px;
            color: #4b5563;
        }

        .summary {
            width: 100%;
            margin-bottom: 14px;
            border-collapse: collapse;
        }

        .summary td {
            border: 1px solid #d1d5db;
            padding: 6px;
        }

        .summary .label {
            background: #f3f4f6;
            font-weight: bold;
        }

        table.data {
            width: 100%;
            border-collapse: collapse;
        }

        table.data th {
            background: #f3f4f6;
            border: 1px solid #d1d5db;
            padding: 5px;
            text-align: left;
        }

        table.data td {
            border: 1px solid #d1d5db;
            padding: 5px;
            vertical-align: top;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }
    </style>
</head>

<body>
    <h2>Laporan Riwayat Stok</h2>
    <div class="subtitle">
        Periode: {{ $tanggalAwal }} s/d {{ $tanggalAkhir }}
    </div>

    <table class="summary">
        <tr>
            <td class="label">Total Data</td>
            <td>{{ number_format($totalData, 0, ',', '.') }}</td>
            <td class="label">Total Barang Masuk</td>
            <td>{{ number_format($totalMasuk, 0, ',', '.') }}</td>
            <td class="label">Total Barang Keluar</td>
            <td>{{ number_format($totalKeluar, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="label">Jumlah Penyesuaian</td>
            <td>{{ number_format($totalPenyesuaian, 0, ',', '.') }}</td>
            <td class="label">Jumlah Stock Opname</td>
            <td>{{ number_format($totalOpname, 0, ',', '.') }}</td>
            <td class="label">Selisih + / -</td>
            <td>
                +{{ number_format($totalSelisihPlus, 0, ',', '.') }}
                /
                -{{ number_format($totalSelisihMinus, 0, ',', '.') }}
            </td>
        </tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Kode</th>
                <th>Nama Barang</th>
                <th>Jenis</th>
                <th>Tipe</th>
                <th class="text-right">Jumlah</th>
                <th class="text-right">Sebelum</th>
                <th class="text-right">Sesudah</th>
                <th class="text-right">Selisih</th>
                <th>Sumber</th>
                <th>Keterangan</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($riwayatStok as $item)
            @php
            $selisih = (int) $item->stok_sesudah - (int) $item->stok_sebelum;
            $isOpname = str_starts_with((string) $item->sumber_transaksi, 'STOCK-OPNAME');

            if ($item->jenis_pergerakan === 'masuk') {
            $jenisLabel = 'Masuk';
            } elseif ($item->jenis_pergerakan === 'keluar') {
            $jenisLabel = 'Keluar';
            } else {
            $jenisLabel = 'Penyesuaian';
            }
            @endphp

            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $item->tanggal ? $item->tanggal->format('d-m-Y') : '-' }}</td>
                <td>{{ $item->barang->kode_barang ?? '-' }}</td>
                <td>{{ $item->barang->nama_barang ?? '-' }}</td>
                <td>{{ $jenisLabel }}</td>
                <td>{{ $isOpname ? 'Opname' : 'Transaksi' }}</td>
                <td class="text-right">{{ number_format($item->jumlah, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($item->stok_sebelum, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($item->stok_sesudah, 0, ',', '.') }}</td>
                <td class="text-right">{{ $selisih > 0 ? '+' : '' }}{{ number_format($selisih, 0, ',', '.') }}</td>
                <td>{{ $item->sumber_transaksi ?? '-' }}</td>
                <td>{{ $item->keterangan ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="12" class="text-center">
                    Data laporan riwayat stok belum tersedia.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>

</html>