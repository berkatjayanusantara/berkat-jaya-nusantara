<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\StockOpname;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockOpnameControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_index_displays_stock_opname_list()
    {
        $response = $this->actingAs($this->user)->get(route('stock-opname.index'));

        $response->assertStatus(200);
        $response->assertViewIs('stock-opname.index');
        $response->assertViewHas('barang');
    }

    public function test_create_displays_form()
    {
        $response = $this->actingAs($this->user)->get(route('stock-opname.create'));

        $response->assertStatus(200);
        $response->assertViewIs('stock-opname.index');
        $response->assertViewHas('barang');
    }
}
