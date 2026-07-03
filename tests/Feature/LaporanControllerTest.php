<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaporanControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_laporan_penjualan_displays_correctly()
    {
        $response = $this->actingAs($this->user)->get(route('laporan.penjualan'));

        $response->assertStatus(200);
        $response->assertViewIs('laporan.penjualan');
        $response->assertViewHas('penjualan');
    }

    public function test_laporan_pembelian_displays_correctly()
    {
        $response = $this->actingAs($this->user)->get(route('laporan.pembelian'));

        $response->assertStatus(200);
        $response->assertViewIs('laporan.pembelian');
        $response->assertViewHas('pembelian');
    }

    public function test_laporan_piutang_displays_correctly()
    {
        $response = $this->actingAs($this->user)->get(route('laporan.piutang'));

        $response->assertStatus(200);
        $response->assertViewIs('laporan.piutang');
        $response->assertViewHas('piutang');
    }

    public function test_laporan_stok_displays_correctly()
    {
        $response = $this->actingAs($this->user)->get(route('laporan.stokBarang'));

        $response->assertStatus(200);
        $response->assertViewIs('laporan.stok-barang');
        $response->assertViewHas('barang');
    }
}
