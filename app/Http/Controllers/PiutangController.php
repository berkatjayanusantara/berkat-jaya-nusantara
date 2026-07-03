<?php

namespace App\Http\Controllers;

use App\Models\PembayaranPiutang;
use App\Models\Penjualan;
use App\Models\Piutang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PiutangController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;
        $status = $request->status;

        $piutang = Piutang::with(['customer', 'penjualan'])
            ->when($search, function ($query, $search) {
                $query->where('nomor_invoice', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($customerQuery) use ($search) {
                        $customerQuery->where('nama_customer', 'like', "%{$search}%");
                    });
            })
            ->when($status, function ($query, $status) {
                $query->where('status_piutang', $status);
            })
            ->orderBy('tanggal_jatuh_tempo', 'asc')
            ->paginate(10);

        return view('piutang.index', compact('piutang', 'search', 'status'));
    }

    public function show(Piutang $piutang)
    {
        $piutang->load([
            'customer',
            'penjualan',
            'pembayaranPiutang.user',
        ]);

        return view('piutang.show', compact('piutang'));
    }

    public function bayar(Request $request, Piutang $piutang)
    {
        $piutang->load(['customer', 'penjualan']);

        $backUrl = $request->query('back_url', route('piutang.index'));

        if ($piutang->status_piutang === 'lunas') {
            return redirect()
                ->route('piutang.show', [
                    'piutang' => $piutang->id_piutang,
                    'back_url' => $backUrl,
                ])
                ->with('error', 'Piutang ini sudah lunas.');
        }

        return view('piutang.bayar', compact('piutang', 'backUrl'));
    }

    public function simpanPembayaran(Request $request, Piutang $piutang)
    {
        $maxPembayaran = ceil($piutang->sisa_piutang);
        // Jika sisa piutang kurang dari 1 (misal 0.8), set max minimal 1 agar bisa dilunasi.
        if ($maxPembayaran < 1 && $piutang->sisa_piutang > 0) {
            $maxPembayaran = 1;
        }

        $request->validate([
            'tanggal_pembayaran' => 'required|date',
            'nominal_pembayaran' => 'required|numeric|min:1|max:' . $maxPembayaran,
            'metode_pembayaran' => 'required|in:tunai,transfer,giro,lainnya',
            'catatan' => 'nullable|string',
            'back_url' => 'nullable|string',
        ]);

        $backUrl = $request->input('back_url', route('piutang.index'));

        DB::transaction(function () use ($request, $piutang) {
            PembayaranPiutang::create([
                'id_piutang' => $piutang->id_piutang,
                'tanggal_pembayaran' => $request->tanggal_pembayaran,
                'nominal_pembayaran' => $request->nominal_pembayaran,
                'metode_pembayaran' => $request->metode_pembayaran,
                'catatan' => $request->catatan,
                'dibuat_oleh' => Auth::id(),
            ]);

            $totalDibayarBaru = $piutang->total_dibayar + $request->nominal_pembayaran;
            $sisaPiutangBaru = $piutang->total_piutang - $totalDibayarBaru;

            if ($sisaPiutangBaru <= 0) {
                $statusPiutang = 'lunas';
                $statusPembayaran = 'lunas';
                $sisaPiutangBaru = 0;
            } else {
                $statusPiutang = 'sebagian_dibayar';
                $statusPembayaran = 'sebagian';
            }

            $piutang->update([
                'total_dibayar' => $totalDibayarBaru,
                'sisa_piutang' => $sisaPiutangBaru,
                'status_piutang' => $statusPiutang,
            ]);

            Penjualan::where('id_penjualan', $piutang->id_penjualan)
                ->update([
                    'status_pembayaran' => $statusPembayaran,
                ]);
        });

        return redirect()
            ->route('piutang.show', [
                'piutang' => $piutang->id_piutang,
                'back_url' => $backUrl,
            ])
            ->with('success', 'Pembayaran piutang berhasil disimpan.');
    }

    public function editPembayaran(Request $request, Piutang $piutang, PembayaranPiutang $pembayaranPiutang)
    {
        $piutang->load(['customer', 'penjualan']);
        
        if ($pembayaranPiutang->id_piutang !== $piutang->id_piutang) {
            abort(404);
        }

        $backUrl = $request->query('back_url', route('piutang.index'));

        return view('piutang.edit-pembayaran', compact('piutang', 'pembayaranPiutang', 'backUrl'));
    }

    public function updatePembayaran(Request $request, Piutang $piutang, PembayaranPiutang $pembayaranPiutang)
    {
        if ($pembayaranPiutang->id_piutang !== $piutang->id_piutang) {
            abort(404);
        }

        // Hitung ulang sisa_piutang jika pembayaran ini dibatalkan
        $sisaDenganEdit = $piutang->sisa_piutang + $pembayaranPiutang->nominal_pembayaran;
        $maxPembayaran = ceil($sisaDenganEdit);
        if ($maxPembayaran < 1 && $sisaDenganEdit > 0) {
            $maxPembayaran = 1;
        }

        $request->validate([
            'tanggal_pembayaran' => 'required|date',
            'nominal_pembayaran' => 'required|numeric|min:1|max:' . $maxPembayaran,
            'metode_pembayaran' => 'required|in:tunai,transfer,giro,lainnya',
            'catatan' => 'nullable|string',
            'back_url' => 'nullable|string',
        ]);

        $backUrl = $request->input('back_url', route('piutang.index'));

        DB::transaction(function () use ($request, $piutang, $pembayaranPiutang) {
            $pembayaranPiutang->update([
                'tanggal_pembayaran' => $request->tanggal_pembayaran,
                'nominal_pembayaran' => $request->nominal_pembayaran,
                'metode_pembayaran' => $request->metode_pembayaran,
                'catatan' => $request->catatan,
            ]);

            $totalDibayarBaru = PembayaranPiutang::where('id_piutang', $piutang->id_piutang)->sum('nominal_pembayaran');
            $sisaPiutangBaru = $piutang->total_piutang - $totalDibayarBaru;

            if ($sisaPiutangBaru <= 0) {
                $statusPiutang = 'lunas';
                $statusPembayaran = 'lunas';
                $sisaPiutangBaru = 0;
            } else {
                $statusPiutang = 'sebagian_dibayar';
                $statusPembayaran = 'sebagian';
            }

            $piutang->update([
                'total_dibayar' => $totalDibayarBaru,
                'sisa_piutang' => $sisaPiutangBaru,
                'status_piutang' => $statusPiutang,
            ]);

            Penjualan::where('id_penjualan', $piutang->id_penjualan)
                ->update([
                    'status_pembayaran' => $statusPembayaran,
                ]);
        });

        return redirect()
            ->route('piutang.show', [
                'piutang' => $piutang->id_piutang,
                'back_url' => $backUrl,
            ])
            ->with('success', 'Pembayaran piutang berhasil diperbarui.');
    }
}
