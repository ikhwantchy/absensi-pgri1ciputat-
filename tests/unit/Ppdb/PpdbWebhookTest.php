<?php

namespace Tests\Unit\Ppdb;

use App\Libraries\Ppdb\PpdbSync;
use App\Libraries\Ppdb\PpdbSyncResult;
use App\Models\PpdbIntegrationModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\StreamFilterTrait;
use Config\Services;
use Tests\Support\Ppdb\FakePpdbApiClient;

/**
 * @internal
 */
final class PpdbWebhookTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;
    use StreamFilterTrait;

    protected $migrate     = true;
    protected $migrateOnce = false;
    protected $refresh     = true;
    protected $namespace   = null;

    private const SECRET = 'rahasia-webhook';
    private const BODY   = '{"event":"siswa.updated"}';

    protected function setUp(): void
    {
        parent::setUp();

        (new PpdbIntegrationModel())->saveConfig('https://ppdb.test', 'tok', self::SECRET);
    }

    protected function tearDown(): void
    {
        Services::resetSingle('ppdbSync');
        @unlink(WRITEPATH . 'ppdb-sync.lock');
        parent::tearDown();
    }

    private function fakeSync(string $status = 'ok'): object
    {
        return new class ($status) {
            /** @var list<string> */
            public array $calls = [];

            public function __construct(private string $status)
            {
            }

            public function run(string $trigger): PpdbSyncResult
            {
                $this->calls[] = $trigger;

                return new PpdbSyncResult($this->status);
            }
        };
    }

    private function sign(int $ts, string $body = self::BODY, string $secret = self::SECRET): string
    {
        return 'sha256=' . hash_hmac('sha256', "{$ts}.{$body}", $secret);
    }

    private function hook(array $headers, string $body = self::BODY)
    {
        $req = $this->withBody($body)->withHeaders($headers);

        return $req->call('post', 'api/ppdb/webhook');
    }

    private function signedHeaders(int $ts): array
    {
        return ['X-PPDB-Timestamp' => (string) $ts, 'X-PPDB-Signature' => $this->sign($ts)];
    }

    public function testWebhookRejectsBadSignature(): void
    {
        $fake = $this->fakeSync();
        Services::injectMock('ppdbSync', $fake);
        $ts = time();

        $res = $this->hook(['X-PPDB-Timestamp' => (string) $ts, 'X-PPDB-Signature' => $this->sign($ts, self::BODY, 'salah')]);

        $res->assertStatus(401);
        $this->assertSame(['ok' => false], json_decode($res->getJSON(), true));
        $this->assertSame([], $fake->calls);
    }

    public function testWebhookRejectsMissingHeaders(): void
    {
        $fake = $this->fakeSync();
        Services::injectMock('ppdbSync', $fake);

        $this->hook([])->assertStatus(401);
        $this->assertSame([], $fake->calls);
    }

    public function testWebhookRejectsStaleTimestamp(): void
    {
        $fake = $this->fakeSync();
        Services::injectMock('ppdbSync', $fake);
        $ts = time() - 301;

        $this->hook($this->signedHeaders($ts))->assertStatus(401);
        $this->assertSame([], $fake->calls);
    }

    public function testWebhookRejectsWhenSecretNotSet(): void
    {
        $this->db->table('tb_ppdb_integration')->update(['webhook_secret' => null]);
        $fake = $this->fakeSync();
        Services::injectMock('ppdbSync', $fake);

        $this->hook($this->signedHeaders(time()))->assertStatus(401);
        $this->assertSame([], $fake->calls);
    }

    public function testWebhookValidRunsSync(): void
    {
        $fake = $this->fakeSync('ok');
        Services::injectMock('ppdbSync', $fake);

        $res = $this->hook($this->signedHeaders(time()));

        $res->assertStatus(200);
        $this->assertSame(['ok' => true, 'status' => 'ok'], json_decode($res->getJSON(), true));
        $this->assertSame(['webhook'], $fake->calls);
    }

    public function testWebhookReturns200WhenLocked(): void
    {
        // PpdbSync asli; lock dipegang proses lain, klien API tidak boleh disentuh.
        Services::injectMock('ppdbSync', new PpdbSync(new FakePpdbApiClient()));
        $handle = fopen(WRITEPATH . 'ppdb-sync.lock', 'c');
        $this->assertTrue(flock($handle, LOCK_EX | LOCK_NB));

        try {
            $res = $this->hook($this->signedHeaders(time()));
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }

        $res->assertStatus(200);
        $this->assertSame(['ok' => true, 'status' => 'skipped'], json_decode($res->getJSON(), true));
    }

    public function testWebhookDoesNotRequireLogin(): void
    {
        Services::injectMock('ppdbSync', $this->fakeSync());

        // Tanpa sesi login: bukan redirect ke login.
        $res = $this->hook($this->signedHeaders(time()));

        $this->assertFalse($res->isRedirect());
        $res->assertStatus(200);
    }

    public function testCommandRunsCronTrigger(): void
    {
        $fake = $this->fakeSync('ok');
        Services::injectMock('ppdbSync', $fake);

        command('ppdb:sync');

        $this->assertSame(['cron'], $fake->calls);
        $this->assertStringContainsString('ok', $this->getStreamFilterBuffer());
    }
}
