<?php

namespace App\Models;

use CodeIgniter\Model;

class PpdbSyncLogModel extends Model
{
    protected $table = 'tb_ppdb_sync_log';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'trigger',
        'status',
        'created',
        'updated',
        'deactivated',
        'skipped',
        'message',
        'created_at',
    ];

    public function record(string $trigger, string $status, array $counts, ?string $message): void
    {
        $this->insert([
            'trigger'     => $trigger,
            'status'      => $status,
            'created'     => (int) ($counts['created'] ?? 0),
            'updated'     => (int) ($counts['updated'] ?? 0),
            'deactivated' => (int) ($counts['deactivated'] ?? 0),
            'skipped'     => (int) ($counts['skipped'] ?? 0),
            'message'     => $message,
            'created_at'  => date('Y-m-d H:i:s'),
        ]);
    }
}
