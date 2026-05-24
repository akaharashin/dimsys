<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            WilayahSeeder::class,
            SupplierSeeder::class,
            ProdukSeeder::class,
            OutletSeeder::class,
            RolePermissionSeeder::class,
            UserSeeder::class,
            RekeningSeeder::class,
        ]);
    }
}