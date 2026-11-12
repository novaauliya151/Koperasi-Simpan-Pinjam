<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_search_and_filter_members(): void
    {
        $staff = $this->createUser('petugas', 'petugas@example.test');
        Anggota::create([
            'nomor_anggota' => 'ANG-001',
            'nama' => 'Siti Contoh',
            'status_keanggotaan' => 'aktif',
        ]);
        Anggota::create([
            'nomor_anggota' => 'ANG-002',
            'nama' => 'Budi Contoh',
            'status_keanggotaan' => 'menunggu_verifikasi',
        ]);

        $this->actingAs($staff)
            ->get('/petugas/anggota?q=ANG-001&status=aktif')
            ->assertOk()
            ->assertSee('Siti Contoh')
            ->assertDontSee('Budi Contoh');
    }

    public function test_staff_creates_member_pending_verification_and_rejects_duplicate_number(): void
    {
        $staff = $this->createUser('petugas', 'petugas@example.test');
        $payload = [
            'nomor_anggota' => 'ANG-003',
            'nama' => 'Anggota Baru',
            'telepon' => '081234567890',
            'alamat' => 'Alamat fiktif',
            'tanggal_bergabung' => '2026-11-12',
        ];

        $this->actingAs($staff)
            ->post('/petugas/anggota', $payload)
            ->assertRedirect();

        $this->assertDatabaseHas('anggotas', [
            'nomor_anggota' => 'ANG-003',
            'status_keanggotaan' => 'menunggu_verifikasi',
        ]);

        $this->actingAs($staff)
            ->from('/petugas/anggota/create')
            ->post('/petugas/anggota', $payload)
            ->assertRedirect('/petugas/anggota/create')
            ->assertSessionHasErrors('nomor_anggota');
    }

    public function test_staff_can_verify_member_without_disabling_linked_account_or_deleting_history(): void
    {
        $staff = $this->createUser('petugas', 'petugas@example.test');
        $account = $this->createUser('anggota', 'anggota@example.test');
        $member = Anggota::create([
            'nomor_anggota' => 'ANG-004',
            'nama' => 'Anggota Terhubung',
            'status_keanggotaan' => 'aktif',
            'user_id' => $account->id,
        ]);

        $this->actingAs($staff)
            ->patch('/petugas/anggota/'.$member->id.'/status', [
                'status_keanggotaan' => 'tidak_aktif',
            ])
            ->assertRedirect('/petugas/anggota');

        $this->assertDatabaseHas('anggotas', [
            'id' => $member->id,
            'status_keanggotaan' => 'tidak_aktif',
            'user_id' => $account->id,
        ]);
        $this->assertTrue($account->fresh()->is_active);
        $this->assertDatabaseHas('log_aktivitas', [
            'aksi' => 'member.status_changed',
            'subjek_id' => $member->id,
        ]);
    }

    public function test_only_staff_can_manage_member_records(): void
    {
        $admin = $this->createUser('admin', 'admin@example.test');

        $this->actingAs($admin)
            ->get('/petugas/anggota')
            ->assertForbidden();
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

