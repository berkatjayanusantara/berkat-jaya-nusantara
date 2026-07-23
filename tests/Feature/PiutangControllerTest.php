<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\PembayaranPiutang;
use App\Models\Penjualan;
use App\Models\Piutang;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PiutangControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    /**
     * Buat Piutang dengan state yang dikontrol penuh.
     * Piutang wajib punya id_penjualan yang valid agar relasi tidak null.
     */
    private function buatPiutang(float $total, float $dibayar = 0, string $status = 'belum_lunas'): Piutang
    {
        $customer  = Customer::factory()->create();
        $penjualan = Penjualan::factory()->kredit()->create([
            'id_customer' => $customer->id_customer,
            'total_akhir' => $total,
        ]);

        return Piutang::create([
            'id_penjualan'       => $penjualan->id_penjualan,
            'nomor_invoice'      => $penjualan->nomor_invoice,
            'id_customer'        => $customer->id_customer,
            'total_piutang'      => $total,
            'total_dibayar'      => $dibayar,
            'sisa_piutang'       => $total - $dibayar,
            'tanggal_jatuh_tempo' => now()->addDays(30)->toDateString(),
            'status_piutang'     => $status,
        ]);
    }

    // =========================================================================
    // 1. Halaman listing & form
    // =========================================================================

    public function test_index_displays_piutang_list(): void
    {
        $this->buatPiutang(500000);
        $this->buatPiutang(300000);

        $this->actingAs($this->user)
            ->get(route('piutang.index'))
            ->assertOk()
            ->assertViewIs('piutang.index')
            ->assertViewHas('piutang');
    }

    public function test_show_displays_piutang_details(): void
    {
        $piutang = $this->buatPiutang(200000);

        $this->actingAs($this->user)
            ->get(route('piutang.show', $piutang->id_piutang))
            ->assertOk()
            ->assertViewIs('piutang.show')
            ->assertViewHas('piutang');
    }

    public function test_bayar_form_tampil_jika_belum_lunas(): void
    {
        $piutang = $this->buatPiutang(100000);

        $this->actingAs($this->user)
            ->get(route('piutang.bayar', $piutang->id_piutang))
            ->assertOk()
            ->assertViewIs('piutang.bayar')
            ->assertViewHas('piutang');
    }

    public function test_bayar_form_redirect_jika_sudah_lunas(): void
    {
        $piutang = $this->buatPiutang(100000, 100000, 'lunas');

        $response = $this->actingAs($this->user)
            ->get(route('piutang.bayar', $piutang->id_piutang));

        $response->assertSessionHas('error');

        // Controller meredirect ke show piutang; abaikan query string back_url
        $location = $response->headers->get('Location') ?? '';
        $this->assertStringContainsString(
            '/piutang/' . $piutang->id_piutang,
            $location,
        );
    }

    // =========================================================================
    // 2. Bayar SEBAGIAN
    // total_piutang = 500.000, bayar 200.000
    // sisa_piutang baru = 500.000 - 200.000 = 300.000
    // status → sebagian_dibayar
    // =========================================================================

    public function test_simpan_pembayaran_sebagian_kurangi_sisa_piutang(): void
    {
        $piutang = $this->buatPiutang(500000);

        $payload = [
            'tanggal_pembayaran' => '2024-01-20',
            'nominal_pembayaran' => 200000,
            'metode_pembayaran'  => 'transfer',
            'catatan'            => 'Cicilan 1',
        ];

        $this->actingAs($this->user)
            ->post(route('piutang.simpanPembayaran', $piutang->id_piutang), $payload)
            ->assertSessionHas('success');

        // -- Pembayaran tersimpan di tabel pembayaran_piutang --
        $this->assertDatabaseHas('pembayaran_piutang', [
            'id_piutang'        => $piutang->id_piutang,
            'nominal_pembayaran' => 200000,
            'metode_pembayaran' => 'transfer',
            'dibuat_oleh'       => $this->user->id_user,
        ]);

        // -- Sisa piutang berkurang, total_dibayar bertambah --
        $piutang->refresh();
        $this->assertEquals(200000.00, (float) $piutang->total_dibayar);
        $this->assertEquals(300000.00, (float) $piutang->sisa_piutang);
        $this->assertEquals('sebagian_dibayar', $piutang->status_piutang);

        // -- Status penjualan ikut berubah --
        $this->assertDatabaseHas('penjualan', [
            'id_penjualan'    => $piutang->id_penjualan,
            'status_pembayaran' => 'sebagian',
        ]);
    }

    // =========================================================================
    // 3. Bayar LUNAS — sisa_piutang menjadi 0
    // total_piutang = 100.000, bayar 100.000 sekaligus
    // =========================================================================

    public function test_simpan_pembayaran_lunas_set_status_lunas(): void
    {
        $piutang = $this->buatPiutang(100000);

        $payload = [
            'tanggal_pembayaran' => '2024-01-21',
            'nominal_pembayaran' => 100000,
            'metode_pembayaran'  => 'tunai',
        ];

        $this->actingAs($this->user)
            ->post(route('piutang.simpanPembayaran', $piutang->id_piutang), $payload)
            ->assertSessionHas('success');

        $piutang->refresh();
        $this->assertEquals(100000.00, (float) $piutang->total_dibayar);
        $this->assertEquals(0.00,      (float) $piutang->sisa_piutang);
        $this->assertEquals('lunas',   $piutang->status_piutang);

        $this->assertDatabaseHas('penjualan', [
            'id_penjualan'    => $piutang->id_penjualan,
            'status_pembayaran' => 'lunas',
        ]);
    }

    // =========================================================================
    // 4. Dua cicilan berurutan — akumulasi sisa piutang harus tepat
    // total = 300.000 → cicil 100.000 → sisa 200.000 → cicil 200.000 → lunas
    // =========================================================================

    public function test_dua_cicilan_berurutan_akumulasi_benar(): void
    {
        $piutang = $this->buatPiutang(300000);

        // Cicilan 1
        $this->actingAs($this->user)
            ->post(route('piutang.simpanPembayaran', $piutang->id_piutang), [
                'tanggal_pembayaran' => '2024-01-20',
                'nominal_pembayaran' => 100000,
                'metode_pembayaran'  => 'transfer',
            ])
            ->assertSessionHas('success');

        $piutang->refresh();
        $this->assertEquals(200000.00, (float) $piutang->sisa_piutang);
        $this->assertEquals('sebagian_dibayar', $piutang->status_piutang);

        // Cicilan 2 — lunasi sisa
        $this->actingAs($this->user)
            ->post(route('piutang.simpanPembayaran', $piutang->id_piutang), [
                'tanggal_pembayaran' => '2024-02-01',
                'nominal_pembayaran' => 200000,
                'metode_pembayaran'  => 'giro',
            ])
            ->assertSessionHas('success');

        $piutang->refresh();
        $this->assertEquals(300000.00, (float) $piutang->total_dibayar);
        $this->assertEquals(0.00,      (float) $piutang->sisa_piutang);
        $this->assertEquals('lunas',   $piutang->status_piutang);

        $this->assertDatabaseCount('pembayaran_piutang', 2);
    }

    // =========================================================================
    // 5. Nominal bayar > sisa piutang → ditolak validasi
    // =========================================================================

    public function test_simpan_pembayaran_ditolak_jika_melebihi_sisa(): void
    {
        $piutang = $this->buatPiutang(100000);

        $payload = [
            'tanggal_pembayaran' => '2024-01-20',
            'nominal_pembayaran' => 999999, // jauh lebih besar dari sisa 100.000
            'metode_pembayaran'  => 'tunai',
        ];

        $this->actingAs($this->user)
            ->post(route('piutang.simpanPembayaran', $piutang->id_piutang), $payload)
            ->assertSessionHasErrors('nominal_pembayaran');

        // Tidak ada perubahan di DB
        $this->assertDatabaseCount('pembayaran_piutang', 0);

        $piutang->refresh();
        $this->assertEquals(0.00,      (float) $piutang->total_dibayar);
        $this->assertEquals(100000.00, (float) $piutang->sisa_piutang);
        $this->assertEquals('belum_lunas', $piutang->status_piutang);
    }

    // =========================================================================
    // 6. Edit pembayaran — sisa piutang dihitung ulang berdasarkan SUM aktual
    // total_piutang = 300.000, ada 2 bayar: 100.000 + 150.000 = 250.000
    // Edit bayar pertama dari 100.000 → 50.000
    // SUM baru = 50.000 + 150.000 = 200.000, sisa = 100.000
    // =========================================================================

    public function test_update_pembayaran_rekalkukasi_sisa_piutang(): void
    {
        $piutang = $this->buatPiutang(300000);

        // Bayar 1
        $this->actingAs($this->user)
            ->post(route('piutang.simpanPembayaran', $piutang->id_piutang), [
                'tanggal_pembayaran' => '2024-01-20',
                'nominal_pembayaran' => 100000,
                'metode_pembayaran'  => 'tunai',
            ]);

        // Bayar 2
        $this->actingAs($this->user)
            ->post(route('piutang.simpanPembayaran', $piutang->id_piutang), [
                'tanggal_pembayaran' => '2024-01-25',
                'nominal_pembayaran' => 150000,
                'metode_pembayaran'  => 'transfer',
            ]);

        $piutang->refresh();
        $this->assertEquals(250000.00, (float) $piutang->total_dibayar);

        // Edit bayar pertama: ubah 100.000 → 50.000
        $bayarPertama = PembayaranPiutang::where('id_piutang', $piutang->id_piutang)
            ->orderBy('id_pembayaran')
            ->first();

        $this->actingAs($this->user)
            ->put(route('piutang.updatePembayaran', [
                'piutang'           => $piutang->id_piutang,
                'pembayaranPiutang' => $bayarPertama->id_pembayaran,
            ]), [
                'tanggal_pembayaran' => '2024-01-20',
                'nominal_pembayaran' => 50000,  // diubah dari 100.000
                'metode_pembayaran'  => 'tunai',
            ])
            ->assertSessionHas('success');

        $piutang->refresh();
        // SUM baru: 50.000 + 150.000 = 200.000
        $this->assertEquals(200000.00, (float) $piutang->total_dibayar);
        $this->assertEquals(100000.00, (float) $piutang->sisa_piutang);
        $this->assertEquals('sebagian_dibayar', $piutang->status_piutang);
    }

    // =========================================================================
    // 7. Pembayaran dari piutang lain tidak boleh edit
    // =========================================================================

    public function test_update_pembayaran_dari_piutang_lain_return_404(): void
    {
        $piutang1 = $this->buatPiutang(100000);
        $piutang2 = $this->buatPiutang(200000);

        // Buat pembayaran untuk piutang2
        $this->actingAs($this->user)
            ->post(route('piutang.simpanPembayaran', $piutang2->id_piutang), [
                'tanggal_pembayaran' => '2024-01-20',
                'nominal_pembayaran' => 50000,
                'metode_pembayaran'  => 'tunai',
            ]);

        $bayarPiutang2 = PembayaranPiutang::where('id_piutang', $piutang2->id_piutang)->first();

        // Coba edit pembayaran piutang2 dari konteks piutang1 → 404
        $this->actingAs($this->user)
            ->put(route('piutang.updatePembayaran', [
                'piutang'           => $piutang1->id_piutang,
                'pembayaranPiutang' => $bayarPiutang2->id_pembayaran,
            ]), [
                'tanggal_pembayaran' => '2024-01-20',
                'nominal_pembayaran' => 50000,
                'metode_pembayaran'  => 'tunai',
            ])
            ->assertNotFound();
    }

    // =========================================================================
    // 8. Guest tidak bisa akses
    // =========================================================================

    public function test_guest_diredirect_ke_login(): void
    {
        $piutang = $this->buatPiutang(100000);

        $this->get(route('piutang.index'))->assertRedirect(route('login'));
        $this->get(route('piutang.show', $piutang->id_piutang))->assertRedirect(route('login'));
        $this->post(route('piutang.simpanPembayaran', $piutang->id_piutang), [])->assertRedirect(route('login'));
    }
}
