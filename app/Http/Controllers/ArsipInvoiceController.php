<?php

namespace App\Http\Controllers;

use App\Models\Pembelian;
use App\Models\Penjualan;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;

class ArsipInvoiceController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) ($request->per_page ?? 10);
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 10;

        $backUrl = route('arsip-invoice.index', $request->query());

        $arsipPenjualan = $request->jenis_invoice === 'pembelian'
            ? collect()
            : $this->queryPenjualan($request)->get()->map(function (Penjualan $penjualan) use ($backUrl) {
                return $this->formatArsipPenjualan($penjualan, $backUrl);
            });

        $arsipPembelian = $request->jenis_invoice === 'penjualan'
            ? collect()
            : $this->queryPembelian($request)->get()->map(function (Pembelian $pembelian) use ($backUrl) {
                return $this->formatArsipPembelian($pembelian, $backUrl);
            });

        $semuaArsip = $arsipPenjualan
            ->merge($arsipPembelian)
            ->sort(function (array $a, array $b) {
                $tanggalCompare = strcmp($b['tanggal_sort'], $a['tanggal_sort']);

                if ($tanggalCompare !== 0) {
                    return $tanggalCompare;
                }

                return strcmp($b['created_at_sort'], $a['created_at_sort']);
            })
            ->values();

        $ringkasan = $this->hitungRingkasan($semuaArsip);
        $arsipInvoice = $this->paginateCollection($semuaArsip, $perPage, $request);

        return view('arsip-invoice.index', array_merge([
            'arsipInvoice' => $arsipInvoice,
            'perPage' => $perPage,
        ], $ringkasan));
    }

    private function queryPenjualan(Request $request)
    {
        return Penjualan::with(['customer', 'piutang'])
            ->when($request->tipe_invoice === 'historis', function ($query) {
                $query->where('is_historical', true);
            })
            ->when($request->tipe_invoice === 'berjalan', function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->whereNull('is_historical')
                        ->orWhere('is_historical', false);
                });
            })
            ->when($request->tanggal_awal, function ($query) use ($request) {
                $query->whereDate('tanggal_penjualan', '>=', $request->tanggal_awal);
            })
            ->when($request->tanggal_akhir, function ($query) use ($request) {
                $query->whereDate('tanggal_penjualan', '<=', $request->tanggal_akhir);
            })
            ->when($request->metode_pembayaran, function ($query) use ($request) {
                $query->where('metode_pembayaran', $request->metode_pembayaran);
            })
            ->when($request->status_pembayaran, function ($query) use ($request) {
                $query->where('status_pembayaran', $request->status_pembayaran);
            })
            ->when($request->search, function ($query) use ($request) {
                $search = $request->search;

                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('nomor_invoice', 'like', "%{$search}%")
                        ->orWhere('nomor_dokumen_asli', 'like', "%{$search}%")
                        ->orWhere('nomor_faktur_pajak', 'like', "%{$search}%")
                        ->orWhere('catatan', 'like', "%{$search}%")
                        ->orWhereHas('customer', function ($customerQuery) use ($search) {
                            $customerQuery->where('kode_customer', 'like', "%{$search}%")
                                ->orWhere('nama_customer', 'like', "%{$search}%")
                                ->orWhere('nomor_telepon', 'like', "%{$search}%")
                                ->orWhere('npwp', 'like', "%{$search}%")
                                ->orWhere('alamat', 'like', "%{$search}%");
                        });
                });
            });
    }

    private function queryPembelian(Request $request)
    {
        return Pembelian::with(['supplier'])
            ->when($request->tipe_invoice === 'historis', function ($query) {
                $query->where('is_historical', true);
            })
            ->when($request->tipe_invoice === 'berjalan', function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->whereNull('is_historical')
                        ->orWhere('is_historical', false);
                });
            })
            ->when($request->tanggal_awal, function ($query) use ($request) {
                $query->whereDate('tanggal_pembelian', '>=', $request->tanggal_awal);
            })
            ->when($request->tanggal_akhir, function ($query) use ($request) {
                $query->whereDate('tanggal_pembelian', '<=', $request->tanggal_akhir);
            })
            ->when($request->status_penerimaan, function ($query) use ($request) {
                $query->where('status_penerimaan', $request->status_penerimaan);
            })
            ->when($request->search, function ($query) use ($request) {
                $search = $request->search;

                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('nomor_pembelian', 'like', "%{$search}%")
                        ->orWhere('nomor_dokumen_asli', 'like', "%{$search}%")
                        ->orWhere('nomor_delivery_order', 'like', "%{$search}%")
                        ->orWhere('nomor_surat_jalan', 'like', "%{$search}%")
                        ->orWhere('catatan', 'like', "%{$search}%")
                        ->orWhereHas('supplier', function ($supplierQuery) use ($search) {
                            $supplierQuery->where('kode_supplier', 'like', "%{$search}%")
                                ->orWhere('nama_supplier', 'like', "%{$search}%")
                                ->orWhere('nomor_telepon', 'like', "%{$search}%")
                                ->orWhere('npwp', 'like', "%{$search}%")
                                ->orWhere('alamat', 'like', "%{$search}%");
                        });
                });
            });
    }

    private function formatArsipPenjualan(Penjualan $penjualan, string $backUrl): array
    {
        $isHistoris = (bool) ($penjualan->is_historical ?? false);
        $nomorTampil = $isHistoris && !empty($penjualan->nomor_dokumen_asli)
            ? $penjualan->nomor_dokumen_asli
            : $penjualan->nomor_invoice;

        $showRoute = $isHistoris
            ? route('invoice-historis.penjualan.show', [
                'penjualan' => $penjualan->id_penjualan,
                'back_url' => $backUrl,
            ])
            : route('penjualan.show', [
                'penjualan' => $penjualan->id_penjualan,
                'back_url' => $backUrl,
            ]);

        $excelRoute = $isHistoris
            ? route('invoice-historis.penjualan.exportExcel', $penjualan->id_penjualan)
            : route('penjualan.exportExcel', $penjualan->id_penjualan);

        return [
            'jenis' => 'penjualan',
            'jenis_label' => 'Penjualan',
            'jenis_class' => 'bg-blue-100 text-blue-700',
            'id' => $penjualan->id_penjualan,
            'nomor_invoice' => $nomorTampil,
            'nomor_sistem' => $penjualan->nomor_invoice,
            'nomor_dokumen_asli' => $penjualan->nomor_dokumen_asli,
            'tanggal' => $penjualan->tanggal_penjualan,
            'tanggal_sort' => optional($penjualan->tanggal_penjualan)->format('Y-m-d') ?? '0000-00-00',
            'created_at_sort' => optional($penjualan->created_at)->format('Y-m-d H:i:s') ?? '0000-00-00 00:00:00',
            'pihak_label' => 'Customer',
            'pihak_nama' => $penjualan->customer->nama_customer ?? '-',
            'pihak_kode' => $penjualan->customer->kode_customer ?? null,
            'metode' => $penjualan->metode_pembayaran ?? '-',
            'status' => $penjualan->status_pembayaran ?? '-',
            'status_label' => $this->labelStatusPembayaran($penjualan->status_pembayaran),
            'status_class' => $this->classStatusPembayaran($penjualan->status_pembayaran),
            'total' => (float) ($penjualan->total_akhir ?? 0),
            'subtotal' => (float) ($penjualan->subtotal ?? 0),
            'nilai_pajak' => (float) ($penjualan->nilai_pajak ?? 0),
            'catatan' => $penjualan->catatan,
            'is_historical' => $isHistoris,
            'tipe_label' => $isHistoris ? 'Historis' : 'Berjalan',
            'tipe_class' => $isHistoris ? 'bg-purple-100 text-purple-700' : 'bg-green-100 text-green-700',
            'affect_stock' => (bool) ($penjualan->affect_stock ?? true),
            'show_route' => $showRoute,
            'excel_route' => $excelRoute,
            'edit_route' => $isHistoris
                ? route('invoice-historis.penjualan.edit', $penjualan->id_penjualan)
                : route('penjualan.edit', $penjualan->id_penjualan),
        ];
    }

    private function formatArsipPembelian(Pembelian $pembelian, string $backUrl): array
    {
        $isHistoris = (bool) ($pembelian->is_historical ?? false);
        $nomorTampil = $isHistoris && !empty($pembelian->nomor_dokumen_asli)
            ? $pembelian->nomor_dokumen_asli
            : $pembelian->nomor_pembelian;

        $showRoute = $isHistoris
            ? route('invoice-historis.pembelian.show', [
                'pembelian' => $pembelian->id_pembelian,
                'back_url' => $backUrl,
            ])
            : route('pembelian.show', [
                'pembelian' => $pembelian->id_pembelian,
                'back_url' => $backUrl,
            ]);

        $excelRoute = $isHistoris
            ? route('invoice-historis.pembelian.exportExcel', $pembelian->id_pembelian)
            : route('pembelian.exportExcel', $pembelian->id_pembelian);

        return [
            'jenis' => 'pembelian',
            'jenis_label' => 'Pembelian',
            'jenis_class' => 'bg-purple-100 text-purple-700',
            'id' => $pembelian->id_pembelian,
            'nomor_invoice' => $nomorTampil,
            'nomor_sistem' => $pembelian->nomor_pembelian,
            'nomor_dokumen_asli' => $pembelian->nomor_dokumen_asli,
            'tanggal' => $pembelian->tanggal_pembelian,
            'tanggal_sort' => optional($pembelian->tanggal_pembelian)->format('Y-m-d') ?? '0000-00-00',
            'created_at_sort' => optional($pembelian->created_at)->format('Y-m-d H:i:s') ?? '0000-00-00 00:00:00',
            'pihak_label' => 'Supplier',
            'pihak_nama' => $pembelian->supplier->nama_supplier ?? '-',
            'pihak_kode' => $pembelian->supplier->kode_supplier ?? null,
            'metode' => '-',
            'status' => $pembelian->status_penerimaan ?? 'lengkap',
            'status_label' => $this->labelStatusPenerimaan($pembelian->status_penerimaan),
            'status_class' => $this->classStatusPenerimaan($pembelian->status_penerimaan),
            'total' => (float) ($pembelian->total_akhir ?? 0),
            'subtotal' => (float) ($pembelian->subtotal ?? 0),
            'nilai_pajak' => (float) ($pembelian->nilai_pajak ?? 0),
            'catatan' => $pembelian->catatan,
            'is_historical' => $isHistoris,
            'tipe_label' => $isHistoris ? 'Historis' : 'Berjalan',
            'tipe_class' => $isHistoris ? 'bg-purple-100 text-purple-700' : 'bg-green-100 text-green-700',
            'affect_stock' => (bool) ($pembelian->affect_stock ?? true),
            'show_route' => $showRoute,
            'excel_route' => $excelRoute,
            'edit_route' => $isHistoris
                ? route('invoice-historis.pembelian.edit', $pembelian->id_pembelian)
                : route('pembelian.edit', $pembelian->id_pembelian),
            'nomor_delivery_order' => $pembelian->nomor_delivery_order,
            'nomor_surat_jalan' => $pembelian->nomor_surat_jalan,
        ];
    }

    private function hitungRingkasan(Collection $arsip): array
    {
        $penjualan = $arsip->where('jenis', 'penjualan');
        $pembelian = $arsip->where('jenis', 'pembelian');

        return [
            'totalArsip' => $arsip->count(),
            'totalPenjualan' => $penjualan->count(),
            'totalPembelian' => $pembelian->count(),
            'totalHistoris' => $arsip->where('is_historical', true)->count(),
            'totalBerjalan' => $arsip->where('is_historical', false)->count(),
            'totalNilaiPenjualan' => $penjualan->sum('total'),
            'totalNilaiPembelian' => $pembelian->sum('total'),
            'totalNilaiInvoice' => $arsip->sum('total'),
            'totalPenjualanTunai' => $penjualan->where('metode', 'tunai')->count(),
            'totalPenjualanKredit' => $penjualan->where('metode', 'kredit')->count(),
            'totalPenjualanLunas' => $penjualan->where('status', 'lunas')->count(),
            'totalPenjualanBelumLunas' => $penjualan->filter(function ($item) {
                return in_array($item['status'], ['belum_lunas', 'sebagian_dibayar'], true);
            })->count(),
            'totalPembelianLengkap' => $pembelian->where('status', 'lengkap')->count(),
            'totalPembelianSebagian' => $pembelian->where('status', 'sebagian')->count(),
        ];
    }

    private function paginateCollection(Collection $items, int $perPage, Request $request): LengthAwarePaginator
    {
        $page = Paginator::resolveCurrentPage('page');
        $page = $page > 0 ? $page : 1;

        $results = $items->forPage($page, $perPage)->values();

        return new LengthAwarePaginator(
            $results,
            $items->count(),
            $perPage,
            $page,
            [
                'path' => Paginator::resolveCurrentPath(),
                'query' => $request->query(),
            ]
        );
    }

    private function labelStatusPembayaran(?string $status): string
    {
        return match ($status) {
            'lunas' => 'Lunas',
            'belum_lunas' => 'Belum Lunas',
            'sebagian_dibayar' => 'Sebagian Dibayar',
            default => $status ? ucwords(str_replace('_', ' ', $status)) : '-',
        };
    }

    private function classStatusPembayaran(?string $status): string
    {
        return match ($status) {
            'lunas' => 'bg-green-100 text-green-700',
            'sebagian_dibayar' => 'bg-yellow-100 text-yellow-700',
            'belum_lunas' => 'bg-red-100 text-red-700',
            default => 'bg-gray-100 text-gray-700',
        };
    }

    private function labelStatusPenerimaan(?string $status): string
    {
        return match ($status) {
            'lengkap' => 'Lengkap',
            'sebagian' => 'Sebagian',
            'belum_dikirim' => 'Belum Dikirim',
            default => $status ? ucwords(str_replace('_', ' ', $status)) : 'Lengkap',
        };
    }

    private function classStatusPenerimaan(?string $status): string
    {
        return match ($status) {
            'lengkap' => 'bg-green-100 text-green-700',
            'sebagian' => 'bg-yellow-100 text-yellow-700',
            'belum_dikirim' => 'bg-gray-100 text-gray-700',
            default => 'bg-green-100 text-green-700',
        };
    }
}
