<?php

namespace App\Libraries\Ppdb;

/**
 * Klien API absensi PPDB (docs/api/absensi-v1.yaml).
 * Kedua metode melempar \RuntimeException (pesan berisi status HTTP)
 * bila respons bukan 2xx, koneksi gagal, atau body bukan JSON objek.
 */
interface PpdbApiClient
{
    public function get(string $path, array $query = []): array;

    public function post(string $path, array $body): array;
}
