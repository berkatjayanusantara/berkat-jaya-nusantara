<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\DetailPembelian;
use App\Models\Pembelian;
use App\Models\RiwayatStok;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class PembelianController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;

        $pembelian = Pembelian::with(['supplier', 'user'])
            ->when($search, function ($query, $search) {
                $query->where('nomor_pembelian', 'like', "%{$search}%")
                    ->orWhere('nomor_delivery_order', 'like', "%{$search}%")
                    ->orWhere('nomor_surat_jalan', 'like', "%{$search}%")
                    ->orWhereHas('supplier', function ($supplierQuery) use ($search) {
                        $supplierQuery->where('nama_supplier', 'like', "%{$search}%");
                    });
            })
            ->orderBy('tanggal_pembelian', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('pembelian.index', compact('pembelian', 'search'));
    }

    public function create()
    {
        $nomorPembelian = $this->generateNomorPembelian();
        $nomorDeliveryOrder = $this->generateNomorDeliveryOrder();
        $nomorSuratJalan = $this->generateNomorSuratJalan();

        $suppliers = Supplier::where('status_aktif', true)
            ->orderBy('nama_supplier')
            ->get();

        $barang = Barang::where('status_aktif', true)
            ->orderBy('nama_barang')
            ->get();

        return view('pembelian.create', compact(
            'nomorPembelian',
            'nomorDeliveryOrder',
            'nomorSuratJalan',
            'suppliers',
            'barang'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nomor_pembelian' => [
                'required',
                'string',
                'max:100',
                Rule::unique('pembelian', 'nomor_pembelian'),
            ],
            'nomor_delivery_order' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('pembelian', 'nomor_delivery_order'),
            ],
            'nomor_surat_jalan' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('pembelian', 'nomor_surat_jalan'),
            ],
            'tanggal_pembelian' => 'required|date',
            'id_supplier' => 'required|exists:suppliers,id_supplier',

            // Pajak pembelian dibuat manual sesuai dokumen/nota supplier.
            'nilai_pajak' => 'nullable|numeric|min:0',
            'biaya_lain' => 'nullable|numeric|min:0',
            'potongan_diskon' => 'nullable|numeric|min:0',
            'keterangan_penyesuaian_total' => 'nullable|string',

            'catatan' => 'nullable|string',

            'id_barang' => 'required|array|min:1',
            'id_barang.*' => 'required|exists:barang,id_barang',

            'jumlah_dipesan' => 'required|array|min:1',
            'jumlah_dipesan.*' => 'required|integer|min:1',

            'jumlah' => 'required|array|min:1',
            'jumlah.*' => 'required|integer|min:0',

            'harga_beli' => 'required|array|min:1',
            'harga_beli.*' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $subtotalPembelian = 0;
            $totalDipesan = 0;
            $totalDiterima = 0;

            foreach ($request->id_barang as $index => $idBarang) {
                $jumlahDipesan = (int) $request->jumlah_dipesan[$index];
                $jumlahDiterima = (int) $request->jumlah[$index];
                $hargaBeli = (float) $request->harga_beli[$index];

                if ($jumlahDiterima > $jumlahDipesan) {
                    throw ValidationException::withMessages([
                        'jumlah' => 'Jumlah diterima tidak boleh lebih besar dari jumlah dipesan.',
                    ]);
                }

                $totalDipesan += $jumlahDipesan;
                $totalDiterima += $jumlahDiterima;
                $subtotalPembelian += $jumlahDiterima * $hargaBeli;
            }

            if ($totalDiterima <= 0) {
                throw ValidationException::withMessages([
                    'jumlah' => 'Minimal harus ada barang yang diterima.',
                ]);
            }

            $statusPenerimaan = $totalDiterima < $totalDipesan
                ? 'sebagian'
                : 'lengkap';

            $nilaiPajak = (float) ($request->nilai_pajak ?? 0);
            $biayaLain = (float) ($request->biaya_lain ?? 0);
            $potonganDiskon = (float) ($request->potongan_diskon ?? 0);

            $totalSebelumPotongan = $subtotalPembelian + $nilaiPajak + $biayaLain;

            if ($potonganDiskon > $totalSebelumPotongan) {
                throw ValidationException::withMessages([
                    'potongan_diskon' => 'Potongan/diskon tidak boleh lebih besar dari subtotal + PPN supplier + biaya lain.',
                ]);
            }

            $totalAkhir = $totalSebelumPotongan - $potonganDiskon;

            // Persentase pajak tetap diisi untuk kompatibilitas laporan lama,
            // tetapi sumber utama pajak pembelian sekarang adalah nominal nilai_pajak.
            $persentasePajak = $subtotalPembelian > 0
                ? round(($nilaiPajak / $subtotalPembelian) * 100, 2)
                : 0;

            $pembelian = Pembelian::create([
                'nomor_pembelian' => trim($request->nomor_pembelian),
                'nomor_delivery_order' => $this->ubahKosongMenjadiNull($request->nomor_delivery_order),
                'nomor_surat_jalan' => $this->ubahKosongMenjadiNull($request->nomor_surat_jalan),
                'tanggal_pembelian' => $request->tanggal_pembelian,
                'id_supplier' => $request->id_supplier,
                'status_penerimaan' => $statusPenerimaan,
                'subtotal' => $subtotalPembelian,

                'persentase_pajak' => $persentasePajak,
                'nilai_pajak' => $nilaiPajak,
                'pajak_ditambahkan' => true,

                'biaya_lain' => $biayaLain,
                'potongan_diskon' => $potonganDiskon,
                'keterangan_penyesuaian_total' => $request->keterangan_penyesuaian_total,

                'total_akhir' => $totalAkhir,
                'catatan' => $request->catatan,
                'dibuat_oleh' => Auth::id(),
            ]);

            foreach ($request->id_barang as $index => $idBarang) {
                $barang = Barang::where('id_barang', $idBarang)
                    ->lockForUpdate()
                    ->firstOrFail();

                $jumlahDipesan = (int) $request->jumlah_dipesan[$index];
                $jumlahDiterima = (int) $request->jumlah[$index];
                $hargaBeli = (float) $request->harga_beli[$index];
                $subtotalDetail = $jumlahDiterima * $hargaBeli;

                DetailPembelian::create([
                    'id_pembelian' => $pembelian->id_pembelian,
                    'id_barang' => $barang->id_barang,
                    'jumlah_dipesan' => $jumlahDipesan,
                    'jumlah' => $jumlahDiterima,
                    'harga_beli' => $hargaBeli,
                    'subtotal' => $subtotalDetail,
                ]);

                if ($jumlahDiterima > 0) {
                    $stokSebelum = $barang->stok_saat_ini;
                    $stokSesudah = $stokSebelum + $jumlahDiterima;

                    $barang->update([
                        'stok_saat_ini' => $stokSesudah,
                        'harga_beli_terakhir' => $hargaBeli,
                    ]);

                    RiwayatStok::create([
                        'id_barang' => $barang->id_barang,
                        'tanggal' => $request->tanggal_pembelian,
                        'jenis_pergerakan' => 'masuk',
                        'jumlah' => $jumlahDiterima,
                        'stok_sebelum' => $stokSebelum,
                        'stok_sesudah' => $stokSesudah,
                        'sumber_transaksi' => $pembelian->nomor_pembelian,
                        'keterangan' => 'Stok masuk dari pembelian. Dipesan: ' . $jumlahDipesan . ', diterima: ' . $jumlahDiterima,
                        'dibuat_oleh' => Auth::id(),
                        'created_at' => now(),
                    ]);
                }
            }
        });

        return redirect()
            ->route('pembelian.index')
            ->with('success', 'Transaksi pembelian berhasil disimpan dan stok barang berhasil diperbarui sesuai jumlah diterima.');
    }

    public function edit(Pembelian $pembelian)
    {
        $pembelian->load([
            'supplier',
            'detailPembelian.barang',
        ]);

        $suppliers = Supplier::where('status_aktif', true)
            ->orWhere('id_supplier', $pembelian->id_supplier)
            ->orderBy('nama_supplier')
            ->get();

        $barang = Barang::where('status_aktif', true)
            ->orWhereIn('id_barang', $pembelian->detailPembelian->pluck('id_barang'))
            ->orderBy('nama_barang')
            ->get();

        return view('pembelian.edit', compact(
            'pembelian',
            'suppliers',
            'barang'
        ));
    }

    public function update(Request $request, Pembelian $pembelian)
    {
        $request->validate([
            'nomor_pembelian' => [
                'required',
                'string',
                'max:100',
                Rule::unique('pembelian', 'nomor_pembelian')
                    ->ignore($pembelian->id_pembelian, 'id_pembelian'),
            ],
            'nomor_delivery_order' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('pembelian', 'nomor_delivery_order')
                    ->ignore($pembelian->id_pembelian, 'id_pembelian'),
            ],
            'nomor_surat_jalan' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('pembelian', 'nomor_surat_jalan')
                    ->ignore($pembelian->id_pembelian, 'id_pembelian'),
            ],
            'tanggal_pembelian' => 'required|date',
            'id_supplier' => 'required|exists:suppliers,id_supplier',

            // Pajak pembelian dibuat manual sesuai dokumen/nota supplier.
            'nilai_pajak' => 'nullable|numeric|min:0',
            'biaya_lain' => 'nullable|numeric|min:0',
            'potongan_diskon' => 'nullable|numeric|min:0',
            'keterangan_penyesuaian_total' => 'nullable|string',

            'catatan' => 'nullable|string',

            'id_barang' => 'required|array|min:1',
            'id_barang.*' => 'required|exists:barang,id_barang',

            'jumlah_dipesan' => 'required|array|min:1',
            'jumlah_dipesan.*' => 'required|integer|min:1',

            'jumlah' => 'required|array|min:1',
            'jumlah.*' => 'required|integer|min:0',

            'harga_beli' => 'required|array|min:1',
            'harga_beli.*' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $pembelian) {
            $pembelian->load('detailPembelian');

            $subtotalPembelian = 0;
            $totalDipesan = 0;
            $totalDiterima = 0;

            foreach ($request->id_barang as $index => $idBarang) {
                $jumlahDipesan = (int) $request->jumlah_dipesan[$index];
                $jumlahDiterima = (int) $request->jumlah[$index];
                $hargaBeli = (float) $request->harga_beli[$index];

                if ($jumlahDiterima > $jumlahDipesan) {
                    throw ValidationException::withMessages([
                        'jumlah' => 'Jumlah diterima tidak boleh lebih besar dari jumlah dipesan.',
                    ]);
                }

                $totalDipesan += $jumlahDipesan;
                $totalDiterima += $jumlahDiterima;
                $subtotalPembelian += $jumlahDiterima * $hargaBeli;
            }

            if ($totalDiterima <= 0) {
                throw ValidationException::withMessages([
                    'jumlah' => 'Minimal harus ada barang yang diterima.',
                ]);
            }

            $affectStock = $pembelian->affect_stock ?? true;

            if ($affectStock) {
                foreach ($pembelian->detailPembelian as $detailLama) {
                    $jumlahLama = (int) $detailLama->jumlah;

                    if ($jumlahLama <= 0) {
                        continue;
                    }

                    $barangLama = Barang::where('id_barang', $detailLama->id_barang)
                        ->lockForUpdate()
                        ->first();

                    if (!$barangLama) {
                        continue;
                    }

                    if ($barangLama->stok_saat_ini < $jumlahLama) {
                        throw ValidationException::withMessages([
                            'stok' => 'Pembelian tidak bisa diedit karena stok barang "' . $barangLama->nama_barang . '" sudah berkurang/dipakai. Stok tersedia: ' . $barangLama->stok_saat_ini . ', stok lama yang harus dikembalikan: ' . $jumlahLama . '.',
                        ]);
                    }

                    $stokSebelum = $barangLama->stok_saat_ini;
                    $stokSesudah = $stokSebelum - $jumlahLama;

                    $barangLama->update([
                        'stok_saat_ini' => $stokSesudah,
                    ]);

                    RiwayatStok::create([
                        'id_barang' => $barangLama->id_barang,
                        'tanggal' => $request->tanggal_pembelian,
                        'jenis_pergerakan' => 'penyesuaian',
                        'jumlah' => $jumlahLama,
                        'stok_sebelum' => $stokSebelum,
                        'stok_sesudah' => $stokSesudah,
                        'sumber_transaksi' => $pembelian->nomor_pembelian,
                        'keterangan' => 'Pengurangan stok karena edit pembelian lama',
                        'dibuat_oleh' => Auth::id(),
                        'created_at' => now(),
                    ]);
                }
            }

            $statusPenerimaan = $totalDiterima < $totalDipesan
                ? 'sebagian'
                : 'lengkap';

            $nilaiPajak = (float) ($request->nilai_pajak ?? 0);
            $biayaLain = (float) ($request->biaya_lain ?? 0);
            $potonganDiskon = (float) ($request->potongan_diskon ?? 0);

            $totalSebelumPotongan = $subtotalPembelian + $nilaiPajak + $biayaLain;

            if ($potonganDiskon > $totalSebelumPotongan) {
                throw ValidationException::withMessages([
                    'potongan_diskon' => 'Potongan/diskon tidak boleh lebih besar dari subtotal + PPN supplier + biaya lain.',
                ]);
            }

            $totalAkhir = $totalSebelumPotongan - $potonganDiskon;

            // Persentase pajak tetap diisi untuk kompatibilitas laporan lama,
            // tetapi sumber utama pajak pembelian sekarang adalah nominal nilai_pajak.
            $persentasePajak = $subtotalPembelian > 0
                ? round(($nilaiPajak / $subtotalPembelian) * 100, 2)
                : 0;

            $nomorPembelianLama = $pembelian->nomor_pembelian;

            $pembelian->update([
                'nomor_pembelian' => trim($request->nomor_pembelian),
                'nomor_delivery_order' => $this->ubahKosongMenjadiNull($request->nomor_delivery_order),
                'nomor_surat_jalan' => $this->ubahKosongMenjadiNull($request->nomor_surat_jalan),
                'tanggal_pembelian' => $request->tanggal_pembelian,
                'id_supplier' => $request->id_supplier,
                'status_penerimaan' => $statusPenerimaan,
                'subtotal' => $subtotalPembelian,

                'persentase_pajak' => $persentasePajak,
                'nilai_pajak' => $nilaiPajak,
                'pajak_ditambahkan' => true,

                'biaya_lain' => $biayaLain,
                'potongan_diskon' => $potonganDiskon,
                'keterangan_penyesuaian_total' => $request->keterangan_penyesuaian_total,

                'total_akhir' => $totalAkhir,
                'catatan' => $request->catatan,
            ]);

            $pembelian->detailPembelian()->delete();

            foreach ($request->id_barang as $index => $idBarang) {
                $barang = Barang::where('id_barang', $idBarang)
                    ->lockForUpdate()
                    ->firstOrFail();

                $jumlahDipesan = (int) $request->jumlah_dipesan[$index];
                $jumlahDiterima = (int) $request->jumlah[$index];
                $hargaBeli = (float) $request->harga_beli[$index];
                $subtotalDetail = $jumlahDiterima * $hargaBeli;

                DetailPembelian::create([
                    'id_pembelian' => $pembelian->id_pembelian,
                    'id_barang' => $barang->id_barang,
                    'jumlah_dipesan' => $jumlahDipesan,
                    'jumlah' => $jumlahDiterima,
                    'harga_beli' => $hargaBeli,
                    'subtotal' => $subtotalDetail,
                ]);

                if ($affectStock && $jumlahDiterima > 0) {
                    $stokSebelum = $barang->stok_saat_ini;
                    $stokSesudah = $stokSebelum + $jumlahDiterima;

                    $barang->update([
                        'stok_saat_ini' => $stokSesudah,
                        'harga_beli_terakhir' => $hargaBeli,
                    ]);

                    RiwayatStok::create([
                        'id_barang' => $barang->id_barang,
                        'tanggal' => $request->tanggal_pembelian,
                        'jenis_pergerakan' => 'masuk',
                        'jumlah' => $jumlahDiterima,
                        'stok_sebelum' => $stokSebelum,
                        'stok_sesudah' => $stokSesudah,
                        'sumber_transaksi' => $pembelian->nomor_pembelian,
                        'keterangan' => 'Stok masuk dari edit pembelian. Nomor lama: ' . $nomorPembelianLama . '. Dipesan: ' . $jumlahDipesan . ', diterima: ' . $jumlahDiterima,
                        'dibuat_oleh' => Auth::id(),
                        'created_at' => now(),
                    ]);
                }
            }
        });

        return redirect()
            ->route('pembelian.show', $pembelian->id_pembelian)
            ->with('success', 'Transaksi pembelian berhasil diperbarui dan stok barang sudah disesuaikan.');
    }

    public function show(Pembelian $pembelian)
    {
        $pembelian->load([
            'supplier',
            'user',
            'detailPembelian.barang',
        ]);

        return view('pembelian.show', compact('pembelian'));
    }

    public function deliveryOrder(Pembelian $pembelian)
    {
        $pembelian->load([
            'supplier',
            'user',
            'detailPembelian.barang',
        ]);

        return view('pembelian.delivery-order', compact('pembelian'));
    }

    public function suratJalan(Pembelian $pembelian)
    {
        $pembelian->load([
            'supplier',
            'user',
            'detailPembelian.barang',
        ]);

        return view('pembelian.surat-jalan', compact('pembelian'));
    }

    public function exportExcel(Pembelian $pembelian)
    {
        $pembelian->load([
            'supplier',
            'user',
            'detailPembelian.barang',
        ]);

        $statusPenerimaan = $pembelian->status_penerimaan ?? 'lengkap';

        $namaPerusahaan = 'CV. BERKAT JAYA NUSANTARA';
        $alamatPerusahaan = 'Jl. Jelambar Utama 1 No. 6A RT. 007 RW. 004, Jakarta Barat 11460';
        $teleponPerusahaan = '(021) 5664892, 5676277';

        $statusText = match ($statusPenerimaan) {
            'lengkap' => 'Lengkap',
            'sebagian' => 'Sebagian',
            default => 'Belum Dikirim',
        };

        $nilaiPajak = (float) ($pembelian->nilai_pajak ?? 0);
        $biayaLain = (float) ($pembelian->biaya_lain ?? 0);
        $potonganDiskon = (float) ($pembelian->potongan_diskon ?? 0);
        $subtotalPembelian = (float) ($pembelian->subtotal ?? 0);
        $totalSebelumPotongan = $subtotalPembelian + $nilaiPajak + $biayaLain;
        $keteranganPenyesuaian = $pembelian->keterangan_penyesuaian_total ?? null;

        $modePajak = 'PPN supplier diinput manual sesuai nominal pada invoice/faktur supplier';

        $formatAngka = function ($angka) {
            return rtrim(rtrim(number_format((float) $angka, 3, ',', '.'), '0'), ',');
        };

        $terbilang = function ($nilai) use (&$terbilang) {
            $nilai = abs((int) $nilai);

            $huruf = [
                '',
                'satu',
                'dua',
                'tiga',
                'empat',
                'lima',
                'enam',
                'tujuh',
                'delapan',
                'sembilan',
                'sepuluh',
                'sebelas',
            ];

            if ($nilai < 12) {
                return $huruf[$nilai];
            }

            if ($nilai < 20) {
                return $terbilang($nilai - 10) . ' belas';
            }

            if ($nilai < 100) {
                return $terbilang(floor($nilai / 10)) . ' puluh ' . $terbilang($nilai % 10);
            }

            if ($nilai < 200) {
                return 'seratus ' . $terbilang($nilai - 100);
            }

            if ($nilai < 1000) {
                return $terbilang(floor($nilai / 100)) . ' ratus ' . $terbilang($nilai % 100);
            }

            if ($nilai < 2000) {
                return 'seribu ' . $terbilang($nilai - 1000);
            }

            if ($nilai < 1000000) {
                return $terbilang(floor($nilai / 1000)) . ' ribu ' . $terbilang($nilai % 1000);
            }

            if ($nilai < 1000000000) {
                return $terbilang(floor($nilai / 1000000)) . ' juta ' . $terbilang($nilai % 1000000);
            }

            if ($nilai < 1000000000000) {
                return $terbilang(floor($nilai / 1000000000)) . ' miliar ' . $terbilang($nilai % 1000000000);
            }

            return $terbilang(floor($nilai / 1000000000000)) . ' triliun ' . $terbilang($nilai % 1000000000000);
        };

        $bersihkanTerbilang = function ($teks) {
            $teks = trim(preg_replace('/\s+/', ' ', $teks));

            return $teks === '' ? 'nol' : $teks;
        };

        $terbilangTotal = $bersihkanTerbilang($terbilang(round($pembelian->total_akhir))) . ' rupiah';

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setTitle('Nota Pembelian');

        $spreadsheet->getProperties()
            ->setCreator('Berkat Jaya Nusantara')
            ->setLastModifiedBy('Berkat Jaya Nusantara')
            ->setTitle('Nota Pembelian ' . $pembelian->nomor_pembelian)
            ->setSubject('Nota Pembelian')
            ->setDescription('Nota pembelian Berkat Jaya Nusantara');

        $sheet->getPageSetup()
            ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE)
            ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4)
            ->setFitToWidth(1)
            ->setFitToHeight(0);

        $sheet->getPageMargins()
            ->setTop(0.3)
            ->setRight(0.25)
            ->setLeft(0.25)
            ->setBottom(0.3);

        $sheet->getDefaultRowDimension()->setRowHeight(20);

        $columnWidths = [
            'A' => 6,
            'B' => 16,
            'C' => 28,
            'D' => 12,
            'E' => 14,
            'F' => 14,
            'G' => 16,
            'H' => 16,
            'I' => 16,
            'J' => 18,
        ];

        foreach ($columnWidths as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }

        $thinBorder = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ];

        $bottomBorder = [
            'borders' => [
                'bottom' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ];

        $headerFill = [
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFFFFF'],
            ],
        ];

        $centerStyle = [
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ];

        $leftStyle = [
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_TOP,
                'wrapText' => true,
            ],
        ];

        $rightStyle = [
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_RIGHT,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ];

        $sheet->mergeCells('A1:A3');
        $sheet->mergeCells('B1:I1');
        $sheet->mergeCells('B2:I2');
        $sheet->mergeCells('B3:I3');
        $sheet->mergeCells('J1:J3');

        $sheet->getRowDimension(1)->setRowHeight(24);
        $sheet->getRowDimension(2)->setRowHeight(20);
        $sheet->getRowDimension(3)->setRowHeight(20);

        $logoPath = public_path('assets/img/logo-bjn.png');

        if (file_exists($logoPath)) {
            $drawing = new Drawing();
            $drawing->setName('Logo Berkat Jaya Nusantara');
            $drawing->setDescription('Logo Berkat Jaya Nusantara');
            $drawing->setPath($logoPath);
            $drawing->setHeight(58);
            $drawing->setCoordinates('A1');
            $drawing->setOffsetX(10);
            $drawing->setOffsetY(5);
            $drawing->setWorksheet($sheet);
        } else {
            $sheet->setCellValue('A1', 'BJN');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        }

        $sheet->setCellValue('B1', $namaPerusahaan);
        $sheet->setCellValue('B2', $alamatPerusahaan);
        $sheet->setCellValue('B3', 'Telp: ' . $teleponPerusahaan);
        $sheet->setCellValue('J1', 'SUPPLIER');

        $sheet->getStyle('A1:J3')->applyFromArray($centerStyle);
        $sheet->getStyle('A1:J3')->applyFromArray($headerFill);
        $sheet->getStyle('B1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('B2:B3')->getFont()->setSize(10);
        $sheet->getStyle('J1')->getFont()->setBold(true)->setSize(10);
        $sheet->getStyle('J1:J3')->applyFromArray($thinBorder);

        $sheet->mergeCells('A4:J4');
        $sheet->getStyle('A4:J4')->applyFromArray($bottomBorder);

        $sheet->mergeCells('A5:E5');
        $sheet->mergeCells('F5:J5');
        $sheet->setCellValue('A5', 'INVOICE / NOTA PEMBELIAN');
        $sheet->setCellValue('F5', 'Tanggal: ' . ($pembelian->tanggal_pembelian ? $pembelian->tanggal_pembelian->format('d-m-Y') : '-'));

        $sheet->mergeCells('A6:E6');
        $sheet->mergeCells('F6:J6');
        $sheet->setCellValue('A6', 'No: ' . $pembelian->nomor_pembelian);
        $sheet->setCellValue('F6', 'Status Terima: ' . $statusText);

        $sheet->mergeCells('A7:E7');
        $sheet->mergeCells('F7:J7');
        $sheet->setCellValue('F7', 'Admin: ' . ($pembelian->user->nama_user ?? '-'));

        $sheet->getStyle('A5')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A6')->getFont()->setBold(true);
        $sheet->getStyle('F5:F7')->applyFromArray($rightStyle);
        $sheet->getStyle('A5:E7')->applyFromArray($leftStyle);

        $sheet->mergeCells('A9:E9');
        $sheet->mergeCells('F9:J9');
        $sheet->setCellValue('A9', 'Informasi Supplier');
        $sheet->setCellValue('F9', 'Informasi Dokumen');

        $sheet->getStyle('A9:E9')->getFont()->setBold(true);
        $sheet->getStyle('F9:J9')->getFont()->setBold(true);
        $sheet->getStyle('A9:E9')->applyFromArray($bottomBorder);
        $sheet->getStyle('F9:J9')->applyFromArray($bottomBorder);

        $supplier = $pembelian->supplier;

        $sheet->setCellValue('A10', 'Nama');
        $sheet->mergeCells('B10:E10');
        $sheet->setCellValue('B10', ': ' . ($supplier->nama_supplier ?? '-'));

        $sheet->setCellValue('F10', 'No. DO');
        $sheet->mergeCells('G10:J10');
        $sheet->setCellValueExplicit('G10', ': ' . ($pembelian->nomor_delivery_order ?? '-'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

        $sheet->setCellValue('A11', 'Telepon');
        $sheet->mergeCells('B11:E11');
        $sheet->setCellValueExplicit('B11', ': ' . ($supplier->nomor_telepon ?? '-'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

        $sheet->setCellValue('F11', 'No. Surat Jalan');
        $sheet->mergeCells('G11:J11');
        $sheet->setCellValueExplicit('G11', ': ' . ($pembelian->nomor_surat_jalan ?? '-'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

        $sheet->setCellValue('A12', 'NPWP');
        $sheet->mergeCells('B12:E12');
        $sheet->setCellValueExplicit('B12', ': ' . ($supplier->npwp ?? '-'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

        $sheet->setCellValue('F12', 'Pajak Pembelian');
        $sheet->mergeCells('G12:J12');
        $sheet->setCellValue('G12', ': ' . $modePajak);

        $sheet->setCellValue('A13', 'Alamat');
        $sheet->mergeCells('B13:E13');
        $sheet->setCellValue('B13', ': ' . ($supplier->alamat ?? '-'));

        $sheet->setCellValue('F13', 'Catatan');
        $sheet->mergeCells('G13:J13');
        $sheet->setCellValue('G13', ': ' . ($pembelian->catatan ?? '-'));

        if ($keteranganPenyesuaian) {
            $sheet->setCellValue('F14', 'Catatan Total');
            $sheet->mergeCells('G14:J14');
            $sheet->setCellValue('G14', ': ' . $keteranganPenyesuaian);
            $sheet->getStyle('F14')->getFont()->setBold(true);
            $sheet->getStyle('A14:J14')->applyFromArray($leftStyle);

            $daftarBarangTitleRow = 16;
            $headerRow = 17;
        } else {
            $daftarBarangTitleRow = 15;
            $headerRow = 16;
        }

        $sheet->getStyle('A10:A13')->getFont()->setBold(true);
        $sheet->getStyle('F10:F13')->getFont()->setBold(true);
        $sheet->getStyle('A10:J13')->applyFromArray($leftStyle);

        $sheet->mergeCells('A' . $daftarBarangTitleRow . ':J' . $daftarBarangTitleRow);
        $sheet->setCellValue('A' . $daftarBarangTitleRow, 'Daftar Barang Dibeli');
        $sheet->getStyle('A' . $daftarBarangTitleRow)->getFont()->setBold(true);
        $sheet->getStyle('A' . $daftarBarangTitleRow . ':J' . $daftarBarangTitleRow)->applyFromArray($bottomBorder);

        $headers = [
            'A' => 'No',
            'B' => 'Kode Barang',
            'C' => 'Nama Barang',
            'D' => 'Satuan',
            'E' => 'Dipesan',
            'F' => 'Diterima',
            'G' => 'Sisa',
            'H' => 'Status Item',
            'I' => 'Harga Beli',
            'J' => 'Subtotal',
        ];

        foreach ($headers as $column => $text) {
            $sheet->setCellValue($column . $headerRow, $text);
        }

        $sheet->getStyle('A' . $headerRow . ':J' . $headerRow)->getFont()->setBold(true);
        $sheet->getStyle('A' . $headerRow . ':J' . $headerRow)->applyFromArray($thinBorder);
        $sheet->getStyle('A' . $headerRow . ':J' . $headerRow)->applyFromArray($centerStyle);

        $row = $headerRow + 1;

        foreach ($pembelian->detailPembelian as $index => $detail) {
            $jumlahDipesan = (float) ($detail->jumlah_dipesan ?? $detail->jumlah);
            $jumlahDiterima = (float) $detail->jumlah;
            $sisaBelumDikirim = max($jumlahDipesan - $jumlahDiterima, 0);
            $satuan = $detail->barang->satuan ?? '-';
            $statusItem = $sisaBelumDikirim > 0 ? 'Sebagian' : 'Lengkap';

            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $detail->barang->kode_barang ?? '-');
            $sheet->setCellValue('C' . $row, $detail->barang->nama_barang ?? '-');
            $sheet->setCellValue('D' . $row, strtoupper($satuan));
            $sheet->setCellValue('E' . $row, $jumlahDipesan);
            $sheet->setCellValue('F' . $row, $jumlahDiterima);
            $sheet->setCellValue('G' . $row, $sisaBelumDikirim);
            $sheet->setCellValue('H' . $row, $statusItem);
            $sheet->setCellValue('I' . $row, $detail->harga_beli);
            $sheet->setCellValue('J' . $row, $detail->subtotal);

            $sheet->getStyle('A' . $row . ':J' . $row)->applyFromArray($thinBorder);
            $sheet->getStyle('A' . $row . ':J' . $row)->applyFromArray($leftStyle);
            $sheet->getStyle('A' . $row)->applyFromArray($centerStyle);
            $sheet->getStyle('D' . $row . ':H' . $row)->applyFromArray($centerStyle);
            $sheet->getStyle('I' . $row . ':J' . $row)->applyFromArray($rightStyle);

            $sheet->getStyle('E' . $row . ':G' . $row)->getNumberFormat()->setFormatCode('#,##0.###');
            $sheet->getStyle('I' . $row)->getNumberFormat()->setFormatCode('"Rp" #,##0');
            $sheet->getStyle('J' . $row)->getNumberFormat()->setFormatCode('"Rp" #,##0');

            $row++;
        }

        $totalStartRow = $row;

        $sheet->mergeCells('A' . $row . ':H' . $row);
        $sheet->setCellValue('I' . $row, 'Subtotal Diterima');
        $sheet->setCellValue('J' . $row, $subtotalPembelian);
        $sheet->getStyle('I' . $row . ':J' . $row)->getFont()->setBold(true);
        $sheet->getStyle('I' . $row . ':J' . $row)->applyFromArray($bottomBorder);
        $sheet->getStyle('I' . $row)->applyFromArray($rightStyle);
        $sheet->getStyle('J' . $row)->applyFromArray($rightStyle);
        $sheet->getStyle('J' . $row)->getNumberFormat()->setFormatCode('"Rp" #,##0');
        $row++;

        $sheet->mergeCells('A' . $row . ':H' . $row);
        $sheet->setCellValue('I' . $row, 'PPN Supplier');
        $sheet->setCellValue('J' . $row, $nilaiPajak);
        $sheet->getStyle('I' . $row . ':J' . $row)->getFont()->setBold(true);
        $sheet->getStyle('I' . $row . ':J' . $row)->applyFromArray($bottomBorder);
        $sheet->getStyle('I' . $row)->applyFromArray($rightStyle);
        $sheet->getStyle('J' . $row)->applyFromArray($rightStyle);
        $sheet->getStyle('J' . $row)->getNumberFormat()->setFormatCode('"Rp" #,##0');
        $row++;

        $sheet->mergeCells('A' . $row . ':H' . $row);
        $sheet->setCellValue('I' . $row, 'Biaya Lain / Ongkir');
        $sheet->setCellValue('J' . $row, $biayaLain);
        $sheet->getStyle('I' . $row . ':J' . $row)->getFont()->setBold(true);
        $sheet->getStyle('I' . $row . ':J' . $row)->applyFromArray($bottomBorder);
        $sheet->getStyle('I' . $row)->applyFromArray($rightStyle);
        $sheet->getStyle('J' . $row)->applyFromArray($rightStyle);
        $sheet->getStyle('J' . $row)->getNumberFormat()->setFormatCode('"Rp" #,##0');
        $row++;

        $sheet->mergeCells('A' . $row . ':H' . $row);
        $sheet->setCellValue('I' . $row, 'Total Sebelum Potongan');
        $sheet->setCellValue('J' . $row, $totalSebelumPotongan);
        $sheet->getStyle('I' . $row . ':J' . $row)->getFont()->setBold(true);
        $sheet->getStyle('I' . $row . ':J' . $row)->applyFromArray($bottomBorder);
        $sheet->getStyle('I' . $row)->applyFromArray($rightStyle);
        $sheet->getStyle('J' . $row)->applyFromArray($rightStyle);
        $sheet->getStyle('J' . $row)->getNumberFormat()->setFormatCode('"Rp" #,##0');
        $row++;

        $sheet->mergeCells('A' . $row . ':H' . $row);
        $sheet->setCellValue('I' . $row, 'Potongan / Diskon');
        $sheet->setCellValue('J' . $row, $potonganDiskon > 0 ? -$potonganDiskon : 0);
        $sheet->getStyle('I' . $row . ':J' . $row)->getFont()->setBold(true);
        $sheet->getStyle('I' . $row . ':J' . $row)->applyFromArray($bottomBorder);
        $sheet->getStyle('I' . $row)->applyFromArray($rightStyle);
        $sheet->getStyle('J' . $row)->applyFromArray($rightStyle);
        $sheet->getStyle('J' . $row)->getNumberFormat()->setFormatCode('"Rp" #,##0');
        $row++;

        $sheet->mergeCells('A' . $row . ':H' . $row);
        $sheet->setCellValue('I' . $row, 'Total Akhir');
        $sheet->setCellValue('J' . $row, $pembelian->total_akhir);
        $sheet->getStyle('I' . $row . ':J' . $row)->getFont()->setBold(true);
        $sheet->getStyle('I' . $row . ':J' . $row)->applyFromArray($bottomBorder);
        $sheet->getStyle('I' . $row)->applyFromArray($rightStyle);
        $sheet->getStyle('J' . $row)->applyFromArray($rightStyle);
        $sheet->getStyle('J' . $row)->getNumberFormat()->setFormatCode('"Rp" #,##0');
        $row++;

        $sheet->mergeCells('I' . $row . ':J' . $row);
        $sheet->setCellValue('I' . $row, 'Catatan: PPN pembelian diinput manual sesuai dokumen supplier.');
        $sheet->getStyle('I' . $row . ':J' . $row)->getFont()->setItalic(true)->setSize(10);
        $sheet->getStyle('I' . $row . ':J' . $row)->applyFromArray($rightStyle);
        $row++;

        if ($keteranganPenyesuaian) {
            $sheet->mergeCells('I' . $row . ':J' . $row);
            $sheet->setCellValue('I' . $row, 'Catatan Penyesuaian: ' . $keteranganPenyesuaian);
            $sheet->getStyle('I' . $row . ':J' . $row)->getFont()->setItalic(true)->setSize(10);
            $sheet->getStyle('I' . $row . ':J' . $row)->applyFromArray($rightStyle);
            $row++;
        }

        $row++;

        $sheet->mergeCells('A' . $row . ':F' . $row);
        $sheet->setCellValue('A' . $row, 'Terbilang: ' . $terbilangTotal);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row)->getAlignment()->setWrapText(true);
        $row++;

        if ($statusPenerimaan === 'sebagian') {
            $sheet->mergeCells('A' . $row . ':J' . $row);
            $sheet->setCellValue('A' . $row, 'Keterangan: Sebagian barang belum dikirim oleh supplier. Stok hanya bertambah sesuai jumlah barang yang sudah diterima.');
            $sheet->getStyle('A' . $row)->getFont()->setItalic(true);
            $sheet->getStyle('A' . $row)->getAlignment()->setWrapText(true);
            $row += 2;
        }

        $sheet->mergeCells('A' . $row . ':D' . ($row + 3));
        $sheet->setCellValue(
            'A' . $row,
            "Berkat\nBCA : 5280902227\n------------------------------\nBerkat\nOCBC NISP : 565 8000 15150"
        );
        $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(11)->getColor()->setRGB('B91C1C');
        $sheet->getStyle('A' . $row)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(true);

        $row += 5;

        $sheet->mergeCells('A' . $row . ':E' . $row);
        $sheet->mergeCells('F' . $row . ':J' . $row);
        $sheet->setCellValue('A' . $row, 'Supplier,');
        $sheet->setCellValue('F' . $row, 'Diterima Oleh,');
        $sheet->getStyle('A' . $row . ':J' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row . ':J' . $row)->applyFromArray($centerStyle);
        $row++;

        $signatureStartRow = $row;
        $signatureEndRow = $row + 3;

        $sheet->mergeCells('A' . $signatureStartRow . ':E' . $signatureEndRow);
        $sheet->mergeCells('F' . $signatureStartRow . ':J' . $signatureEndRow);

        for ($i = $signatureStartRow; $i <= $signatureEndRow; $i++) {
            $sheet->getRowDimension($i)->setRowHeight(18);
        }

        $row = $signatureEndRow + 1;

        $sheet->mergeCells('A' . $row . ':E' . $row);
        $sheet->mergeCells('F' . $row . ':J' . $row);
        $sheet->setCellValue('A' . $row, $supplier->nama_supplier ?? 'Supplier');
        $sheet->setCellValue('F' . $row, $namaPerusahaan);
        $sheet->getStyle('A' . $row . ':J' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row . ':J' . $row)->applyFromArray($centerStyle);

        $sheet->getStyle('A1:J' . $row)->getAlignment()->setWrapText(true);
        $sheet->getStyle('A1:J' . $row)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A1:J' . $row)->getFont()->setName('Arial');
        $sheet->getStyle('A' . $totalStartRow . ':J' . $row)->getAlignment()->setWrapText(true);

        $safeNomor = preg_replace('/[^A-Za-z0-9\-_]+/', '-', $pembelian->nomor_pembelian ?? 'nota');
        $safeNomor = trim(preg_replace('/-+/', '-', $safeNomor), '-');
        $fileName = 'Nota-Pembelian-' . ($safeNomor ?: 'nota') . '.xlsx';

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }


    private function generateNomorPembelian(bool $lock = false)
    {
        $tanggal = now()->format('Ymd');
        $prefix = 'PB-' . $tanggal . '-';

        $query = Pembelian::where('nomor_pembelian', 'like', $prefix . '%')
            ->orderBy('nomor_pembelian', 'desc');

        if ($lock) {
            $query->lockForUpdate();
        }

        $lastPembelian = $query->first();

        if (!$lastPembelian) {
            return $prefix . '0001';
        }

        $lastNumber = (int) substr($lastPembelian->nomor_pembelian, -4);
        $newNumber = $lastNumber + 1;

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    private function generateNomorDeliveryOrder(bool $lock = false)
    {
        $tanggal = now()->format('Ymd');
        $prefix = 'DO-SUP-' . $tanggal . '-';

        $query = Pembelian::where('nomor_delivery_order', 'like', $prefix . '%')
            ->orderBy('nomor_delivery_order', 'desc');

        if ($lock) {
            $query->lockForUpdate();
        }

        $lastPembelian = $query->first();

        if (!$lastPembelian || !$lastPembelian->nomor_delivery_order) {
            return $prefix . '0001';
        }

        $lastNumber = (int) substr($lastPembelian->nomor_delivery_order, -4);
        $newNumber = $lastNumber + 1;

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    private function generateNomorSuratJalan(bool $lock = false)
    {
        $tanggal = now()->format('Ymd');
        $prefix = 'SJ-SUP-' . $tanggal . '-';

        $query = Pembelian::where('nomor_surat_jalan', 'like', $prefix . '%')
            ->orderBy('nomor_surat_jalan', 'desc');

        if ($lock) {
            $query->lockForUpdate();
        }

        $lastPembelian = $query->first();

        if (!$lastPembelian || !$lastPembelian->nomor_surat_jalan) {
            return $prefix . '0001';
        }

        $lastNumber = (int) substr($lastPembelian->nomor_surat_jalan, -4);
        $newNumber = $lastNumber + 1;

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    private function ubahKosongMenjadiNull(?string $value): ?string
    {
        $value = trim($value ?? '');

        return $value === '' ? null : $value;
    }
}
