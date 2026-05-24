<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $sukabumi = Wilayah::where('nama', 'Sukabumi')->first();
        $cianjur = Wilayah::where('nama', 'Cianjur')->first();

        // Owner
        $owner = User::firstOrCreate(
            ['email' => 'owner@dimsys.id'],
            [
                'name' => 'Owner',
                'password' => Hash::make('password'),
                'role' => 'owner',
                'wilayah_id' => $sukabumi?->id,
            ]
        );
        $owner->assignRole('owner');

        // Admin Pusat
        $admin = User::firstOrCreate(
            ['email' => 'admin@dimsys.id'],
            [
                'name' => 'Admin Pusat',
                'password' => Hash::make('password'),
                'role' => 'admin_pusat',
                'wilayah_id' => $sukabumi?->id,
            ]
        );
        $admin->assignRole('admin_pusat');

        // Koordinator Sukabumi
        $koordinatorSku = User::firstOrCreate(
            ['email' => 'koordinator.sukabumi@dimsys.id'],
            [
                'name' => 'Koordinator Sukabumi',
                'password' => Hash::make('password'),
                'role' => 'koordinator',
                'wilayah_id' => $sukabumi?->id,
            ]
        );
        $koordinatorSku->assignRole('koordinator');

        // Koordinator Cianjur
        $koordinatorCjr = User::firstOrCreate(
            ['email' => 'koordinator.cianjur@dimsys.id'],
            [
                'name' => 'Koordinator Cianjur',
                'password' => Hash::make('password'),
                'role' => 'koordinator',
                'wilayah_id' => $cianjur?->id,
            ]
        );
        $koordinatorCjr->assignRole('koordinator');
    }
}