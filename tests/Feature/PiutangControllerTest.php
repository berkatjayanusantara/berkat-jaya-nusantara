<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Penjualan;
use App\Models\Piutang;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PiutangControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_index_displays_piutang_list()
    {
        Piutang::factory()->count(3)->create();

        $response = $this->actingAs($this->user)->get(route('piutang.index'));

        $response->assertStatus(200);
        $response->assertViewIs('piutang.index');
        $response->assertViewHas('piutang');
    }

    public function test_show_displays_piutang_details()
    {
        $piutang = Piutang::factory()->create();

        $response = $this->actingAs($this->user)->get(route('piutang.show', $piutang->id_piutang));

        $response->assertStatus(200);
        $response->assertViewIs('piutang.show');
        $response->assertViewHas('piutang');
    }

    public function test_bayar_displays_pembayaran_form()
    {
        $piutang = Piutang::factory()->create([
            'total_piutang' => 100000,
            'total_dibayar' => 0,
            'sisa_piutang' => 100000,
            'status_piutang' => 'belum_lunas'
        ]);

        $response = $this->actingAs($this->user)->get(route('piutang.bayar', $piutang->id_piutang));

        $response->assertStatus(200);
        $response->assertViewIs('piutang.bayar');
        $response->assertViewHas('piutang');
    }

    public function test_simpan_pembayaran_saves_new_pembayaran()
    {
        $this->withoutExceptionHandling();
        $piutang = Piutang::factory()->create([
            'total_piutang' => 100000,
            'total_dibayar' => 0,
            'sisa_piutang' => 100000,
            'status_piutang' => 'belum_lunas'
        ]);

        $data = [
            'tanggal_pembayaran' => '2023-10-02',
            'nominal_pembayaran' => 50000,
            'metode_pembayaran' => 'transfer',
            'keterangan' => 'Cicilan 1',
        ];

        $response = $this->actingAs($this->user)->post(route('piutang.simpanPembayaran', $piutang->id_piutang), $data);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('pembayaran_piutang', [
            'id_piutang' => $piutang->id_piutang,
            'nominal_pembayaran' => 50000,
        ]);
        
        $piutang->refresh();
        $this->assertEquals(50000, $piutang->sisa_piutang);
    }
}
