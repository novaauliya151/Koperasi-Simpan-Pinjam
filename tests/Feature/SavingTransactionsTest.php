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

class SavingTransactionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_record_a_savings_deposit_and_activity_log_atomically(): void
    {
        [$staff, $member, $type] = $this->prepareDeposit();

        $this->actingAs($staff)->post('/petugas/simpanan', $this->depositData($member, $type))
            ->assertRedirect('/petugas/simpanan');

        $transaction = TransaksiSimpanan::firstOrFail();
        $this->assertSame('tercatat', $transaction->status);
        $this->assertSame('Simpanan Sukarela', $transaction->jenis_simpanan_nama_snapshot);
        $this->assertSame('100000.00', $transaction->nominal);
        $this->assertDatabaseHas('log_aktivitas', [
            'aksi' => 'savings.recorded',
            'subjek_id' => $transaction->id,
        ]);
    }

    public function test_required_savings_needs_a_period_and_amount_must_be_positive(): void
    {
        [$staff, $member, $type] = $this->prepareDeposit('WAJIB');
        $data = $this->depositData($member, $type);

        $this->actingAs($staff)->post('/petugas/simpanan', array_diff_key($data, ['periode' => true]))
            ->assertSessionHasErrors('periode');

        $data['nominal'] = '-1';
        $data['periode'] = now()->format('Y-m');
        $this->actingAs($staff)->post('/petugas/simpanan', $data)
            ->assertSessionHasErrors('nominal');

        $this->assertDatabaseCount('transaksi_simpanans', 0);
    }

    public function test_inactive_member_cannot_receive_a_new_deposit(): void
    {
        [$staff, $member, $type] = $this->prepareDeposit();
        $member->update(['status_keanggotaan' => 'keluar']);

        $this->actingAs($staff)->post('/petugas/simpanan', $this->depositData($member, $type))
            ->assertSessionHasErrors('anggota_id');

        $this->assertDatabaseCount('transaksi_simpanans', 0);
    }

    public function test_repeated_submission_with_the_same_key_does_not_duplicate_a_deposit(): void
    {
        [$staff, $member, $type] = $this->prepareDeposit();
        $data = $this->depositData($member, $type);

        $this->actingAs($staff)->post('/petugas/simpanan', $data)
            ->assertRedirect('/petugas/simpanan');
        $this->post('/petugas/simpanan', $data)
            ->assertSessionHasErrors('idempotency_key');

        $this->assertDatabaseCount('transaksi_simpanans', 1);
        $this->assertDatabaseCount('log_aktivitas', 1);
    }

    public function test_member_can_only_see_their_own_savings_and_balances(): void
    {
        [$staff, $member, $type] = $this->prepareDeposit();
        $otherMember = $this->createMember('ANG-002', 'Anggota Lain');
        $this->actingAs($staff)->post('/petugas/simpanan', $this->depositData($member, $type));
        $otherData = $this->depositData($otherMember, $type);
        $otherData['idempotency_key'] = (string) Str::uuid();
        $otherData['nominal'] = '75000';
        $this->post('/petugas/simpanan', $otherData);

        $this->actingAs($member->user)->get('/anggota/simpanan')
            ->assertOk()
            ->assertSee('100.000')
            ->assertDontSee('75.000')
            ->assertDontSee($otherMember->nomor_anggota);
    }

    public function test_routes_are_limited_to_their_roles(): void
    {
        $staff = $this->createUser('petugas', 'petugas@example.test');
        $member = $this->createUser('anggota', 'anggota@example.test');

        $this->actingAs($staff)->get('/anggota/simpanan')->assertForbidden();
        $this->actingAs($member)->get('/petugas/simpanan')->assertForbidden();
        $this->actingAs($member)->post('/petugas/simpanan')->assertForbidden();
    }

    private function prepareDeposit(string $code = 'SUKARELA'): array
    {
        $staff = $this->createUser('petugas', 'petugas@example.test');
        $member = $this->createMember('ANG-001', 'Anggota Uji', true);
        $type = JenisSimpanan::create([
            'kode' => $code,
            'nama' => $code === 'WAJIB' ? 'Simpanan Wajib' : 'Simpanan Sukarela',
            'is_active' => true,
        ]);
        KetentuanSimpanan::create([
            'jenis_simpanan_id' => $type->id,
            'nominal_ketentuan' => '25000.00',
            'berlaku_mulai' => now()->toDateString(),
            'dibuat_oleh' => $staff->id,
        ]);

        return [$staff, $member, $type];
    }

    private function depositData(Anggota $member, JenisSimpanan $type): array
    {
        return [
            'idempotency_key' => (string) Str::uuid(),
            'anggota_id' => $member->id,
            'jenis_simpanan_id' => $type->id,
            'tanggal_transaksi' => now()->toDateString(),
            'periode' => $type->kode === 'WAJIB' ? now()->format('Y-m') : null,
            'nominal' => '100000.00',
            'metode' => 'tunai',
            'keterangan' => 'Setoran uji',
        ];
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
