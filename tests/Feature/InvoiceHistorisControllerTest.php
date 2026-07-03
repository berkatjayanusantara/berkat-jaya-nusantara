<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\InvoiceHistoris;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceHistorisControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_index_displays_invoice_historis_list()
    {
        $response = $this->actingAs($this->user)->get(route('invoice-historis.index'));

        $response->assertStatus(200);
        $response->assertViewIs('invoice-historis.index');
        $response->assertViewHas('pembelianHistoris');
        $response->assertViewHas('penjualanHistoris');
    }

    public function test_create_pembelian_displays_form()
    {
        $response = $this->actingAs($this->user)->get(route('invoice-historis.pembelian.create'));

        $response->assertStatus(200);
        $response->assertViewIs('invoice-historis.create-pembelian');
    }

    public function test_create_penjualan_displays_form()
    {
        $response = $this->actingAs($this->user)->get(route('invoice-historis.penjualan.create'));

        $response->assertStatus(200);
        $response->assertViewIs('invoice-historis.create-penjualan');
    }
}
