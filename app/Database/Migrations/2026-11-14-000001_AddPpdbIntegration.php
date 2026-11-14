<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPpdbIntegration extends Migration
{
    public function up()
    {
        $this->forge->addColumn('tb_siswa', [
            'ppdb_ref' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
                'default'    => null,
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => false,
                'default'    => 1,
            ],
            'ppdb_synced_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'ppdb_rombel_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            // "tingkat|index" kelas terakhir yang diset dari PPDB; deteksi rombel ganti nama vs kenaikan manual.
            'ppdb_kelas_key' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'default'    => null,
            ],
        ]);
        $this->db->query('CREATE UNIQUE INDEX uq_tb_siswa_ppdb_ref ON tb_siswa(ppdb_ref)');

        $this->forge->addField([
            'id'             => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'base_url'       => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'token'          => ['type' => 'TEXT', 'null' => true],
            'webhook_secret' => ['type' => 'TEXT', 'null' => true],
            'synced_until'   => ['type' => 'DATETIME', 'null' => true],
            'last_sync_at'   => ['type' => 'DATETIME', 'null' => true],
            'last_status'    => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => true],
            'last_message'   => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('tb_ppdb_integration', true);

        $this->forge->addField([
            'id'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'trigger'     => ['type' => 'VARCHAR', 'constraint' => 10],
            'status'      => ['type' => 'VARCHAR', 'constraint' => 10],
            'created'     => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'updated'     => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'deactivated' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'skipped'     => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'message'     => ['type' => 'TEXT', 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('tb_ppdb_sync_log', true);
    }

    public function down()
    {
        $this->forge->dropTable('tb_ppdb_sync_log', true);
        $this->forge->dropTable('tb_ppdb_integration', true);

        if ($this->db->DBDriver === 'SQLite3') {
            $this->db->query('DROP INDEX IF EXISTS uq_tb_siswa_ppdb_ref');
        } else {
            $this->db->query('DROP INDEX uq_tb_siswa_ppdb_ref ON tb_siswa');
        }
        $this->forge->dropColumn('tb_siswa', ['ppdb_ref', 'is_active', 'ppdb_synced_at', 'ppdb_rombel_id', 'ppdb_kelas_key']);
    }
}
