<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\PembayaranPiutang;
use App\Models\Penjualan;
use App\Models\Piutang;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PiutangTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        /** @var User $user */
        $user = User::factory()->create([
            'nama_user' => 'Admin Piutang',
            'username' => 'admin_piutang',
            'email' => 'admin_piutang@example.com',
            'password' => bcrypt('password'),
            'status_aktif' => true,
        ]);

        return $user;
    }

    private function customer(): Customer
    {
        /** @var Customer $customer */
        $customer = Customer::factory()->create([
            'kode_customer' => 'CUS-0001',
            'nama_customer' => 'Customer Piutang',
            'nomor_telepon' => '081234567890',
            'status_aktif' => true,
        ]);

        return $customer;
    }

    private function penjualanKredit(Customer $customer, array $override = []): Penjualan
    {
        /** @var Penjualan $penjualan */
        $penjualan = Penjualan::factory()->kredit()->create(array_merge([
            'nomor_invoice' => 'INV-' . now()->format('Ymd') . '-0001',
            'tanggal_penjualan' => now()->toDateString(),
            'id_customer' => $customer->id_customer,
            'subtotal' => 100000,
            'persentase_pajak' => 0,
            'nilai_pajak' => 0,
            'pajak_ditambahkan' => false,
            'total_akhir' => 100000,
            'metode_pembayaran' => 'kredit',
            'status_pembayaran' => 'belum_lunas',
            'tanggal_jatuh_tempo' => now()->addDays(14)->toDateString(),
            'dibuat_oleh' => $this->admin()->id_user,
        ], $override));

        return $penjualan;
    }

    private function piutang(Customer $customer, Penjualan $penjualan, array $override = []): Piutang
    {
        /** @var Piutang $piutang */
        $piutang = Piutang::factory()->create(array_merge([
            'id_penjualan' => $penjualan->id_penjualan,
            'nomor_invoice' => $penjualan->nomor_invoice,
            'id_customer' => $customer->id_customer,
            'total_piutang' => 100000,
            'total_dibayar' => 0,
            'sisa_piutang' => 100000,
            'tanggal_jatuh_tempo' => now()->addDays(14)->toDateString(),
            'status_piutang' => 'belum_lunas',
            'catatan' => 'Piutang testing',
        ], $override));

        return $piutang;
    }

    public function test_guest_can_not_access_piutang_page(): void
    {
        $response = $this->get('/piutang');

        $response->assertRedirect('/login');
    }

    public function test_admin_can_open_piutang_index_page(): void
    {
        $response = $this->actingAs($this->admin())->get('/piutang');

        $response->assertStatus(200);
        $response->assertSee('Piutang', false);
    }

    public function test_admin_can_open_piutang_detail_page(): void
    {
        $user = $this->admin();
        $customer = $this->customer();

        $penjualan = Penjualan::factory()->kredit()->create([
            'nomor_invoice' => 'INV-' . now()->format('Ymd') . '-0001',
            'id_customer' => $customer->id_customer,
            'dibuat_oleh' => $user->id_user,
        ]);

        $piutang = $this->piutang($customer, $penjualan);

        $response = $this->actingAs($user)->get('/piutang/' . $piutang->id_piutang);

        $response->assertStatus(200);
        $response->assertSee($piutang->nomor_invoice, false);
    }

    public function test_admin_can_open_bayar_piutang_page(): void
    {
        $user = $this->admin();
        $customer = $this->customer();

        $penjualan = Penjualan::factory()->kredit()->create([
            'nomor_invoice' => 'INV-' . now()->format('Ymd') . '-0001',
            'id_customer' => $customer->id_customer,
            'dibuat_oleh' => $user->id_user,
            'status_pembayaran' => 'belum_lunas',
        ]);

        $piutang = $this->piutang($customer, $penjualan);

        $response = $this->actingAs($user)->get('/piutang/' . $piutang->id_piutang . '/bayar');

        $response->assertStatus(200);
        $response->assertSee($piutang->nomor_invoice, false);
    }

    public function test_admin_can_pay_partial_piutang(): void
    {
        $user = $this->admin();
        $customer = $this->customer();

        $penjualan = Penjualan::factory()->kredit()->create([
            'nomor_invoice' => 'INV-' . now()->format('Ymd') . '-0001',
            'id_customer' => $customer->id_customer,
            'dibuat_oleh' => $user->id_user,
            'status_pembayaran' => 'belum_lunas',
        ]);

        $piutang = $this->piutang($customer, $penjualan, [
            'total_piutang' => 100000,
            'total_dibayar' => 0,
            'sisa_piutang' => 100000,
            'status_piutang' => 'belum_lunas',
        ]);

        $response = $this->actingAs($user)->post('/piutang/' . $piutang->id_piutang . '/bayar', [
            'tanggal_pembayaran' => now()->toDateString(),
            'nominal_pembayaran' => 40000,
            'metode_pembayaran' => 'tunai',
            'catatan' => 'Bayar sebagian',
            'back_url' => route('piutang.index', absolute: false),
        ]);

        $response->assertRedirect(route('piutang.show', [
            'piutang' => $piutang->id_piutang,
            'back_url' => route('piutang.index', absolute: false),
        ], false));

        $this->assertDatabaseHas('pembayaran_piutang', [
            'id_piutang' => $piutang->id_piutang,
            'nominal_pembayaran' => 40000,
            'metode_pembayaran' => 'tunai',
            'dibuat_oleh' => $user->id_user,
        ]);

        $this->assertDatabaseHas('piutang', [
            'id_piutang' => $piutang->id_piutang,
            'total_piutang' => 100000,
            'total_dibayar' => 40000,
            'sisa_piutang' => 60000,
            'status_piutang' => 'sebagian_dibayar',
        ]);

        $this->assertDatabaseHas('penjualan', [
            'id_penjualan' => $penjualan->id_penjualan,
            'status_pembayaran' => 'sebagian',
        ]);
    }

    public function test_admin_can_fully_pay_piutang(): void
    {
        $user = $this->admin();
        $customer = $this->customer();

        $penjualan = Penjualan::factory()->kredit()->create([
            'nomor_invoice' => 'INV-' . now()->format('Ymd') . '-0001',
            'id_customer' => $customer->id_customer,
            'dibuat_oleh' => $user->id_user,
            'status_pembayaran' => 'belum_lunas',
        ]);

        $piutang = $this->piutang($customer, $penjualan, [
            'total_piutang' => 100000,
            'total_dibayar' => 20000,
            'sisa_piutang' => 80000,
            'status_piutang' => 'sebagian_dibayar',
        ]);

        $response = $this->actingAs($user)->post('/piutang/' . $piutang->id_piutang . '/bayar', [
            'tanggal_pembayaran' => now()->toDateString(),
            'nominal_pembayaran' => 80000,
            'metode_pembayaran' => 'transfer',
            'catatan' => 'Pelunasan',
            'back_url' => route('piutang.index', absolute: false),
        ]);

        $response->assertRedirect(route('piutang.show', [
            'piutang' => $piutang->id_piutang,
            'back_url' => route('piutang.index', absolute: false),
        ], false));

        $this->assertDatabaseHas('pembayaran_piutang', [
            'id_piutang' => $piutang->id_piutang,
            'nominal_pembayaran' => 80000,
            'metode_pembayaran' => 'transfer',
            'dibuat_oleh' => $user->id_user,
        ]);

        $this->assertDatabaseHas('piutang', [
            'id_piutang' => $piutang->id_piutang,
            'total_piutang' => 100000,
            'total_dibayar' => 100000,
            'sisa_piutang' => 0,
            'status_piutang' => 'lunas',
        ]);

        $this->assertDatabaseHas('penjualan', [
            'id_penjualan' => $penjualan->id_penjualan,
            'status_pembayaran' => 'lunas',
        ]);
    }

    public function test_payment_can_not_be_more_than_remaining_piutang(): void
    {
        $user = $this->admin();
        $customer = $this->customer();

        $penjualan = Penjualan::factory()->kredit()->create([
            'nomor_invoice' => 'INV-' . now()->format('Ymd') . '-0001',
            'id_customer' => $customer->id_customer,
            'dibuat_oleh' => $user->id_user,
        ]);

        $piutang = $this->piutang($customer, $penjualan, [
            'total_piutang' => 100000,
            'total_dibayar' => 40000,
            'sisa_piutang' => 60000,
            'status_piutang' => 'sebagian_dibayar',
        ]);

        $response = $this->actingAs($user)->from('/piutang/' . $piutang->id_piutang . '/bayar')
            ->post('/piutang/' . $piutang->id_piutang . '/bayar', [
                'tanggal_pembayaran' => now()->toDateString(),
                'nominal_pembayaran' => 70000,
                'metode_pembayaran' => 'tunai',
                'catatan' => 'Melebihi sisa piutang',
                'back_url' => route('piutang.index', absolute: false),
            ]);

        $response->assertRedirect('/piutang/' . $piutang->id_piutang . '/bayar');
        $response->assertSessionHasErrors('nominal_pembayaran');

        $this->assertDatabaseCount('pembayaran_piutang', 0);

        $this->assertDatabaseHas('piutang', [
            'id_piutang' => $piutang->id_piutang,
            'total_dibayar' => 40000,
            'sisa_piutang' => 60000,
            'status_piutang' => 'sebagian_dibayar',
        ]);
    }

    public function test_lunas_piutang_can_not_open_payment_page(): void
    {
        $user = $this->admin();
        $customer = $this->customer();

        $penjualan = Penjualan::factory()->kredit()->create([
            'nomor_invoice' => 'INV-' . now()->format('Ymd') . '-0001',
            'id_customer' => $customer->id_customer,
            'dibuat_oleh' => $user->id_user,
            'status_pembayaran' => 'lunas',
        ]);

        $piutang = $this->piutang($customer, $penjualan, [
            'total_piutang' => 100000,
            'total_dibayar' => 100000,
            'sisa_piutang' => 0,
            'status_piutang' => 'lunas',
        ]);

        $response = $this->actingAs($user)->get('/piutang/' . $piutang->id_piutang . '/bayar');

        $response->assertRedirect(route('piutang.show', [
            'piutang' => $piutang->id_piutang,
            'back_url' => route('piutang.index'),
        ]));

        $response->assertSessionHas('error', 'Piutang ini sudah lunas.');
    }

    public function test_admin_can_filter_piutang_by_status(): void
    {
        $user = $this->admin();
        $customer = $this->customer();

        $penjualanBelumLunas = Penjualan::factory()->kredit()->create([
            'nomor_invoice' => 'INV-' . now()->format('Ymd') . '-0001',
            'id_customer' => $customer->id_customer,
            'dibuat_oleh' => $user->id_user,
        ]);

        $penjualanLunas = Penjualan::factory()->kredit()->create([
            'nomor_invoice' => 'INV-' . now()->format('Ymd') . '-0002',
            'id_customer' => $customer->id_customer,
            'dibuat_oleh' => $user->id_user,
        ]);

        $this->piutang($customer, $penjualanBelumLunas, [
            'nomor_invoice' => 'INV-' . now()->format('Ymd') . '-0001',
            'status_piutang' => 'belum_lunas',
        ]);

        $this->piutang($customer, $penjualanLunas, [
            'nomor_invoice' => 'INV-' . now()->format('Ymd') . '-0002',
            'status_piutang' => 'lunas',
            'total_dibayar' => 100000,
            'sisa_piutang' => 0,
        ]);

        $response = $this->actingAs($user)->get('/piutang?status=belum_lunas');

        $response->assertStatus(200);
        $response->assertSee('INV-' . now()->format('Ymd') . '-0001', false);
        $response->assertDontSee('INV-' . now()->format('Ymd') . '-0002', false);
    }
}
