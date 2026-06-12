@php
$pajakDitambahkan = $penjualan->pajak_ditambahkan ?? true;
$nomorTelepon = $penjualan->customer->nomor_telepon ?? '-';
@endphp

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $penjualan->nomor_invoice }}</title>

    <style>
        .title {
            font-size: 18px;
            font-weight: bold;
            text-align: center;
        }

        .subtitle {
            text-align: center;
        }

        .section-header {
            font-weight: bold;
            background-color: #eeeeee;
        }

        .header {
            font-weight: bold;
            background-color: #eeeeee;
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
    </style>
</head>

<body>
    <table border="1">
        <tr>
            <td colspan="6" class="title">
                INVOICE PENJUALAN
            </td>
        </tr>

        <tr>
            <td colspan="6" class="subtitle">
                {{ $penjualan->nomor_invoice }}
            </td>
        </tr>

        <tr>
            <td colspan="6"></td>
        </tr>

        <tr class="section-header">
            <td colspan="2">Informasi Customer</td>
            <td colspan="4">Informasi Penjualan</td>
        </tr>

        <tr>
            <td class="bold">Nama Customer</td>
            <td>{{ $penjualan->customer->nama_customer ?? '-' }}</td>

            <td class="bold">Tanggal</td>
            <td colspan="3">
                {{ $penjualan->tanggal_penjualan ? $penjualan->tanggal_penjualan->format('d-m-Y') : '-' }}
            </td>
        </tr>

        <tr>
            <td class="bold">Nomor Telepon</td>
            <td>
                @if ($nomorTelepon !== '-')
                &#8203;{{ $nomorTelepon }}
                @else
                -
                @endif
            </td>

            <td class="bold">Metode Pembayaran</td>
            <td colspan="3">{{ ucfirst($penjualan->metode_pembayaran) }}</td>
        </tr>

        <tr>
            <td class="bold">Alamat</td>
            <td>{{ $penjualan->customer->alamat ?? '-' }}</td>

            <td class="bold">Status Pembayaran</td>
            <td colspan="3">{{ str_replace('_', ' ', ucfirst($penjualan->status_pembayaran)) }}</td>
        </tr>

        <tr>
            <td class="bold">Kategori Customer</td>
            <td>{{ $penjualan->customer->kategori_customer ?? '-' }}</td>

            <td class="bold">Jatuh Tempo</td>
            <td colspan="3">
                {{ $penjualan->tanggal_jatuh_tempo ? $penjualan->tanggal_jatuh_tempo->format('d-m-Y') : '-' }}
            </td>
        </tr>

        <tr>
            <td></td>
            <td></td>

            <td class="bold">Admin</td>
            <td colspan="3">{{ $penjualan->user->nama_user ?? '-' }}</td>
        </tr>

        <tr>
            <td colspan="6"></td>
        </tr>

        <tr class="section-header">
            <td colspan="6">Daftar Barang</td>
        </tr>

        <tr class="header">
            <td>No</td>
            <td>Kode Barang</td>
            <td>Nama Barang</td>
            <td>Qty</td>
            <td>Perhitungan</td>
            <td>Harga Jual</td>
            <td>Subtotal</td>
        </tr>

        @foreach ($penjualan->detailPenjualan as $detail)
        @php
        $tipePerhitungan = $detail->tipe_perhitungan_harga ?? 'normal';
        $satuanTransaksi = $detail->satuan_transaksi ?? ($detail->barang->satuan ?? '');
        $satuanHitung = $detail->satuan_hitung_harga ?? $satuanTransaksi;
        $isiPerSatuan = (float) ($detail->isi_per_satuan ?? 1);
        @endphp

        <tr>
            <td class="text-center">
                {{ $loop->iteration }}
            </td>

            <td>
                {{ $detail->barang->kode_barang ?? '-' }}
            </td>

            <td>
                {{ $detail->barang->nama_barang ?? '-' }}
            </td>

            <td class="text-center">
                {{ $detail->jumlah }} {{ $satuanTransaksi }}
            </td>

            <td>
                @if ($tipePerhitungan === 'isi_kemasan')
                {{ $detail->jumlah }} {{ $satuanTransaksi }}
                x {{ rtrim(rtrim(number_format($isiPerSatuan, 3, ',', '.'), '0'), ',') }} {{ $satuanHitung }}
                x Rp {{ number_format($detail->harga_jual, 0, ',', '.') }}
                @else
                {{ $detail->jumlah }} {{ $satuanTransaksi }}
                x Rp {{ number_format($detail->harga_jual, 0, ',', '.') }}
                @endif
            </td>

            <td class="text-right">
                Rp {{ number_format($detail->harga_jual, 0, ',', '.') }}
                / {{ $tipePerhitungan === 'isi_kemasan' ? $satuanHitung : $satuanTransaksi }}
            </td>

            <td class="text-right">
                Rp {{ number_format($detail->subtotal, 0, ',', '.') }}
            </td>
        </tr>
        @endforeach

        <tr>
            <td colspan="6"></td>
        </tr>

        <tr>
            <td colspan="4"></td>
            <td class="bold">Subtotal</td>
            <td class="text-right">
                Rp {{ number_format($penjualan->subtotal, 0, ',', '.') }}
            </td>
        </tr>

        <tr>
            <td colspan="4"></td>
            <td class="bold">
                Pajak {{ number_format($penjualan->persentase_pajak, 2, ',', '.') }}%
            </td>
            <td class="text-right">
                Rp {{ number_format($penjualan->nilai_pajak, 0, ',', '.') }}
            </td>
        </tr>

        @if (!$pajakDitambahkan)
        <tr>
            <td colspan="4"></td>
            <td colspan="2">
                Pajak hanya ditampilkan dan tidak ditambahkan ke total akhir.
            </td>
        </tr>
        @endif

        <tr>
            <td colspan="4"></td>
            <td class="bold">Total Akhir</td>
            <td class="bold text-right">
                Rp {{ number_format($penjualan->total_akhir, 0, ',', '.') }}
            </td>
        </tr>

        @if ($penjualan->piutang)
        <tr>
            <td colspan="6"></td>
        </tr>

        <tr class="section-header">
            <td colspan="6">Informasi Piutang</td>
        </tr>

        <tr>
            <td class="bold">Total Piutang</td>
            <td class="text-right">
                Rp {{ number_format($penjualan->piutang->total_piutang, 0, ',', '.') }}
            </td>

            <td class="bold">Total Dibayar</td>
            <td class="text-right">
                Rp {{ number_format($penjualan->piutang->total_dibayar, 0, ',', '.') }}
            </td>

            <td class="bold">Sisa Piutang</td>
            <td class="text-right">
                Rp {{ number_format($penjualan->piutang->sisa_piutang, 0, ',', '.') }}
            </td>
        </tr>

        <tr>
            <td class="bold">Status Piutang</td>
            <td colspan="5">
                {{ str_replace('_', ' ', ucfirst($penjualan->piutang->status_piutang)) }}
            </td>
        </tr>
        @endif

        <tr>
            <td colspan="6"></td>
        </tr>

        <tr>
            <td class="bold">Catatan</td>
            <td colspan="5">{{ $penjualan->catatan ?? '-' }}</td>
        </tr>
    </table>
</body>

</html>