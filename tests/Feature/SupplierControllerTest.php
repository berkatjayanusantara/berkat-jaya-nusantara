<?php

namespace Tests\Feature;

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_index_displays_suppliers_list()
    {
        Supplier::factory()->count(3)->create();

        $response = $this->actingAs($this->user)->get(route('suppliers.index'));

        $response->assertStatus(200);
        $response->assertViewIs('suppliers.index');
        $response->assertViewHas('suppliers');
    }

    public function test_create_displays_form()
    {
        $response = $this->actingAs($this->user)->get(route('suppliers.create'));

        $response->assertStatus(200);
        $response->assertViewIs('suppliers.create');
    }

    public function test_store_saves_new_supplier()
    {
        $data = [
            'nama_supplier' => 'PT Makmur Jaya Supplier',
            'nomor_telepon' => '08123456789',
            'alamat' => 'Jl. Sudirman No 1',
        ];

        $response = $this->actingAs($this->user)->post(route('suppliers.store'), $data);

        $response->assertRedirect(route('suppliers.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('suppliers', [
            'nama_supplier' => 'PT Makmur Jaya Supplier',
            'nomor_telepon' => '08123456789',
        ]);
    }

    public function test_edit_displays_form_with_supplier_data()
    {
        $supplier = Supplier::factory()->create();

        $response = $this->actingAs($this->user)->get(route('suppliers.edit', $supplier->id_supplier));

        $response->assertStatus(200);
        $response->assertViewIs('suppliers.edit');
        $response->assertViewHas('supplier');
    }

    public function test_update_modifies_existing_supplier()
    {
        $supplier = Supplier::factory()->create([
            'nama_supplier' => 'Toko Lama',
        ]);

        $data = [
            'nama_supplier' => 'Toko Baru',
            'nomor_telepon' => $supplier->nomor_telepon,
            'status_aktif' => 1,
        ];

        $response = $this->actingAs($this->user)->put(route('suppliers.update', $supplier->id_supplier), $data);

        $response->assertRedirect(route('suppliers.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('suppliers', [
            'id_supplier' => $supplier->id_supplier,
            'nama_supplier' => 'Toko Baru',
        ]);
    }

    public function test_nonaktifkan_changes_status_aktif()
    {
        $supplier = Supplier::factory()->create(['status_aktif' => 1]);

        $response = $this->actingAs($this->user)->patch(route('suppliers.nonaktifkan', $supplier->id_supplier));

        $response->assertRedirect(route('suppliers.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('suppliers', [
            'id_supplier' => $supplier->id_supplier,
            'status_aktif' => 0,
        ]);
    }
}
