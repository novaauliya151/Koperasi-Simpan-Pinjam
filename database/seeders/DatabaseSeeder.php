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
        $admin = User::updateOrCreate(
            ['email' => 'admin@koperasi.test'],
            ['name' => 'Admin Demo', 'password' => 'password', 'role' => 'admin', 'is_active' => true],
        );

        User::updateOrCreate(
            ['email' => 'petugas@koperasi.test'],
            ['name' => 'Petugas Demo', 'password' => 'password', 'role' => 'petugas', 'is_active' => true],
        );

        $anggotaUser = User::updateOrCreate(
            ['email' => 'anggota@koperasi.test'],
            ['name' => 'Anggota Demo', 'password' => 'password', 'role' => 'anggota', 'is_active' => true],
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


