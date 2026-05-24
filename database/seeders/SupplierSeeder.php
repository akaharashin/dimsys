<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Supplier;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $supplier = [
            ['nama' => 'Jaya Food', 'keterangan' => 'Supplier utama - Sukabumi'],
            ['nama' => 'Dimsum Salmon', 'keterangan' => 'Supplier - Tangerang'],
        ];

        foreach ($supplier as $s) {
            Supplier::create($s);
        }
    }
}