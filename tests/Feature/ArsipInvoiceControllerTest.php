<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Penjualan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArsipInvoiceControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_index_displays_arsip_invoice_list()
    {
        $response = $this->actingAs($this->user)->get(route('arsip-invoice.index'));

        $response->assertStatus(200);
        $response->assertViewIs('arsip-invoice.index');
        $response->assertViewHas('arsipInvoice');
    }
}
