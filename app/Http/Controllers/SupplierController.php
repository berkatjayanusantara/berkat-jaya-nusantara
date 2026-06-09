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

    public function quickStore(Request $request)
    {
        $request->validate([
            'nama_supplier' => 'required|string|max:255',
            'nomor_telepon' => 'required|string|max:30',
            'alamat' => 'nullable|string',
            'catatan' => 'nullable|string',
        ]);

        $namaSupplier = trim($request->nama_supplier);
        $nomorTelepon = trim($request->nomor_telepon);
        $nomorTeleponNormal = $this->normalisasiNomorTelepon($nomorTelepon);

        /*
         * Cek supplier lama:
         * - Jika nama sama, dianggap supplier sudah tersedia.
         * - Jika nomor HP sama, dianggap supplier sudah tersedia.
         */
        $existingSupplier = Supplier::query()
            ->whereRaw('LOWER(nama_supplier) = ?', [strtolower($namaSupplier)])
            ->orWhere('nomor_telepon', $nomorTelepon)
            ->get()
            ->first(function ($supplier) use ($namaSupplier, $nomorTeleponNormal) {
                $namaSama = strtolower(trim($supplier->nama_supplier)) === strtolower($namaSupplier);
                $nomorSama = $this->normalisasiNomorTelepon($supplier->nomor_telepon) === $nomorTeleponNormal;

                return $namaSama || $nomorSama;
            });

        if ($existingSupplier) {
            if (!$existingSupplier->status_aktif) {
                $existingSupplier->update([
                    'status_aktif' => true,
                ]);
            }

            return response()->json([
                'status' => 'exists',
                'message' => 'Supplier sudah tersedia dan langsung dipilih.',
                'supplier' => [
                    'id_supplier' => $existingSupplier->id_supplier,
                    'kode_supplier' => $existingSupplier->kode_supplier,
                    'nama_supplier' => $existingSupplier->nama_supplier,
                    'nomor_telepon' => $existingSupplier->nomor_telepon,
                    'alamat' => $existingSupplier->alamat,
                ],
            ]);
        }

        $supplier = Supplier::create([
            'kode_supplier' => $this->generateKodeSupplier(),
            'nama_supplier' => $namaSupplier,
            'nomor_telepon' => $nomorTelepon,
            'alamat' => $request->alamat,
            'catatan' => $request->catatan,
            'status_aktif' => true,
        ]);

        return response()->json([
            'status' => 'created',
            'message' => 'Supplier baru berhasil ditambahkan dan langsung dipilih.',
            'supplier' => [
                'id_supplier' => $supplier->id_supplier,
                'kode_supplier' => $supplier->kode_supplier,
                'nama_supplier' => $supplier->nama_supplier,
                'nomor_telepon' => $supplier->nomor_telepon,
                'alamat' => $supplier->alamat,
            ],
        ]);
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

    private function normalisasiNomorTelepon(?string $nomorTelepon): string
    {
        return preg_replace('/[^0-9]/', '', $nomorTelepon ?? '');
    }
}
