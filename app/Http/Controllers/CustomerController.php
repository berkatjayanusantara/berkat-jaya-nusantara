<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;

        $customers = Customer::query()
            ->when($search, function ($query, $search) {
                $query->where('nama_customer', 'like', "%{$search}%")
                    ->orWhere('kode_customer', 'like', "%{$search}%")
                    ->orWhere('nomor_telepon', 'like', "%{$search}%");
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
        $request->validate([
            'nama_customer' => 'required|string|max:255',
            'nomor_telepon' => 'nullable|string|max:30',
            'alamat' => 'nullable|string',
            'kategori_customer' => 'nullable|string|max:100',
            'catatan' => 'nullable|string',
        ]);

        Customer::create([
            'kode_customer' => $this->generateKodeCustomer(),
            'nama_customer' => $request->nama_customer,
            'nomor_telepon' => $request->nomor_telepon,
            'alamat' => $request->alamat,
            'kategori_customer' => $request->kategori_customer,
            'catatan' => $request->catatan,
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
        $request->validate([
            'nama_customer' => 'required|string|max:255',
            'nomor_telepon' => 'nullable|string|max:30',
            'alamat' => 'nullable|string',
            'kategori_customer' => 'nullable|string|max:100',
            'catatan' => 'nullable|string',
            'status_aktif' => 'required|boolean',
        ]);

        $customer->update([
            'nama_customer' => $request->nama_customer,
            'nomor_telepon' => $request->nomor_telepon,
            'alamat' => $request->alamat,
            'kategori_customer' => $request->kategori_customer,
            'catatan' => $request->catatan,
            'status_aktif' => $request->status_aktif,
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
        $request->validate([
            'nama_customer' => 'required|string|max:255',
            'nomor_telepon' => 'nullable|string|max:30',
            'alamat' => 'nullable|string',
            'kategori_customer' => 'nullable|string|max:100',
            'catatan' => 'nullable|string',
        ]);

        $customer = Customer::create([
            'kode_customer' => $this->generateKodeCustomer(),
            'nama_customer' => $request->nama_customer,
            'nomor_telepon' => $request->nomor_telepon,
            'alamat' => $request->alamat,
            'kategori_customer' => $request->kategori_customer,
            'catatan' => $request->catatan,
            'status_aktif' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Customer berhasil ditambahkan.',
            'customer' => [
                'id_customer' => $customer->id_customer,
                'kode_customer' => $customer->kode_customer,
                'nama_customer' => $customer->nama_customer,
                'nomor_telepon' => $customer->nomor_telepon,
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
}
