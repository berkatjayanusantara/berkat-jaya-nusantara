<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\SuratKeluar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KopSuratControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_index_displays_surat_keluar_list()
    {
        $response = $this->actingAs($this->user)->get(route('kop-surat.index'));

        $response->assertStatus(200);
        $response->assertViewIs('kop-surat.index');
        $response->assertViewHas('suratKeluar');
    }

    public function test_create_displays_form()
    {
        $response = $this->actingAs($this->user)->get(route('kop-surat.create'));

        $response->assertStatus(200);
        $response->assertViewIs('kop-surat.create');
    }

    public function test_store_saves_new_surat_keluar()
    {
        $this->withoutExceptionHandling();

        $data = [
            'nomor_surat' => 'SK-001',
            'jenis_surat' => 'penawaran',
            'tujuan' => 'PT Pelanggan Setia',
            'alamat_tujuan' => 'Jl. Merdeka No. 10',
            'tanggal_surat' => '2023-10-05',
            'perihal' => 'Penawaran Produk',
            'isi_surat' => 'Kami menawarkan produk...',
            'nama_penandatangan' => 'Budi Santoso',
            'jabatan_penandatangan' => 'Direktur',
            'kota_ttd' => 'Jakarta',
            'status_surat' => 'draft',
        ];

        $response = $this->actingAs($this->user)->post(route('kop-surat.store'), $data);

        $response->assertRedirect(route('kop-surat.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('surat_keluar', [
            'nomor_surat' => 'SK-001',
            'tujuan' => 'PT Pelanggan Setia',
        ]);
    }
}
