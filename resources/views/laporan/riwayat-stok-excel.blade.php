<table border="1">
    <thead>
        <tr>
            <th colspan="13" style="font-weight: bold; font-size: 16px;">
                Laporan Riwayat Stok
            </th>
        </tr>
        <tr>
            <th colspan="13">
                Periode: {{ $tanggalAwal }} s/d {{ $tanggalAkhir }}
            </th>
        </tr>
        <tr>
            <th colspan="13"></th>
        </tr>
        <tr>
            <th>Total Data</th>
            <th>Total Masuk</th>
            <th>Total Keluar</th>
            <th>Jumlah Penyesuaian</th>
            <th>Jumlah Stock Opname</th>
            <th>Total Selisih Bertambah</th>
            <th>Total Selisih Berkurang</th>
            <th colspan="6"></th>
        </tr>
        <tr>
            <td>{{ $totalData }}</td>
            <td>{{ $totalMasuk }}</td>
            <td>{{ $totalKeluar }}</td>
            <td>{{ $totalPenyesuaian }}</td>
            <td>{{ $totalOpname }}</td>
            <td>{{ $totalSelisihPlus }}</td>
            <td>{{ $totalSelisihMinus }}</td>
            <td colspan="6"></td>
        </tr>
        <tr>
            <th>No</th>
            <th>Tanggal</th>
            <th>Kode Barang</th>
            <th>Nama Barang</th>
            <th>Jenis</th>
            <th>Tipe</th>
            <th>Jumlah</th>
            <th>Stok Sebelum</th>
            <th>Stok Sesudah</th>
            <th>Selisih</th>
            <th>Sumber</th>
            <th>Keterangan</th>
            <th>Dibuat Oleh</th>
        </tr>
    </thead>

    <tbody>
        @foreach ($riwayatStok as $item)
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
            <td>{{ $item->jumlah }}</td>
            <td>{{ $item->stok_sebelum }}</td>
            <td>{{ $item->stok_sesudah }}</td>
            <td>{{ $selisih }}</td>
            <td>{{ $item->sumber_transaksi ?? '-' }}</td>
            <td>{{ $item->keterangan ?? '-' }}</td>
            <td>{{ $item->user->nama_user ?? '-' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>