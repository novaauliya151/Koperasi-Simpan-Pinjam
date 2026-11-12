<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\JenisSimpanan;
use App\Models\KetentuanSimpanan;
use App\Models\LogAktivitas;
use App\Models\TransaksiSimpanan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SavingsReversalsTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_cancel_a_deposit_and_balance_returns_to_zero(): void
    {
        [$staff, $member, $transaction] = $this->recordDeposit();

        $this->actingAs($staff)->post(
            '/petugas/simpanan/'.$transaction->id.'/pembatalan',
            ['alasan_koreksi' => 'Setoran dicatat dua kali.'],
        )->assertRedirect('/petugas/simpanan');

        $this->assertDatabaseHas('transaksi_simpanans', [
            'id' => $transaction->id,
            'status' => 'dibatalkan',
            'alasan_koreksi' => 'Setoran dicatat dua kali.',
        ]);
        $this->assertDatabaseHas('transaksi_simpanans', [
            'pembalikan_dari_id' => $transaction->id,
            'status' => 'pembalikan',
            'nominal' => '125000.00',
        ]);
        $this->assertDatabaseHas('log_aktivitas', [
            'aksi' => 'savings.reversed',
            'subjek_id' => $transaction->id,
            'alasan' => 'Setoran dicatat dua kali.',
        ]);

        $this->actingAs($member->user)->get('/anggota/simpanan')
            ->assertOk()
            ->assertSee('Rp 0');
    }

    public function test_cancellation_requires_a_reason_and_cannot_be_repeated(): void
    {
        [$staff, , $transaction] = $this->recordDeposit();
        $url = '/petugas/simpanan/'.$transaction->id.'/pembatalan';

        $this->actingAs($staff)->post($url, ['alasan_koreksi' => ''])
            ->assertSessionHasErrors('alasan_koreksi');
        $this->assertSame('tercatat', $transaction->fresh()->status);

        $this->post($url, ['alasan_koreksi' => 'Koreksi nominal.'])
            ->assertRedirect('/petugas/simpanan');
        $this->post($url, ['alasan_koreksi' => 'Percobaan ulang.'])
            ->assertSessionHasErrors('transaksi');

        $this->assertDatabaseCount('transaksi_simpanans', 2);
        $this->assertDatabaseCount('log_aktivitas', 2);
    }

    public function test_receipt_is_available_to_staff_but_member_cannot_read_another_members_receipt(): void
    {
        [$staff, $member, $transaction] = $this->recordDeposit();
        $otherMember = $this->createMember('ANG-002', 'Anggota Lain', true);

        $this->actingAs($staff)
            ->get('/petugas/simpanan/'.$transaction->id.'/bukti')
            ->assertOk()
            ->assertSee($transaction->nomor_transaksi)
            ->assertSee($member->nama)
            ->assertSee('Cetak bukti');

        $this->actingAs($member->user)
            ->get('/anggota/simpanan/'.$transaction->id.'/bukti')
            ->assertOk()
            ->assertSee($transaction->nomor_transaksi);

        $otherDeposit = $this->makeTransaction($staff, $otherMember, $transaction->jenis);
        $this->actingAs($member->user)
            ->get('/anggota/simpanan/'.$otherDeposit->id.'/bukti')
            ->assertNotFound();
    }

    public function test_reversal_and_original_are_both_visible_in_the_member_history(): void
    {
        [$staff, $member, $transaction] = $this->recordDeposit();
        $this->actingAs($staff)->post(
            '/petugas/simpanan/'.$transaction->id.'/pembatalan',
            ['alasan_koreksi' => 'Salah pilih anggota.'],
        );

        $this->actingAs($member->user)->get('/anggota/simpanan')
            ->assertOk()
            ->assertSee($transaction->nomor_transaksi)
            ->assertSee('Dibatalkan')
            ->assertSee('Pembalikan');
    }

    private function recordDeposit(): array
    {
        $staff = $this->createUser('petugas', 'petugas@example.test');
        $member = $this->createMember('ANG-001', 'Anggota Uji', true);
        $type = JenisSimpanan::create([
            'kode' => 'SUKARELA',
            'nama' => 'Simpanan Sukarela',
            'is_active' => true,
        ]);
        KetentuanSimpanan::create([
            'jenis_simpanan_id' => $type->id,
            'nominal_ketentuan' => '25000.00',
            'berlaku_mulai' => now()->toDateString(),
            'dibuat_oleh' => $staff->id,
        ]);
        $transaction = $this->makeTransaction($staff, $member, $type);

        return [$staff, $member, $transaction];
    }

    private function makeTransaction(
        User $staff,
        Anggota $member,
        JenisSimpanan $type,
    ): TransaksiSimpanan {
        return TransaksiSimpanan::create([
            'nomor_transaksi' => 'SS-'.Str::upper(Str::random(12)),
            'idempotency_key' => (string) Str::uuid(),
            'anggota_id' => $member->id,
            'jenis_simpanan_id' => $type->id,
            'tanggal_transaksi' => now()->toDateString(),
            'nominal' => '125000.00',
            'metode' => 'tunai',
            'status' => 'tercatat',
            'dibuat_oleh' => $staff->id,
            'jenis_simpanan_kode_snapshot' => $type->kode,
            'jenis_simpanan_nama_snapshot' => $type->nama,
        ]);
    }

    private function createMember(string $number, string $name, bool $linked = false): Anggota
    {
        $user = $linked ? $this->createUser('anggota', 'anggota@example.test') : null;

        return Anggota::create([
            'nomor_anggota' => $number,
            'nama' => $name,
            'tanggal_bergabung' => now()->toDateString(),
            'status_keanggotaan' => 'aktif',
            'user_id' => $user?->id,
        ]);
    }

    private function createUser(string $role, string $email): User
    {
        return User::create([
            'name' => 'Pengguna Uji',
            'email' => $email,
            'password' => 'password-rahasia',
            'role' => $role,
            'is_active' => true,
        ]);
    }
}
