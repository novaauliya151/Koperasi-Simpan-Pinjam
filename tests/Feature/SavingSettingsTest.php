<?php

namespace Tests\Feature;

use App\Models\JenisSimpanan;
use App\Models\KetentuanSimpanan;
use App\Models\LogAktivitas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SavingSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_saving_type_with_an_effective_rule(): void
    {
        $admin = $this->createUser('admin', 'admin@example.test');

        $this->actingAs($admin)->post('/admin/jenis-simpanan', [
            'kode' => 'SUKARELA',
            'nama' => 'Simpanan Sukarela',
            'nominal_ketentuan' => '50000.00',
            'berlaku_mulai' => now()->toDateString(),
        ])->assertRedirect('/admin/jenis-simpanan');

        $type = JenisSimpanan::where('kode', 'SUKARELA')->firstOrFail();
        $this->assertDatabaseHas('ketentuan_simpanans', [
            'jenis_simpanan_id' => $type->id,
            'nominal_ketentuan' => '50000.00',
            'dibuat_oleh' => $admin->id,
        ]);
        $this->assertDatabaseHas('log_aktivitas', [
            'aksi' => 'saving_type.created',
            'subjek_id' => $type->id,
        ]);
    }

    public function test_new_effective_rule_keeps_the_previous_rule_unchanged(): void
    {
        $admin = $this->createUser('admin', 'admin@example.test');
        $type = JenisSimpanan::create([
            'kode' => 'WAJIB',
            'nama' => 'Simpanan Wajib',
            'nominal_ketentuan' => '25000.00',
            'berlaku_mulai' => now()->toDateString(),
            'is_active' => true,
        ]);
        $oldRule = KetentuanSimpanan::create([
            'jenis_simpanan_id' => $type->id,
            'nominal_ketentuan' => '25000.00',
            'berlaku_mulai' => now()->toDateString(),
            'dibuat_oleh' => $admin->id,
        ]);
        $newDate = now()->addDay()->toDateString();

        $this->actingAs($admin)->put('/admin/jenis-simpanan/'.$type->id, [
            'kode' => 'WAJIB',
            'nama' => 'Simpanan Wajib',
            'nominal_ketentuan' => '30000.00',
            'berlaku_mulai' => $newDate,
            'is_active' => '1',
        ])->assertRedirect('/admin/jenis-simpanan');

        $this->assertSame('25000.00', $oldRule->fresh()->nominal_ketentuan);
        $this->assertDatabaseHas('ketentuan_simpanans', [
            'jenis_simpanan_id' => $type->id,
            'nominal_ketentuan' => '30000.00',
            'berlaku_mulai' => $newDate,
        ]);
    }

    public function test_deactivating_a_saving_type_preserves_its_history(): void
    {
        $admin = $this->createUser('admin', 'admin@example.test');
        $type = JenisSimpanan::create([
            'kode' => 'POKOK',
            'nama' => 'Simpanan Pokok',
            'berlaku_mulai' => now()->toDateString(),
            'is_active' => true,
        ]);
        KetentuanSimpanan::create([
            'jenis_simpanan_id' => $type->id,
            'nominal_ketentuan' => null,
            'berlaku_mulai' => now()->toDateString(),
            'dibuat_oleh' => $admin->id,
        ]);

        $this->actingAs($admin)->patch('/admin/jenis-simpanan/'.$type->id.'/status', [
            'is_active' => '0',
        ])->assertRedirect('/admin/jenis-simpanan');

        $this->assertDatabaseHas('jenis_simpanans', [
            'id' => $type->id,
            'is_active' => false,
        ]);
        $this->assertDatabaseHas('ketentuan_simpanans', [
            'jenis_simpanan_id' => $type->id,
        ]);
    }

    public function test_activity_log_is_available_only_to_admins(): void
    {
        $admin = $this->createUser('admin', 'admin@example.test');
        $staff = $this->createUser('petugas', 'petugas@example.test');

        LogAktivitas::create([
            'user_id' => $admin->id,
            'aksi' => 'saving_type.created',
            'perubahan' => ['fields_changed' => ['kode']],
            'terjadi_pada' => now(),
        ]);

        $this->actingAs($admin)
            ->get('/admin/log-aktivitas')
            ->assertOk()
            ->assertSee('saving_type.created');

        $this->actingAs($staff)
            ->get('/admin/log-aktivitas')
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
