<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;

        $suppliers = Supplier::query()
            ->when($search, function ($query, $search) {
                $query->where('nama_supplier', 'like', "%{$search}%")
                    ->orWhere('kode_supplier', 'like', "%{$search}%")
                    ->orWhere('nomor_telepon', 'like', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('suppliers.index', compact('suppliers', 'search'));
    }

    public function create()
    {
        $kodeSupplier = $this->generateKodeSupplier();

        return view('suppliers.create', compact('kodeSupplier'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_supplier' => 'required|string|max:255',
            'nomor_telepon' => 'nullable|string|max:30',
            'alamat' => 'nullable|string',
            'catatan' => 'nullable|string',
        ]);

        Supplier::create([
            'kode_supplier' => $this->generateKodeSupplier(),
            'nama_supplier' => $request->nama_supplier,
            'nomor_telepon' => $request->nomor_telepon,
            'alamat' => $request->alamat,
            'catatan' => $request->catatan,
            'status_aktif' => true,
        ]);

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Data supplier berhasil ditambahkan.');
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $request->validate([
            'nama_supplier' => 'required|string|max:255',
            'nomor_telepon' => 'nullable|string|max:30',
            'alamat' => 'nullable|string',
            'catatan' => 'nullable|string',
            'status_aktif' => 'required|boolean',
        ]);

        $supplier->update([
            'nama_supplier' => $request->nama_supplier,
            'nomor_telepon' => $request->nomor_telepon,
            'alamat' => $request->alamat,
            'catatan' => $request->catatan,
            'status_aktif' => $request->status_aktif,
        ]);

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Data supplier berhasil diperbarui.');
    }

    public function nonaktifkan(Supplier $supplier)
    {
        $supplier->update([
            'status_aktif' => false,
        ]);

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Supplier berhasil dinonaktifkan.');
    }

    private function generateKodeSupplier()
    {
        $lastSupplier = Supplier::orderBy('id_supplier', 'desc')->first();

        if (!$lastSupplier) {
            return 'SUP-0001';
        }

        $lastNumber = (int) substr($lastSupplier->kode_supplier, 4);
        $newNumber = $lastNumber + 1;

        return 'SUP-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
