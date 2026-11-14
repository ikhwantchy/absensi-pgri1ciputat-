<?php

namespace Tests\Unit\Ppdb;

use App\Models\PpdbIntegrationModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @internal
 */
final class PpdbIntegrationModelTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate     = true;
    protected $migrateOnce = false;
    protected $refresh     = true;
    protected $namespace   = null;

    private function writeRaw(array $data): void
    {
        (new PpdbIntegrationModel())->config(); // pastikan baris ada
        $this->db->table('tb_ppdb_integration')->update($data);
    }

    public function testGarbageStoredValuesDoNotThrow(): void
    {
        $this->writeRaw(['token' => 'bukan-base64-valid!!', 'webhook_secret' => base64_encode('garbage')]);

        $cfg = (new PpdbIntegrationModel())->config();

        $this->assertNull($cfg['token']);
        $this->assertTrue($cfg['token_unreadable']);
        $this->assertNull($cfg['webhook_secret']);
        $this->assertTrue($cfg['secret_unreadable']);
    }

    public function testValueEncryptedWithDifferentKeyIsUnreadable(): void
    {
        $other = new \Config\Encryption();
        $other->key = hex2bin(str_repeat('ab', 32));
        $enc = \Config\Services::encrypter($other, false);

        $this->writeRaw([
            'token'          => base64_encode($enc->encrypt('tok')),
            'webhook_secret' => base64_encode($enc->encrypt('sec')),
        ]);

        $cfg = (new PpdbIntegrationModel())->config();

        $this->assertNull($cfg['token']);
        $this->assertTrue($cfg['token_unreadable']);
        $this->assertTrue($cfg['secret_unreadable']);
    }

    public function testReadableAndEmptyValuesAreNotFlagged(): void
    {
        $model = new PpdbIntegrationModel();
        $cfg   = $model->config();
        $this->assertFalse($cfg['token_unreadable']);
        $this->assertFalse($cfg['secret_unreadable']);

        $model->saveConfig('https://x', 'tok', 'sec');
        $cfg = $model->config();
        $this->assertSame('tok', $cfg['token']);
        $this->assertFalse($cfg['token_unreadable']);
    }
}
