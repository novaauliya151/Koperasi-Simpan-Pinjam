<?php

namespace Database\Seeders;

use App\Models\Anggota;
use App\Models\JenisSimpanan;
use App\Models\KetentuanSimpanan;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $passwords = config('app.demo_seed_passwords');
        $requiredRoles = ['admin', 'petugas', 'anggota'];
        $hasConfiguredValues = is_array($passwords)
            && collect($requiredRoles)->every(
                fn (string $role): bool => isset($passwords[$role])
                    && is_string($passwords[$role])
                    && $passwords[$role] !== '',
            );
        $isLocalDemoEnvironment = app()->environment(['local', 'testing']);
        $hasConfiguredPasswords = $hasConfiguredValues
            && ($isLocalDemoEnvironment || (
                collect($requiredRoles)->every(
                    fn (string $role): bool => mb_strlen($passwords[$role]) >= 16,
                )
                && count(array_unique(array_values(array_intersect_key(
                    $passwords,
                    array_flip($requiredRoles),
                )))) === count($requiredRoles)
            ));

        if (! $hasConfiguredPasswords) {
            $this->command?->warn(
                'Demo accounts were skipped. Set three KOPERASI_DEMO_*_PASSWORD values. '
                    .'Production passwords must be distinct and at least 16 characters.',
            );

            return;
        }

        $admin = User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Admin Demo',
                'password' => $passwords['admin'],
                'role' => 'admin',
                'is_active' => true,
            ],
        );

        User::updateOrCreate(
            ['email' => 'petugas@gmail.com'],
            [
                'name' => 'Petugas Demo',
                'password' => $passwords['petugas'],
                'role' => 'petugas',
                'is_active' => true,
            ],
        );

        $anggotaUser = User::updateOrCreate(
            ['email' => 'anggota@gmail.com'],
            [
                'name' => 'Anggota Demo',
                'password' => $passwords['anggota'],
                'role' => 'anggota',
                'is_active' => true,
            ],
        );

        foreach ([
            ['kode' => 'POKOK', 'nama' => 'Simpanan Pokok'],
            ['kode' => 'WAJIB', 'nama' => 'Simpanan Wajib'],
        ] as $savingTypeData) {
            $savingType = JenisSimpanan::firstOrCreate(
                ['kode' => $savingTypeData['kode']],
                [
                    'nama' => $savingTypeData['nama'],
                    'nominal_ketentuan' => null,
                    'berlaku_mulai' => now()->toDateString(),
                    'is_active' => true,
                ],
            );

            if (! $savingType->ketentuan()->exists()) {
                KetentuanSimpanan::create([
                    'jenis_simpanan_id' => $savingType->id,
                    'nominal_ketentuan' => $savingType->nominal_ketentuan,
                    'berlaku_mulai' => $savingType->berlaku_mulai ?? now()->toDateString(),
                    'dibuat_oleh' => $admin->id,
                ]);
            }
        }
        Anggota::updateOrCreate(
            ['nomor_anggota' => 'DEMO-001'],
            [
                'nama' => 'Anggota Demo',
                'alamat' => 'Alamat fiktif untuk pengujian',
                'tanggal_bergabung' => now()->toDateString(),
                'status_keanggotaan' => 'aktif',
                'user_id' => $anggotaUser->id,
                'diverifikasi_oleh' => $admin->id,
                'diverifikasi_pada' => now(),
            ],
        );
    }
}


