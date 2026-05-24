<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Buat permissions
        $permissions = [
            // Master
            'master.view', 'master.create', 'master.edit', 'master.delete',
            // Stok
            'stok.view', 'stok.create', 'stok.delete',
            // Transaksi
            'transaksi.view', 'transaksi.create', 'transaksi.delete',
            // Laporan
            'laporan.view', 'laporan.export',
            // Penjualan Wilayah (khusus pusat)
            'penjualan-wilayah.view', 'penjualan-wilayah.create', 'penjualan-wilayah.delete',
            // Dashboard
            'dashboard.view',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // Role: Owner — hanya lihat semua
        $owner = Role::firstOrCreate(['name' => 'owner']);
        $owner->syncPermissions([
            'dashboard.view',
            'laporan.view', 'laporan.export',
            'stok.view',
            'transaksi.view',
            'penjualan-wilayah.view',
        ]);

        // Role: Admin Pusat — full access
        $adminPusat = Role::firstOrCreate(['name' => 'admin_pusat']);
        $adminPusat->syncPermissions(Permission::all());

        // Role: Koordinator — input transaksi wilayah sendiri, tidak bisa master
        $koordinator = Role::firstOrCreate(['name' => 'koordinator']);
        $koordinator->syncPermissions([
            'dashboard.view',
            'stok.view', 'stok.create', 'stok.delete',
            'transaksi.view', 'transaksi.create', 'transaksi.delete',
            'laporan.view', 'laporan.export',
        ]);
    }
}