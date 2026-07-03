<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_index_displays_customers_list()
    {
        Customer::factory()->count(3)->create();

        $response = $this->actingAs($this->user)->get(route('customers.index'));

        $response->assertStatus(200);
        $response->assertViewIs('customers.index');
        $response->assertViewHas('customers');
    }

    public function test_create_displays_form()
    {
        $response = $this->actingAs($this->user)->get(route('customers.create'));

        $response->assertStatus(200);
        $response->assertViewIs('customers.create');
    }

    public function test_store_saves_new_customer()
    {
        $data = [
            'nama_customer' => 'PT Makmur Jaya',
            'nomor_telepon' => '08123456789',
            'alamat' => 'Jl. Sudirman No 1',
        ];

        $response = $this->actingAs($this->user)->post(route('customers.store'), $data);

        $response->assertRedirect(route('customers.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('customers', [
            'nama_customer' => 'PT Makmur Jaya',
            'nomor_telepon' => '08123456789',
        ]);
    }

    public function test_edit_displays_form_with_customer_data()
    {
        $customer = Customer::factory()->create();

        $response = $this->actingAs($this->user)->get(route('customers.edit', $customer->id_customer));

        $response->assertStatus(200);
        $response->assertViewIs('customers.edit');
        $response->assertViewHas('customer');
    }

    public function test_update_modifies_existing_customer()
    {
        $customer = Customer::factory()->create([
            'nama_customer' => 'Toko Lama',
        ]);

        $data = [
            'nama_customer' => 'Toko Baru',
            'nomor_telepon' => $customer->nomor_telepon,
            'status_aktif' => 1,
        ];

        $response = $this->actingAs($this->user)->put(route('customers.update', $customer->id_customer), $data);

        $response->assertRedirect(route('customers.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('customers', [
            'id_customer' => $customer->id_customer,
            'nama_customer' => 'Toko Baru',
        ]);
    }

    public function test_nonaktifkan_changes_status_aktif()
    {
        $customer = Customer::factory()->create(['status_aktif' => 1]);

        $response = $this->actingAs($this->user)->patch(route('customers.nonaktifkan', $customer->id_customer));

        $response->assertRedirect(route('customers.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('customers', [
            'id_customer' => $customer->id_customer,
            'status_aktif' => 0,
        ]);
    }
}
