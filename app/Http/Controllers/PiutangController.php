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

    public function bayar(Piutang $piutang)
    {
        $piutang->load(['customer', 'penjualan']);

        if ($piutang->status_piutang === 'lunas') {
            return redirect()
                ->route('piutang.show', $piutang->id_piutang)
                ->with('error', 'Piutang ini sudah lunas.');
        }

        return view('piutang.bayar', compact('piutang'));
    }

    public function simpanPembayaran(Request $request, Piutang $piutang)
    {
        $request->validate([
            'tanggal_pembayaran' => 'required|date',
            'nominal_pembayaran' => 'required|numeric|min:1|max:' . $piutang->sisa_piutang,
            'metode_pembayaran' => 'required|in:tunai,transfer,giro,lainnya',
            'catatan' => 'nullable|string',
        ]);

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
            ->route('piutang.show', $piutang->id_piutang)
            ->with('success', 'Pembayaran piutang berhasil disimpan.');
    }
}
