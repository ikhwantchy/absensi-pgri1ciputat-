<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class PpdbSyncCommand extends BaseCommand
{
    protected $group       = 'PPDB';
    protected $name        = 'ppdb:sync';
    protected $description = 'Sinkronkan siswa dari PPDB (dipakai cron).';

    public function run(array $params)
    {
        $r = service('ppdbSync')->run('cron');

        CLI::write(sprintf(
            '%s: baru %d, diubah %d, dinonaktifkan %d, dilewati %d. %s',
            $r->status,
            $r->created,
            $r->updated,
            $r->deactivated,
            $r->skipped,
            $r->message
        ));

        return $r->status === 'failed' ? EXIT_ERROR : EXIT_SUCCESS;
    }
}
