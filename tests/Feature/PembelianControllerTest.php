<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\Supplier;
use App\Models\Pembelian;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PembelianControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_index_displays_pembelian_list()
    {
        $response = $this->actingAs($this->user)->get(route('pembelian.index'));

        $response->assertStatus(200);
        $response->assertViewIs('pembelian.index');
        $response->assertViewHas('pembelian');
    }

    public function test_create_displays_form()
    {
        $response = $this->actingAs($this->user)->get(route('pembelian.create'));

        $response->assertStatus(200);
        $response->assertViewIs('pembelian.create');
        $response->assertViewHas('suppliers');
        $response->assertViewHas('barang');
    }

    public function test_store_saves_new_pembelian()
    {
        $this->withoutExceptionHandling();
        $supplier = Supplier::factory()->create();
        $barang = Barang::factory()->create([
            'stok_saat_ini' => 10,
        ]);

        $data = [
            'nomor_pembelian' => 'PO-001',
            'nomor_delivery_order' => 'DO-001',
            'nomor_surat_jalan' => 'SJ-001',
            'tanggal_pembelian' => '2023-10-01',
            'id_supplier' => $supplier->id_supplier,
            'nilai_pajak' => 0,
            'biaya_lain' => 0,
            'potongan_diskon' => 0,
            'id_barang' => [$barang->id_barang],
            'jumlah_dipesan' => [10],
            'jumlah' => [10], // Diterima
            'harga_beli' => [40000],
        ];

        $response = $this->actingAs($this->user)->post(route('pembelian.store'), $data);

        $response->assertRedirect(route('pembelian.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('pembelian', [
            'id_supplier' => $supplier->id_supplier,
            'nomor_dokumen_asli' => 'PO-001',
        ]);
        $this->assertDatabaseHas('detail_pembelian', [
            'id_barang' => $barang->id_barang,
            'jumlah' => 10,
            'harga_beli' => 40000,
        ]);
    }

    public function test_show_displays_pembelian_details()
    {
        $pembelian = Pembelian::factory()->create();

        $response = $this->actingAs($this->user)->get(route('pembelian.show', $pembelian->id_pembelian));

        $response->assertStatus(200);
        $response->assertViewIs('pembelian.show');
        $response->assertViewHas('pembelian');
    }
}
