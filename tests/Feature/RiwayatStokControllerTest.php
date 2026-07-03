<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\RiwayatStok;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RiwayatStokControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_index_displays_riwayat_stok_list()
    {
        $response = $this->actingAs($this->user)->get(route('riwayat-stok.index'));

        $response->assertStatus(200);
        $response->assertViewIs('riwayat-stok.index');
        $response->assertViewHas('riwayatStok');
    }
}
