<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AccountManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_search_and_filter_accounts(): void
    {
        $admin = $this->createUser('admin', 'admin@example.test');
        $this->createUser('petugas', 'petugas@example.test', 'Petugas Utama');
        $this->createUser('anggota', 'anggota@example.test', 'Anggota Demo', false);

        $this->actingAs($admin)
            ->get('/admin/akun?q=Petugas&role=petugas&status=active')
            ->assertOk()
            ->assertSee('Petugas Utama')
            ->assertDontSee('Anggota Demo');
    }

    public function test_admin_can_create_account_and_duplicate_email_is_rejected(): void
    {
        $admin = $this->createUser('admin', 'admin@example.test');

        $this->actingAs($admin)->post('/admin/akun', [
            'name' => 'Petugas Baru',
            'email' => 'baru@example.test',
            'role' => 'petugas',
            'password' => 'password-rahasia',
            'password_confirmation' => 'password-rahasia',
            'is_active' => '1',
        ])->assertRedirect('/admin/akun');

        $this->assertDatabaseHas('users', [
            'email' => 'baru@example.test',
            'role' => 'petugas',
        ]);

        $this->actingAs($admin)->from('/admin/akun/create')->post('/admin/akun', [
            'name' => 'Duplikat',
            'email' => 'baru@example.test',
            'role' => 'anggota',
            'password' => 'password-rahasia',
            'password_confirmation' => 'password-rahasia',
            'is_active' => '1',
        ])->assertRedirect('/admin/akun/create')->assertSessionHasErrors('email');
    }

    public function test_admin_can_change_role_disable_account_and_reset_password(): void
    {
        $admin = $this->createUser('admin', 'admin@example.test');
        $account = $this->createUser('anggota', 'anggota@example.test');

        $this->actingAs($admin)->put('/admin/akun/'.$account->id, [
            'name' => $account->name,
            'email' => $account->email,
            'role' => 'petugas',
            'is_active' => '0',
            'password' => 'password-baru',
            'password_confirmation' => 'password-baru',
        ])->assertRedirect('/admin/akun');

        $account->refresh();
        $this->assertSame('petugas', $account->role);
        $this->assertFalse($account->is_active);
        $this->assertTrue(Hash::check('password-baru', $account->password));
        $this->assertDatabaseHas('log_aktivitas', [
            'aksi' => 'account.updated',
            'subjek_id' => $account->id,
        ]);
    }

    public function test_admin_cannot_disable_or_demote_the_current_account(): void
    {
        $admin = $this->createUser('admin', 'admin@example.test');

        $this->actingAs($admin)->put('/admin/akun/'.$admin->id, [
            'name' => $admin->name,
            'email' => $admin->email,
            'role' => 'petugas',
            'is_active' => '0',
        ])->assertSessionHasErrors('role');

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    public function test_member_can_update_contact_but_cannot_change_identity_or_role(): void
    {
        $user = $this->createUser('anggota', 'anggota@example.test');
        $member = Anggota::create([
            'nomor_anggota' => 'UJI-001',
            'nama' => 'Nama Anggota',
            'telepon' => '081200000001',
            'alamat' => 'Alamat lama',
            'status_keanggotaan' => 'aktif',
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)->put('/profil', [
            'telepon' => '081200000002',
            'alamat' => 'Alamat baru',
        ])->assertRedirect();

        $this->assertDatabaseHas('anggotas', [
            'id' => $member->id,
            'telepon' => '081200000002',
            'alamat' => 'Alamat baru',
            'nomor_anggota' => 'UJI-001',
            'nama' => 'Nama Anggota',
        ]);

        $this->actingAs($user)->put('/profil', [
            'telepon' => '081200000003',
            'role' => 'admin',
            'nomor_anggota' => 'UBAH-001',
        ])->assertUnprocessable();

        $this->assertSame('anggota', $user->fresh()->role);
        $this->assertSame('UJI-001', $member->fresh()->nomor_anggota);
    }

    private function createUser(
        string $role,
        string $email,
        string $name = 'Pengguna Uji',
        bool $isActive = true,
    ): User {
        return User::create([
            'name' => $name,
            'email' => $email,
            'password' => 'password-rahasia',
            'role' => $role,
            'is_active' => $isActive,
        ]);
    }
    public function test_user_can_change_password_after_confirming_current_password(): void
    {
        $user = $this->createUser('anggota', 'password@example.test');

        $this->actingAs($user)->put('/profil', [
            'current_password' => 'password-rahasia',
            'password' => 'password-baru',
            'password_confirmation' => 'password-baru',
        ])->assertRedirect();

        $this->assertTrue(Hash::check('password-baru', $user->fresh()->password));
    }

    public function test_admin_links_member_role_account_to_an_active_member(): void
    {
        $admin = $this->createUser('admin', 'admin@example.test');
        $member = Anggota::create([
            'nomor_anggota' => 'LINK-001',
            'nama' => 'Anggota Terhubung',
            'status_keanggotaan' => 'aktif',
        ]);

        $this->actingAs($admin)->post('/admin/akun', [
            'name' => 'Akun Anggota',
            'email' => 'link@example.test',
            'role' => 'anggota',
            'anggota_id' => $member->id,
            'password' => 'password-rahasia',
            'password_confirmation' => 'password-rahasia',
            'is_active' => '1',
        ])->assertRedirect('/admin/akun');

        $this->assertDatabaseHas('anggotas', [
            'id' => $member->id,
            'user_id' => User::where('email', 'link@example.test')->value('id'),
        ]);
    }
}




