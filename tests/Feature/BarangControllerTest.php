<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BarangControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_index_displays_barang_list()
    {
        Barang::factory()->count(3)->create();

        $response = $this->actingAs($this->user)->get(route('barang.index'));

        $response->assertStatus(200);
        $response->assertViewIs('barang.index');
        $response->assertViewHas('barang');
    }

    public function test_create_displays_form()
    {
        $response = $this->actingAs($this->user)->get(route('barang.create'));

        $response->assertStatus(200);
        $response->assertViewIs('barang.create');
    }

    public function test_store_saves_new_barang()
    {
        $data = [
            'kode_barang' => 'BRG-9999',
            'nama_barang' => 'Semen Tiga Roda',
            'satuan' => 'sak',
            'harga_beli_terakhir' => 45000,
            'harga_jual_default' => 50000,
            'stok_saat_ini' => 100,
            'tipe_perhitungan_harga' => 'normal',
            'jenis_ppn' => 'non_ppn',
            'keterangan' => 'Semen abu-abu',
            'status_aktif' => 1,
        ];

        $response = $this->actingAs($this->user)->post(route('barang.store'), $data);

        $response->assertRedirect(route('barang.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('barang', [
            'nama_barang' => 'Semen Tiga Roda',
        ]);
    }

    public function test_edit_displays_form_with_barang_data()
    {
        $barang = Barang::factory()->create();

        $response = $this->actingAs($this->user)->get(route('barang.edit', $barang->id_barang));

        $response->assertStatus(200);
        $response->assertViewIs('barang.edit');
        $response->assertViewHas('barang');
    }

    public function test_update_modifies_existing_barang()
    {
        $barang = Barang::factory()->create([
            'nama_barang' => 'Besi Tua',
        ]);

        $data = [
            'kode_barang' => $barang->kode_barang,
            'nama_barang' => 'Besi Baru',
            'satuan' => $barang->satuan,
            'harga_beli_terakhir' => $barang->harga_beli_terakhir,
            'harga_jual_default' => 60000,
            'stok_saat_ini' => $barang->stok_saat_ini,
            'tipe_perhitungan_harga' => 'normal',
            'jenis_ppn' => 'non_ppn',
            'status_aktif' => 1,
        ];

        $response = $this->actingAs($this->user)->put(route('barang.update', $barang->id_barang), $data);

        $response->assertRedirect(route('barang.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('barang', [
            'id_barang' => $barang->id_barang,
            'nama_barang' => 'Besi Baru',
        ]);
    }

    public function test_nonaktifkan_changes_status_aktif()
    {
        $barang = Barang::factory()->create(['status_aktif' => 1]);

        $response = $this->actingAs($this->user)->patch(route('barang.nonaktifkan', $barang->id_barang));

        $response->assertRedirect(route('barang.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('barang', [
            'id_barang' => $barang->id_barang,
            'status_aktif' => 0,
        ]);
    }
}
