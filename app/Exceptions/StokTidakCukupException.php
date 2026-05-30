<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Dilempar dari dalam DB::transaction saat stok tidak mencukupi, agar transaksi
 * otomatis rollback. Pesannya sudah ramah-pengguna (Bahasa Indonesia) dan
 * ditangkap di controller untuk dikembalikan sebagai error form.
 */
class StokTidakCukupException extends RuntimeException
{
}
