@php
$namaPerusahaan = 'CV. BERKAT JAYA NUSANTARA';
$alamatPerusahaan = 'Jl. Jelambar Utama 1 No. 6A RT. 007 RW. 004, Jakarta Barat 11460';
$teleponPerusahaan = '(021) 5664892, 5676277';

$periodeAwal = $tanggalAwal === 'awal' ? 'Awal' : $tanggalAwal;
$periodeAkhir = $tanggalAkhir === 'akhir' ? 'Akhir' : $tanggalAkhir;

$formatIsi = function ($angka) {
return rtrim(rtrim(number_format((float) ($angka ?? 0), 3, ',', '.'), '0'), ',');
};

$normalisasiModePpn = function ($modePpn, $persentasePajak = 0, $pajakDitambahkan = false) {
if (in_array($modePpn, ['tanpa_ppn', 'include', 'exclude'], true)) {
return $modePpn;
}

if ((float) ($persentasePajak ?? 0) <= 0) {
    return 'tanpa_ppn' ;
    }

    return (bool) $pajakDitambahkan ? 'exclude' : 'include' ;
    };

    $labelModePpn=function ($modePpn) {
    return [ 'tanpa_ppn'=> 'Tanpa PPN',
    'include' => 'PPN Include / Harga Sudah Termasuk PPN',
    'exclude' => 'PPN Exclude / Harga Belum Termasuk PPN',
    ][$modePpn] ?? 'Tanpa PPN';
    };

    $labelStatusPembayaran = function ($status) {
    return [
    'lunas' => 'Lunas',
    'sebagian' => 'Sebagian',
    'belum_lunas' => 'Belum Lunas',
    ][$status] ?? 'Belum Lunas';
    };

    $labelPenyesuaian = function ($jenis, $nominal) {
    $nominal = (float) ($nominal ?? 0);
    if ($nominal <= 0 || !$jenis || $jenis==='tidak_ada' ) {
        return 'Tidak Ada' ;
        }

        return $jenis==='tambah' ? 'Tambah' : 'Kurang' ;
        };

        $nomor=1;
        $jumlahKolom=42;
        @endphp

        <!DOCTYPE html>
        <html>

        <head>
            <meta charset="UTF-8">
            <title>Laporan Penjualan</title>

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

                .header {
                    font-weight: bold;
                    background-color: #eeeeee;
                    text-align: center;
                }

                .section-header {
                    font-weight: bold;
                    background-color: #dbeafe;
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

                .historis {
                    background-color: #f3e8ff;
                }

                .sistem {
                    background-color: #f9fafb;
                }

                .isi-kemasan {
                    background-color: #f5f3ff;
                }
            </style>
        </head>

        <body>
            <table border="1">
                <tr>
                    <td colspan="{{ $jumlahKolom }}" class="company-title">
                        {{ $namaPerusahaan }}
                    </td>
                </tr>

                <tr>
                    <td colspan="{{ $jumlahKolom }}" class="text-center">
                        {{ $alamatPerusahaan }} | Telp: {{ $teleponPerusahaan }}
                    </td>
                </tr>

                <tr>
                    <td colspan="{{ $jumlahKolom }}" class="title">
                        LAPORAN PENJUALAN
                    </td>
                </tr>

                <tr>
                    <td colspan="{{ $jumlahKolom }}" class="subtitle">
                        Periode: {{ $periodeAwal }} s/d {{ $periodeAkhir }} | Dicetak: {{ now()->format('d-m-Y H:i') }}
                    </td>
                </tr>

                <tr>
                    <td colspan="{{ $jumlahKolom }}"></td>
                </tr>

                <tr class="section-header">
                    <td colspan="{{ $jumlahKolom }}">Ringkasan Laporan</td>
                </tr>

                <tr>
                    <td class="bold">Total Transaksi</td>
                    <td class="text-center">{{ $totalTransaksi ?? 0 }}</td>
                    <td class="bold">Sistem Berjalan</td>
                    <td class="text-center">{{ $totalSistemBerjalan ?? 0 }}</td>
                    <td class="bold">Historis</td>
                    <td class="text-center">{{ $totalHistoris ?? 0 }}</td>
                    <td class="bold">Mempengaruhi Stok</td>
                    <td class="text-center">{{ $totalMempengaruhiStok ?? 0 }}</td>
                    <td class="bold">Tidak Pengaruh Stok</td>
                    <td class="text-center">{{ $totalTidakMempengaruhiStok ?? 0 }}</td>
                    <td class="bold">Tunai</td>
                    <td class="text-right currency">{{ $totalTunai ?? 0 }}</td>
                    <td class="bold">Kredit</td>
                    <td class="text-right currency">{{ $totalKredit ?? 0 }}</td>
                    <td class="bold">Total Piutang</td>
                    <td class="text-right currency">{{ $totalPiutang ?? 0 }}</td>
                    <td class="bold">Total Dibayar</td>
                    <td class="text-right currency">{{ $totalDibayar ?? 0 }}</td>
                    <td class="bold">Sisa Piutang</td>
                    <td colspan="23" class="text-right currency">{{ $totalSisaPiutang ?? 0 }}</td>
                </tr>

                <tr>
                    <td class="bold">Total Subtotal</td>
                    <td colspan="3" class="text-right currency">{{ $totalSubtotal ?? 0 }}</td>
                    <td class="bold">Subtotal Kena PPN</td>
                    <td colspan="3" class="text-right currency">{{ $totalSubtotalKenaPpn ?? 0 }}</td>
                    <td class="bold">Subtotal Non PPN</td>
                    <td colspan="3" class="text-right currency">{{ $totalSubtotalNonPpn ?? 0 }}</td>
                    <td class="bold">DPP PPN</td>
                    <td colspan="3" class="text-right currency">{{ $totalDppPpn ?? 0 }}</td>
                    <td class="bold">Total PPN</td>
                    <td colspan="3" class="text-right currency">{{ $totalPajak ?? 0 }}</td>
                    <td class="bold">Total Sebelum Penyesuaian</td>
                    <td colspan="5" class="text-right currency">{{ $totalSebelumPenyesuaian ?? 0 }}</td>
                    <td class="bold">Total Akhir</td>
                    <td colspan="15" class="text-right currency">{{ $totalAkhir ?? 0 }}</td>
                </tr>

                <tr>
                    <td class="bold">Tanpa PPN</td>
                    <td class="text-center">{{ $totalPpnTanpaPpn ?? 0 }}</td>
                    <td class="bold">PPN Include</td>
                    <td class="text-center">{{ $totalPpnInclude ?? 0 }}</td>
                    <td class="bold">PPN Exclude</td>
                    <td class="text-center">{{ $totalPpnExclude ?? 0 }}</td>
                    <td class="bold">Nilai PPN Include</td>
                    <td colspan="3" class="text-right currency">{{ $totalNilaiPajakInclude ?? 0 }}</td>
                    <td class="bold">Nilai PPN Exclude</td>
                    <td colspan="3" class="text-right currency">{{ $totalNilaiPajakExclude ?? 0 }}</td>
                    <td class="bold">Butuh Faktur</td>
                    <td class="text-center">{{ $totalButuhFakturPajak ?? 0 }}</td>
                    <td class="bold">Tidak Butuh Faktur</td>
                    <td class="text-center">{{ $totalTidakButuhFakturPajak ?? 0 }}</td>
                    <td class="bold">Penyesuaian Tambah</td>
                    <td colspan="4" class="text-right currency">{{ $totalPenyesuaianTambah ?? 0 }}</td>
                    <td class="bold">Penyesuaian Kurang</td>
                    <td colspan="4" class="text-right currency">{{ $totalPenyesuaianKurang ?? 0 }}</td>
                    <td class="bold">Penyesuaian Bersih</td>
                    <td colspan="14" class="text-right currency">{{ $totalPenyesuaianBersih ?? 0 }}</td>
                </tr>

                <tr>
                    <td class="bold">Total Baris Barang</td>
                    <td class="text-center number-format">{{ $totalItemBarang ?? 0 }}</td>
                    <td class="bold">Total Qty Terjual</td>
                    <td class="text-center number-format">{{ $totalJumlahTerjual ?? 0 }}</td>
                    <td class="bold">Barang Normal</td>
                    <td class="text-center">{{ $totalBarangNormal ?? 0 }}</td>
                    <td class="bold">Nilai Normal</td>
                    <td colspan="3" class="text-right currency">{{ $totalNilaiBarangNormal ?? 0 }}</td>
                    <td class="bold">Barang Isi Kemasan</td>
                    <td class="text-center">{{ $totalBarangIsiKemasan ?? 0 }}</td>
                    <td class="bold">Nilai Isi Kemasan</td>
                    <td colspan="3" class="text-right currency">{{ $totalNilaiBarangIsiKemasan ?? 0 }}</td>
                    <td class="bold">Item Kena PPN</td>
                    <td class="text-center">{{ $totalItemKenaPpn ?? 0 }}</td>
                    <td class="bold">Item Non PPN</td>
                    <td class="text-center">{{ $totalItemNonPpn ?? 0 }}</td>
                    <td class="bold">DPP Detail</td>
                    <td colspan="4" class="text-right currency">{{ $totalDppPpnDetail ?? 0 }}</td>
                    <td class="bold">PPN Detail</td>
                    <td colspan="16" class="text-right currency">{{ $totalNilaiPpnDetail ?? 0 }}</td>
                </tr>

                <tr>
                    <td colspan="{{ $jumlahKolom }}"></td>
                </tr>

                <tr class="header">
                    <td>No</td>
                    <td>Tanggal</td>
                    <td>No Invoice Sistem</td>
                    <td>No Dokumen Asli</td>
                    <td>Customer</td>
                    <td>Nomor Telepon</td>
                    <td>NPWP Customer</td>
                    <td>Metode</td>
                    <td>Status Pembayaran</td>
                    <td>Tipe Invoice</td>
                    <td>Pengaruh Stok</td>
                    <td>Mode PPN</td>
                    <td>Barang Kena PPN</td>
                    <td>Butuh Faktur Pajak</td>
                    <td>No Faktur Pajak</td>
                    <td>Tanggal Faktur Pajak</td>
                    <td>Nama Faktur Pajak</td>
                    <td>NPWP Faktur Pajak</td>
                    <td>Kode Barang</td>
                    <td>Nama Barang</td>
                    <td>Tipe Harga</td>
                    <td>Jumlah</td>
                    <td>Satuan Transaksi</td>
                    <td>Isi Per Satuan</td>
                    <td>Satuan Hitung Harga</td>
                    <td>Harga Jual</td>
                    <td>Rumus Perhitungan</td>
                    <td>Subtotal Barang</td>
                    <td>DPP Barang</td>
                    <td>PPN Barang</td>
                    <td>Subtotal Invoice</td>
                    <td>Subtotal Kena PPN</td>
                    <td>Subtotal Non PPN</td>
                    <td>DPP Invoice</td>
                    <td>PPN Invoice</td>
                    <td>Total Sebelum Penyesuaian</td>
                    <td>Jenis Penyesuaian</td>
                    <td>Nominal Penyesuaian</td>
                    <td>Total Akhir</td>
                    <td>Total Piutang</td>
                    <td>Total Dibayar</td>
                    <td>Sisa Piutang</td>
                </tr>

                @foreach ($penjualan as $item)
                @php
                $nomorTelepon = $item->customer->nomor_telepon ?? '-';
                $npwp = $item->customer->npwp ?? '-';
                $isHistoris = (bool) ($item->is_historical ?? false);
                $nomorInvoiceTampil = $isHistoris && !empty($item->nomor_dokumen_asli)
                ? $item->nomor_dokumen_asli
                : $item->nomor_invoice;
                $affectStock = (bool) ($item->affect_stock ?? true);
                $modePpn = $normalisasiModePpn($item->mode_ppn ?? null, $item->persentase_pajak ?? 0, $item->pajak_ditambahkan ?? false);
                $statusPembayaran = $labelStatusPembayaran($item->status_pembayaran);
                $tipeInvoice = $isHistoris ? 'Historis / Lama' : 'Sistem Berjalan';
                $pengaruhStok = $affectStock ? 'Mempengaruhi Stok' : 'Tidak Mempengaruhi Stok';
                $butuhFaktur = (bool) ($item->butuh_faktur_pajak ?? false);
                $jenisPenyesuaian = $labelPenyesuaian($item->jenis_penyesuaian_total ?? 'tidak_ada', $item->nominal_penyesuaian_total ?? 0);
                $detailList = $item->detailPenjualan;
                @endphp

                @forelse ($detailList as $detail)
                @php
                $tipeHarga = $detail->tipe_perhitungan_harga ?? 'normal';
                $satuanTransaksi = $detail->satuan_transaksi ?? ($detail->barang->satuan ?? '-');
                $satuanHitungHarga = $detail->satuan_hitung_harga ?? $satuanTransaksi;
                $isiPerSatuan = (float) ($detail->isi_per_satuan ?? 1);
                $tipeHargaText = $tipeHarga === 'isi_kemasan' ? 'Isi Kemasan' : 'Normal';
                $kenaPpn = (bool) ($detail->kena_ppn ?? false);
                $rumusPerhitungan = $tipeHarga === 'isi_kemasan'
                ? $detail->jumlah . ' ' . $satuanTransaksi . ' x ' . $formatIsi($isiPerSatuan) . ' ' . $satuanHitungHarga . ' x Rp ' . number_format((float) $detail->harga_jual, 0, ',', '.')
                : $detail->jumlah . ' ' . $satuanTransaksi . ' x Rp ' . number_format((float) $detail->harga_jual, 0, ',', '.');
                @endphp

                <tr class="{{ $isHistoris ? 'historis' : ($tipeHarga === 'isi_kemasan' ? 'isi-kemasan' : 'sistem') }}">
                    <td class="text-center">{{ $nomor++ }}</td>
                    <td class="text-center">{{ $item->tanggal_penjualan ? $item->tanggal_penjualan->format('d-m-Y') : '-' }}</td>
                    <td class="text-format">{{ $nomorInvoiceTampil }}</td>
                    <td class="text-format">{{ $item->nomor_dokumen_asli ?? '-' }}</td>
                    <td>{{ $item->customer->nama_customer ?? '-' }}</td>
                    <td class="text-format">{{ $nomorTelepon !== '-' ? '&#8203;' . $nomorTelepon : '-' }}</td>
                    <td class="text-format">{{ $npwp !== '-' ? '&#8203;' . $npwp : '-' }}</td>
                    <td class="text-center">{{ ucfirst($item->metode_pembayaran ?? '-') }}</td>
                    <td class="text-center">{{ $statusPembayaran }}</td>
                    <td class="text-center">{{ $tipeInvoice }}</td>
                    <td class="text-center">{{ $pengaruhStok }}</td>
                    <td>{{ $labelModePpn($modePpn) }}</td>
                    <td class="text-center">{{ $kenaPpn ? 'Ya' : 'Tidak' }}</td>
                    <td class="text-center">{{ $butuhFaktur ? 'Ya' : 'Tidak' }}</td>
                    <td class="text-format">{{ $item->nomor_faktur_pajak ?? '-' }}</td>
                    <td class="text-center">{{ $item->tanggal_faktur_pajak ? $item->tanggal_faktur_pajak->format('d-m-Y') : '-' }}</td>
                    <td>{{ $item->nama_faktur_pajak ?? '-' }}</td>
                    <td class="text-format">{{ $item->npwp_faktur_pajak ?? '-' }}</td>
                    <td class="text-format">{{ $detail->barang->kode_barang ?? '-' }}</td>
                    <td>{{ $detail->barang->nama_barang ?? '-' }}</td>
                    <td class="text-center">{{ $tipeHargaText }}</td>
                    <td class="text-center number-format">{{ $detail->jumlah }}</td>
                    <td class="text-center">{{ $satuanTransaksi }}</td>
                    <td class="text-center number-format">{{ $tipeHarga === 'isi_kemasan' ? $isiPerSatuan : 1 }}</td>
                    <td class="text-center">{{ $satuanHitungHarga }}</td>
                    <td class="text-right currency">{{ $detail->harga_jual }}</td>
                    <td>{{ $rumusPerhitungan }}</td>
                    <td class="text-right currency">{{ $detail->subtotal }}</td>
                    <td class="text-right currency">{{ $detail->dpp_ppn ?? 0 }}</td>
                    <td class="text-right currency">{{ $detail->nilai_ppn ?? 0 }}</td>
                    <td class="text-right currency">{{ $item->subtotal }}</td>
                    <td class="text-right currency">{{ $item->subtotal_kena_ppn ?? 0 }}</td>
                    <td class="text-right currency">{{ $item->subtotal_non_ppn ?? 0 }}</td>
                    <td class="text-right currency">{{ $item->dpp_ppn ?? 0 }}</td>
                    <td class="text-right currency">{{ $item->nilai_pajak ?? 0 }}</td>
                    <td class="text-right currency">{{ $item->total_sebelum_penyesuaian ?? $item->total_akhir ?? 0 }}</td>
                    <td class="text-center">{{ $jenisPenyesuaian }}</td>
                    <td class="text-right currency">{{ $item->nominal_penyesuaian_total ?? 0 }}</td>
                    <td class="text-right currency">{{ $item->total_akhir }}</td>
                    <td class="text-right currency">{{ $item->piutang->total_piutang ?? 0 }}</td>
                    <td class="text-right currency">{{ $item->piutang->total_dibayar ?? 0 }}</td>
                    <td class="text-right currency">{{ $item->piutang->sisa_piutang ?? 0 }}</td>
                </tr>
                @empty
                <tr class="{{ $isHistoris ? 'historis' : 'sistem' }}">
                    <td class="text-center">{{ $nomor++ }}</td>
                    <td class="text-center">{{ $item->tanggal_penjualan ? $item->tanggal_penjualan->format('d-m-Y') : '-' }}</td>
                    <td class="text-format">{{ $nomorInvoiceTampil }}</td>
                    <td class="text-format">{{ $item->nomor_dokumen_asli ?? '-' }}</td>
                    <td>{{ $item->customer->nama_customer ?? '-' }}</td>
                    <td class="text-format">{{ $nomorTelepon }}</td>
                    <td class="text-format">{{ $npwp }}</td>
                    <td class="text-center">{{ ucfirst($item->metode_pembayaran ?? '-') }}</td>
                    <td class="text-center">{{ $statusPembayaran }}</td>
                    <td class="text-center">{{ $tipeInvoice }}</td>
                    <td class="text-center">{{ $pengaruhStok }}</td>
                    <td>{{ $labelModePpn($modePpn) }}</td>
                    <td colspan="18" class="text-center">Detail barang tidak tersedia</td>
                    <td class="text-right currency">{{ $item->subtotal }}</td>
                    <td class="text-right currency">{{ $item->subtotal_kena_ppn ?? 0 }}</td>
                    <td class="text-right currency">{{ $item->subtotal_non_ppn ?? 0 }}</td>
                    <td class="text-right currency">{{ $item->dpp_ppn ?? 0 }}</td>
                    <td class="text-right currency">{{ $item->nilai_pajak ?? 0 }}</td>
                    <td class="text-right currency">{{ $item->total_sebelum_penyesuaian ?? $item->total_akhir ?? 0 }}</td>
                    <td class="text-center">{{ $jenisPenyesuaian }}</td>
                    <td class="text-right currency">{{ $item->nominal_penyesuaian_total ?? 0 }}</td>
                    <td class="text-right currency">{{ $item->total_akhir }}</td>
                    <td class="text-right currency">{{ $item->piutang->total_piutang ?? 0 }}</td>
                    <td class="text-right currency">{{ $item->piutang->total_dibayar ?? 0 }}</td>
                    <td class="text-right currency">{{ $item->piutang->sisa_piutang ?? 0 }}</td>
                </tr>
                @endforelse
                @endforeach

                <tr>
                    <td colspan="{{ $jumlahKolom }}"></td>
                </tr>

                <tr class="total-row">
                    <td colspan="30" class="bold">TOTAL SUBTOTAL INVOICE</td>
                    <td colspan="12" class="text-right currency">{{ $totalSubtotal ?? 0 }}</td>
                </tr>

                <tr class="total-row">
                    <td colspan="30" class="bold">TOTAL DPP PPN</td>
                    <td colspan="12" class="text-right currency">{{ $totalDppPpn ?? 0 }}</td>
                </tr>

                <tr class="total-row">
                    <td colspan="30" class="bold">TOTAL PPN INVOICE</td>
                    <td colspan="12" class="text-right currency">{{ $totalPajak ?? 0 }}</td>
                </tr>

                <tr class="total-row">
                    <td colspan="30" class="bold">TOTAL PENYESUAIAN BERSIH</td>
                    <td colspan="12" class="text-right currency">{{ $totalPenyesuaianBersih ?? 0 }}</td>
                </tr>

                <tr class="total-row">
                    <td colspan="30" class="bold">TOTAL AKHIR INVOICE</td>
                    <td colspan="12" class="text-right currency">{{ $totalAkhir ?? 0 }}</td>
                </tr>

                <tr class="total-row">
                    <td colspan="30" class="bold">TOTAL SISA PIUTANG</td>
                    <td colspan="12" class="text-right currency">{{ $totalSisaPiutang ?? 0 }}</td>
                </tr>
            </table>
        </body>

        </html>