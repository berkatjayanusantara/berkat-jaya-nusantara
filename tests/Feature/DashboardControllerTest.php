<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_dashboard_displays_correctly()
    {
        $response = $this->actingAs($this->user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('dashboard');
        $response->assertViewHas('totalBarang');
        $response->assertViewHas('penjualanHariIni');
        $response->assertViewHas('pembelianHariIni');
        $response->assertViewHas('totalPiutang');
        $response->assertViewHas('totalPiutangBelumLunas');
    }
}
