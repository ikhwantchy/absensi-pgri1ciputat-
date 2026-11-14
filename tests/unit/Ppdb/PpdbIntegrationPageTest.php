<?php

namespace Tests\Unit\Ppdb;

use App\Models\PpdbIntegrationModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * @internal
 */
final class PpdbIntegrationPageTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $migrate     = true;
    protected $migrateOnce = false;
    protected $refresh     = true;
    protected $namespace   = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\App\Database\Seeds\GeneralSettingsSeeder::class);
        (new PpdbIntegrationModel())->saveConfig('https://ppdb.test', 'token-lama', 'secret-lama');
    }

    private function user(int $role): int
    {
        $this->db->table('users')->insert([
            'email'         => "u{$role}@test.com",
            'username'      => "user{$role}",
            'is_superadmin' => $role,
            'password_hash' => 'x',
            'active'        => 1,
        ]);

        return (int) $this->db->insertID();
    }

    /** Auth service menyimpan user dari request sebelumnya; reset tiap request. */
    private function as(int $userId): self
    {
        \Config\Services::resetSingle('authentication');
        \Config\Services::resetSingle('authorization');
        $this->withSession(['logged_in' => $userId]);

        return $this;
    }

    public function testPageOnlyForSuperadmin(): void
    {
        $admin = $this->user(1);
        $staf  = $this->user(2);

        $ok = $this->as($admin)->get('admin/ppdb-integration');
        $ok->assertStatus(200);
        $ok->assertSee('Integrasi PPDB');
        $ok->assertSee(base_url('api/ppdb/webhook'));
        $ok->assertDontSee('token-lama');
        $ok->assertDontSee('secret-lama');

        try {
            $res = $this->as($staf)->get('admin/ppdb-integration');
            $this->assertSame(404, $res->response()->getStatusCode());
        } catch (\CodeIgniter\Exceptions\PageNotFoundException $e) {
            $this->addToAssertionCount(1);
        }
    }

    public function testSaveKeepsTokenWhenBlank(): void
    {
        $admin = $this->user(1);

        $this->as($admin)->post('admin/ppdb-integration/save', [
            'base_url'       => 'https://ppdb.test/baru',
            'token'          => '',
            'webhook_secret' => '',
        ]);

        $cfg = (new PpdbIntegrationModel())->config();
        $this->assertSame('https://ppdb.test/baru', $cfg['base_url']);
        $this->assertSame('token-lama', $cfg['token']);
        $this->assertSame('secret-lama', $cfg['webhook_secret']);

        $this->as($admin)->post('admin/ppdb-integration/save', [
            'base_url'       => 'https://ppdb.test/baru',
            'token'          => 'token-baru',
            'webhook_secret' => '',
        ]);

        $cfg = (new PpdbIntegrationModel())->config();
        $this->assertSame('token-baru', $cfg['token']);
        $this->assertSame('secret-lama', $cfg['webhook_secret']);
    }

    public function testSaveRejectsUnsafeBaseUrl(): void
    {
        $admin = $this->user(1);

        foreach ([
            'http://ppdb.contoh.sch.id',
            'ftp://ppdb.test',
            'https://user:pass@ppdb.test',
            'https://user@ppdb.test',
            'bukan url',
        ] as $url) {
            $this->as($admin)->post('admin/ppdb-integration/save', [
                'base_url' => $url, 'token' => 'token-jahat', 'webhook_secret' => '',
            ]);

            $cfg = (new PpdbIntegrationModel())->config();
            $this->assertSame('https://ppdb.test', $cfg['base_url'], $url);
            $this->assertSame('token-lama', $cfg['token'], $url);
        }

        foreach (['http://localhost:8000', 'http://127.0.0.1'] as $url) {
            $this->as($admin)->post('admin/ppdb-integration/save', [
                'base_url' => $url, 'token' => 'token-lokal', 'webhook_secret' => '',
            ]);
            $this->assertSame($url, (new PpdbIntegrationModel())->config()['base_url']);
        }
    }

    public function testHostChangeClearsStoredToken(): void
    {
        $admin = $this->user(1);

        // Host sama (path berbeda): token tetap.
        $this->as($admin)->post('admin/ppdb-integration/save', [
            'base_url' => 'https://ppdb.test/app', 'token' => '', 'webhook_secret' => '',
        ]);
        $this->assertSame('token-lama', (new PpdbIntegrationModel())->config()['token']);

        // Host berubah tanpa token baru: token lama dihapus.
        $this->as($admin)->post('admin/ppdb-integration/save', [
            'base_url' => 'https://ppdb.lain', 'token' => '', 'webhook_secret' => '',
        ]);
        $cfg = (new PpdbIntegrationModel())->config();
        $this->assertSame('https://ppdb.lain', $cfg['base_url']);
        $this->assertEmpty($cfg['token']);
        $this->assertSame('secret-lama', $cfg['webhook_secret']);

        // Host berubah dengan token baru: token baru dipakai.
        $this->as($admin)->post('admin/ppdb-integration/save', [
            'base_url' => 'https://ppdb.ketiga', 'token' => 'token-baru', 'webhook_secret' => '',
        ]);
        $this->assertSame('token-baru', (new PpdbIntegrationModel())->config()['token']);
    }

    public function testStatusLabelsTimezones(): void
    {
        $admin = $this->user(1);
        (new PpdbIntegrationModel())->markResult('ok', 'Selesai', '2026-11-14 05:12:00');

        $res = $this->as($admin)->get('admin/ppdb-integration');
        $res->assertSee('WIB');
        $res->assertSee('2026-11-14 05:12:00 UTC');
    }
}
