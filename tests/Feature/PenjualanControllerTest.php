<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\Customer;
use App\Models\Penjualan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PenjualanControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_index_displays_penjualan_list()
    {
        $response = $this->actingAs($this->user)->get(route('penjualan.index'));

        $response->assertStatus(200);
        $response->assertViewIs('penjualan.index');
        $response->assertViewHas('penjualan');
    }

    public function test_create_displays_form()
    {
        $response = $this->actingAs($this->user)->get(route('penjualan.create'));

        $response->assertStatus(200);
        $response->assertViewIs('penjualan.create');
        $response->assertViewHas('customers');
        $response->assertViewHas('barang');
    }

    public function test_store_saves_new_penjualan_tunai()
    {
        $customer = Customer::factory()->create();
        $barang = Barang::factory()->create([
            'harga_jual_default' => 50000,
            'stok_saat_ini' => 100,
            'jenis_ppn' => 'non_ppn'
        ]);

        $data = [
            'tanggal_penjualan' => '2023-10-01',
            'nomor_invoice' => 'INV-001',
            'id_customer' => $customer->id_customer,
            'metode_pembayaran' => 'tunai',
            'mode_ppn' => 'tanpa_ppn',
            'jenis_penyesuaian_total' => 'tidak_ada',
            'id_barang' => [$barang->id_barang],
            'jumlah' => [2],
            'harga_jual' => [50000],
            'diskon_nominal' => [0],
        ];

        $response = $this->actingAs($this->user)->post(route('penjualan.store'), $data);

        $response->assertRedirect(route('penjualan.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('penjualan', [
            'id_customer' => $customer->id_customer,
            'metode_pembayaran' => 'tunai',
            'status_pembayaran' => 'lunas',
        ]);
        $this->assertDatabaseHas('detail_penjualan', [
            'id_barang' => $barang->id_barang,
            'jumlah' => 2,
            'harga_jual' => 50000,
        ]);
    }

    public function test_store_saves_new_penjualan_kredit()
    {
        $customer = Customer::factory()->create();
        $barang = Barang::factory()->create([
            'harga_jual_default' => 50000,
            'stok_saat_ini' => 100,
            'jenis_ppn' => 'non_ppn'
        ]);

        $data = [
            'tanggal_penjualan' => '2023-10-01',
            'nomor_invoice' => 'INV-002',
            'id_customer' => $customer->id_customer,
            'metode_pembayaran' => 'kredit',
            'tanggal_jatuh_tempo' => '2023-11-01',
            'mode_ppn' => 'tanpa_ppn',
            'jenis_penyesuaian_total' => 'tidak_ada',
            'id_barang' => [$barang->id_barang],
            'jumlah' => [2],
            'harga_jual' => [50000],
            'diskon_nominal' => [0],
        ];

        $response = $this->actingAs($this->user)->post(route('penjualan.store'), $data);

        $response->assertRedirect(route('penjualan.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('penjualan', [
            'id_customer' => $customer->id_customer,
            'metode_pembayaran' => 'kredit',
            'status_pembayaran' => 'belum_lunas',
        ]);
        $this->assertDatabaseHas('piutang', [
            'id_customer' => $customer->id_customer,
        ]);
    }

    public function test_show_displays_penjualan_details()
    {
        // Karena ada relasi yang kompleks, lebih mudah membuat penjualan via endpoint store atau factory komprehensif
        $penjualan = Penjualan::factory()->create();

        $response = $this->actingAs($this->user)->get(route('penjualan.show', $penjualan->id_penjualan));

        $response->assertStatus(200);
        $response->assertViewIs('penjualan.show');
        $response->assertViewHas('penjualan');
    }
}
