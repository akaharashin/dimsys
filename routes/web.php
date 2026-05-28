<?php
use App\Http\Controllers\Laporan\ExportBulananController;
use App\Http\Controllers\Stok\StokOpnameController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Master\WilayahController;
use App\Http\Controllers\Master\ProdukController;
use App\Http\Controllers\Master\OutletController;
use App\Http\Controllers\Master\SupplierController;
use App\Http\Controllers\Master\UserController;
use App\Http\Controllers\Stok\StokMasukController;
use App\Http\Controllers\Stok\DistribusiController;
use App\Http\Controllers\Transaksi\LaporanHarianController;
use App\Http\Controllers\Api\DistribusiApiController;
use App\Http\Controllers\Transaksi\KasController;
use App\Http\Controllers\Transaksi\PenjualanWilayahController;
use App\Http\Controllers\Laporan\OmsetController;
use App\Http\Controllers\Laporan\KontrolController;
use App\Http\Controllers\Laporan\StokController;
use App\Http\Controllers\Laporan\RataRataOutController;
use App\Http\Controllers\Stok\RekapStokController;
use App\Http\Controllers\Admin\ActivityLogController;

Route::get('/', fn() => redirect()->route('login'));

Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Master — admin_pusat full access, owner read only (dikontrol di view)
    Route::middleware('role:admin_pusat|owner')->prefix('master')->name('master.')->group(function () {
        Route::get('wilayah/export', [WilayahController::class, 'export'])->name('wilayah.export');
        Route::resource('wilayah', WilayahController::class);
        Route::get('produk/export', [ProdukController::class, 'export'])->name('produk.export');
        Route::resource('produk', ProdukController::class);
        Route::get('outlet/export', [OutletController::class, 'export'])->name('outlet.export');
        Route::post('outlet', [OutletController::class, 'store'])->name('outlet.store');
        Route::delete('outlet/{outlet}', [OutletController::class, 'destroy'])->name('outlet.destroy');
        Route::get('supplier/export', [SupplierController::class, 'export'])->name('supplier.export');
        Route::resource('supplier', SupplierController::class);
    });

    Route::model('opname', \App\Models\StokOpname::class);

    // Stok — admin_pusat + koordinator full, owner read only (dikontrol di view)
    Route::middleware('role:admin_pusat|koordinator|owner')->prefix('stok')->name('stok.')->group(function () {
        Route::get('masuk/export', [StokMasukController::class, 'export'])->name('masuk.export');
        Route::resource('masuk', StokMasukController::class);
        Route::get('distribusi/export', [DistribusiController::class, 'export'])->name('distribusi.export');
        Route::resource('distribusi', DistribusiController::class);
        Route::get('rekap', [RekapStokController::class, 'index'])->name('rekap');
        Route::get('rekap/export', [RekapStokController::class, 'export'])->name('rekap.export');
        Route::get('opname/export', [StokOpnameController::class, 'export'])->name('opname.export');
        Route::get('opname/stok-sistem', [StokOpnameController::class, 'getStokSistem'])->name('opname.stok-sistem');
        Route::post('opname/{id}/foto', [StokOpnameController::class, 'uploadFoto'])->name('opname.foto.upload');
        Route::delete('opname/foto/{fotoId}', [StokOpnameController::class, 'hapusFoto'])->name('opname.foto.hapus');
        Route::resource('opname', StokOpnameController::class);

        Route::middleware('role:admin_pusat|koordinator')->group(function () {
            Route::get('generate-awal/preview', [StokMasukController::class, 'generateAwalPreview'])->name('generate-awal.preview');
            Route::get('generate-awal', [StokMasukController::class, 'generateAwalForm'])->name('generate-awal');
            Route::post('generate-awal', [StokMasukController::class, 'generateAwal'])->name('generate-awal.store');
        });
    });

    // Outlet index + update — admin_pusat | owner | koordinator
    Route::middleware('role:admin_pusat|owner|koordinator')->prefix('master')->name('master.')->group(function () {
        Route::get('outlet', [OutletController::class, 'index'])->name('outlet.index');
    });
    Route::middleware('role:admin_pusat|koordinator')->prefix('master')->name('master.')->group(function () {
        Route::put('outlet/{outlet}', [OutletController::class, 'update'])->name('outlet.update');
        Route::patch('outlet/{outlet}', [OutletController::class, 'update']);
    });

    // Transaksi — admin_pusat + koordinator full, owner read only (dikontrol di view)
    Route::middleware('role:admin_pusat|koordinator|owner')->prefix('transaksi')->name('transaksi.')->group(function () {
        Route::get('laporan-harian/export', [LaporanHarianController::class, 'export'])->name('laporan-harian.export');
        Route::resource('laporan-harian', LaporanHarianController::class);
        Route::get('kas/export', [KasController::class, 'export'])->name('kas.export');
        Route::resource('kas', KasController::class);
        Route::get('penjualan-wilayah/export', [PenjualanWilayahController::class, 'export'])->name('penjualan-wilayah.export');
        Route::post('penjualan-wilayah/{penjualanWilayah}/approve', [PenjualanWilayahController::class, 'approve'])->name('penjualan-wilayah.approve');
        Route::post('penjualan-wilayah/{penjualanWilayah}/reject', [PenjualanWilayahController::class, 'reject'])->name('penjualan-wilayah.reject');
        Route::post('penjualan-wilayah/{id}/foto', [PenjualanWilayahController::class, 'uploadFoto'])->name('penjualan-wilayah.foto.upload');
        Route::delete('penjualan-wilayah/foto/{fotoId}', [PenjualanWilayahController::class, 'hapusFoto'])->name('penjualan-wilayah.foto.hapus');
        Route::resource('penjualan-wilayah', PenjualanWilayahController::class);
    });

    // Laporan — semua role
    Route::prefix('laporan')->name('laporan.')->group(function () {
        Route::get('omset', [OmsetController::class, 'index'])->name('omset');
        Route::get('omset/export', [OmsetController::class, 'export'])->name('omset.export');
        Route::get('kontrol', [KontrolController::class, 'index'])->name('kontrol');
        Route::get('kontrol/export', [KontrolController::class, 'export'])->name('kontrol.export');
        Route::get('stok', [StokController::class, 'index'])->name('stok');
        Route::get('stok/export', [StokController::class, 'export'])->name('stok.export');
        Route::get('rata-rata-out', [RataRataOutController::class, 'index'])->name('rata-rata-out');
        Route::get('rata-rata-out/export', [RataRataOutController::class, 'export'])->name('rata-rata-out.export');
        Route::get('export-bulanan', [ExportBulananController::class, 'export'])->name('export-bulanan');
    });

    // Admin — admin_pusat only
    Route::middleware('role:admin_pusat')->prefix('admin')->name('admin.')->group(function () {
        Route::get('activity-log', [ActivityLogController::class, 'index'])->name('activity-log');
    });

    // Master User — admin_pusat only
    Route::middleware('role:admin_pusat')->prefix('master')->name('master.')->group(function () {
        Route::get('user/export', [UserController::class, 'export'])->name('user.export');
        Route::post('user/{user}/reset-password', [UserController::class, 'resetPassword'])
            ->name('user.reset-password');
        Route::post('user/{id}/restore', [UserController::class, 'restore'])
            ->name('user.restore');
        Route::resource('user', UserController::class)->except(['create', 'show', 'edit']);
    });

    Route::prefix('api')->group(function () {
        Route::get('distribusi', [DistribusiApiController::class, 'getByOutletTanggal'])->name('api.distribusi');
        Route::get('stok-tersedia', [\App\Http\Controllers\Api\StokApiController::class, 'getStokTersedia'])->name('api.stok-tersedia');
    });

});

require __DIR__ . '/auth.php';