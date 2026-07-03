<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;

        $customers = Customer::query()
            ->when($search, function ($query, $search) {
                $query->where('nama_customer', 'like', "%{$search}%")
                    ->orWhere('kode_customer', 'like', "%{$search}%")
                    ->orWhere('nomor_telepon', 'like', "%{$search}%")
                    ->orWhere('npwp', 'like', "%{$search}%")
                    ->orWhere('alamat', 'like', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('customers.index', compact('customers', 'search'));
    }

    public function create()
    {
        $kodeCustomer = $this->generateKodeCustomer();

        return view('customers.create', compact('kodeCustomer'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_customer' => 'required|string|max:255',
            'nomor_telepon' => 'nullable|string|max:30',
            'npwp' => 'nullable|string|max:30',
            'alamat' => 'nullable|string',
            'kategori_customer' => 'nullable|string|max:100',
            'catatan' => 'nullable|string',
        ]);

        Customer::create([
            'kode_customer' => $this->generateKodeCustomer(),
            'nama_customer' => trim($validated['nama_customer']),
            'nomor_telepon' => $this->ubahKosongMenjadiNull($validated['nomor_telepon'] ?? null),
            'npwp' => $this->ubahKosongMenjadiNull($validated['npwp'] ?? null),
            'alamat' => $this->ubahKosongMenjadiNull($validated['alamat'] ?? null),
            'kategori_customer' => $this->ubahKosongMenjadiNull($validated['kategori_customer'] ?? null),
            'catatan' => $this->ubahKosongMenjadiNull($validated['catatan'] ?? null),
            'status_aktif' => true,
        ]);

        return redirect()
            ->route('customers.index')
            ->with('success', 'Data customer berhasil ditambahkan.');
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'nama_customer' => 'required|string|max:255',
            'nomor_telepon' => 'nullable|string|max:30',
            'npwp' => 'nullable|string|max:30',
            'alamat' => 'nullable|string',
            'kategori_customer' => 'nullable|string|max:100',
            'catatan' => 'nullable|string',
            'status_aktif' => 'required|boolean',
        ]);

        $customer->update([
            'nama_customer' => trim($validated['nama_customer']),
            'nomor_telepon' => $this->ubahKosongMenjadiNull($validated['nomor_telepon'] ?? null),
            'npwp' => $this->ubahKosongMenjadiNull($validated['npwp'] ?? null),
            'alamat' => $this->ubahKosongMenjadiNull($validated['alamat'] ?? null),
            'kategori_customer' => $this->ubahKosongMenjadiNull($validated['kategori_customer'] ?? null),
            'catatan' => $this->ubahKosongMenjadiNull($validated['catatan'] ?? null),
            'status_aktif' => $validated['status_aktif'],
        ]);

        return redirect()
            ->route('customers.index')
            ->with('success', 'Data customer berhasil diperbarui.');
    }

    public function nonaktifkan(Customer $customer)
    {
        $customer->update([
            'status_aktif' => false,
        ]);

        return redirect()
            ->route('customers.index')
            ->with('success', 'Customer berhasil dinonaktifkan.');
    }

    public function quickStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_customer' => 'required|string|max:255',
            'nomor_telepon' => 'nullable|string|max:30',
            'npwp' => 'nullable|string|max:30',
            'alamat' => 'nullable|string',
            'kategori_customer' => 'nullable|string|max:100',
            'catatan' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Data customer tidak valid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        $customer = Customer::create([
            'kode_customer' => $this->generateKodeCustomer(),
            'nama_customer' => trim($validated['nama_customer']),
            'nomor_telepon' => $this->ubahKosongMenjadiNull($validated['nomor_telepon'] ?? null),
            'npwp' => $this->ubahKosongMenjadiNull($validated['npwp'] ?? null),
            'alamat' => $this->ubahKosongMenjadiNull($validated['alamat'] ?? null),
            'kategori_customer' => $this->ubahKosongMenjadiNull($validated['kategori_customer'] ?? null),
            'catatan' => $this->ubahKosongMenjadiNull($validated['catatan'] ?? null),
            'status_aktif' => true,
        ]);

        return response()->json([
            'status' => 'created',
            'message' => 'Customer baru berhasil ditambahkan dan langsung dipilih.',
            'customer' => [
                'id_customer' => $customer->id_customer,
                'kode_customer' => $customer->kode_customer,
                'nama_customer' => $customer->nama_customer,
                'nomor_telepon' => $customer->nomor_telepon,
                'npwp' => $customer->npwp,
                'alamat' => $customer->alamat,
                'kategori_customer' => $customer->kategori_customer,
            ],
        ]);
    }

    private function generateKodeCustomer()
    {
        $lastCustomer = Customer::orderBy('id_customer', 'desc')->first();

        if (!$lastCustomer) {
            return 'CUS-0001';
        }

        $lastNumber = (int) substr($lastCustomer->kode_customer, 4);
        $newNumber = $lastNumber + 1;

        return 'CUS-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    private function ubahKosongMenjadiNull(?string $value): ?string
    {
        $value = trim($value ?? '');

        return $value === '' ? null : $value;
    }
}