<?php

namespace App\Models;

use CodeIgniter\Model;

class PpdbIntegrationModel extends Model
{
    protected $table = 'tb_ppdb_integration';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'base_url',
        'token',
        'webhook_secret',
        'synced_until',
        'last_sync_at',
        'last_status',
        'last_message',
        'updated_at',
    ];

    /**
     * Satu baris konfigurasi; dibuat kosong bila belum ada.
     * Token dan secret dikembalikan dalam bentuk terdekripsi.
     */
    public function config(): array
    {
        $row = $this->row();

        $token  = $row['token'] ?? null;
        $secret = $row['webhook_secret'] ?? null;

        $row['token']             = $this->decrypt($token);
        $row['webhook_secret']    = $this->decrypt($secret);
        // Nilai tersimpan tapi tidak terbaca (mis. encryption.key berubah): minta admin isi ulang.
        $row['token_unreadable']  = $this->isUnreadable($token, $row['token']);
        $row['secret_unreadable'] = $this->isUnreadable($secret, $row['webhook_secret']);

        return $row;
    }

    /** `null` pada $token / $secret berarti nilai lama dipertahankan. */
    public function saveConfig(string $baseUrl, ?string $token, ?string $secret): void
    {
        $row  = $this->row();
        $data = ['base_url' => $baseUrl, 'updated_at' => date('Y-m-d H:i:s')];

        if ($token !== null) {
            $data['token'] = $this->encrypt($token);
        }
        if ($secret !== null) {
            $data['webhook_secret'] = $this->encrypt($secret);
        }

        $this->update($row['id'], $data);
    }

    public function markResult(string $status, string $message, ?string $syncedUntil = null): void
    {
        $row  = $this->row();
        $data = [
            'last_sync_at' => date('Y-m-d H:i:s'),
            'last_status'  => $status,
            'last_message' => mb_substr($message, 0, 255),
        ];
        if ($syncedUntil !== null) {
            $data['synced_until'] = $syncedUntil;
        }

        $this->update($row['id'], $data);
    }

    private function row(): array
    {
        $row = $this->orderBy('id')->first();
        if ($row === null) {
            $id  = $this->insert(['updated_at' => date('Y-m-d H:i:s')], true);
            $row = $this->find($id);
        }

        return $row;
    }

    private function encrypt(string $plain): string
    {
        return $plain === '' ? '' : base64_encode(service('encrypter')->encrypt($plain));
    }

    private function decrypt(?string $stored): ?string
    {
        if ($stored === null || $stored === '') {
            return $stored;
        }

        try {
            $raw = base64_decode($stored, true);
            if ($raw === false) {
                return null;
            }

            return service('encrypter')->decrypt($raw);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function isUnreadable(?string $stored, ?string $plain): bool
    {
        return $stored !== null && $stored !== '' && $plain === null;
    }
}
