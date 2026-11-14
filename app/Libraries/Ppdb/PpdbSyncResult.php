<?php

namespace App\Libraries\Ppdb;

class PpdbSyncResult
{
    /** synced_until baru (UTC, Y-m-d H:i:s) bila run berhasil; null bila tidak maju. */
    public ?string $syncedUntil = null;

    public function __construct(
        public string $status,
        public string $message = '',
        public int $created = 0,
        public int $updated = 0,
        public int $deactivated = 0,
        public int $skipped = 0,
    ) {
    }

    public function counts(): array
    {
        return [
            'created'     => $this->created,
            'updated'     => $this->updated,
            'deactivated' => $this->deactivated,
            'skipped'     => $this->skipped,
        ];
    }
}
