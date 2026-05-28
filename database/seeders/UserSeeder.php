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
        $cianjur  = Wilayah::where('nama', 'Cianjur')->first();
        $bogor    = Wilayah::where('nama', 'Bogor')->first();

        $users = [
            [
                'username'   => 'owner',
                'name'       => 'Owner',
                'email'      => 'owner@dimsys.id',
                'password'   => 'passwordOW47',
                'role'       => 'owner',
                'wilayah_id' => $sukabumi?->id,
            ],
            [
                'username'   => 'admin',
                'name'       => 'Admin Pusat',
                'email'      => 'admin@dimsys.id',
                'password'   => 'passwordAD82',
                'role'       => 'admin_pusat',
                'wilayah_id' => $sukabumi?->id,
            ],
            [
                'username'   => 'koor.sukabumi',
                'name'       => 'Koordinator Sukabumi',
                'email'      => 'koordinator.sukabumi@dimsys.id',
                'password'   => 'passwordSK31',
                'role'       => 'koordinator',
                'wilayah_id' => $sukabumi?->id,
            ],
            [
                'username'   => 'koor.cianjur',
                'name'       => 'Koordinator Cianjur',
                'email'      => 'koordinator.cianjur@dimsys.id',
                'password'   => 'passwordCJ15',
                'role'       => 'koordinator',
                'wilayah_id' => $cianjur?->id,
            ],
            [
                'username'   => 'koor.bogor',
                'name'       => 'Koordinator Bogor',
                'email'      => 'koordinator.bogor@dimsys.id',
                'password'   => 'passwordBG73',
                'role'       => 'koordinator',
                'wilayah_id' => $bogor?->id,
            ],
        ];

        foreach ($users as $u) {
            $user = User::withTrashed()
                ->where('email', $u['email'])
                ->orWhere('username', $u['username'])
                ->first();

            if ($user) {
                $user->update([
                    'username'   => $u['username'],
                    'name'       => $u['name'],
                    'email'      => $u['email'],
                    'password'   => Hash::make($u['password']),
                    'role'       => $u['role'],
                    'wilayah_id' => $u['wilayah_id'],
                    'deleted_at' => null,
                ]);
            } else {
                $user = User::create([
                    'username'   => $u['username'],
                    'name'       => $u['name'],
                    'email'      => $u['email'],
                    'password'   => Hash::make($u['password']),
                    'role'       => $u['role'],
                    'wilayah_id' => $u['wilayah_id'],
                ]);
            }

            $user->syncRoles([$u['role']]);
        }
    }
}
