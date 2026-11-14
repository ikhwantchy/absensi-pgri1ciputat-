<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\PpdbIntegrationModel;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Penerima webhook dari PPDB. Diautentikasi lewat HMAC-SHA256, bukan sesi login.
 */
class PpdbWebhook extends BaseController
{
    private const TOLERANCE = 300;

    public function receive(): ResponseInterface
    {
        $secret = (new PpdbIntegrationModel())->config()['webhook_secret'] ?? null;

        if (! $this->isValid($secret)) {
            return $this->response->setStatusCode(401)->setJSON(['ok' => false]);
        }

        $result = service('ppdbSync')->run('webhook');

        return $this->response->setStatusCode(200)->setJSON(['ok' => true, 'status' => $result->status]);
    }

    private function isValid(?string $secret): bool
    {
        if ($secret === null || $secret === '') {
            return false;
        }

        $ts  = $this->request->getHeaderLine('X-PPDB-Timestamp');
        $sig = $this->request->getHeaderLine('X-PPDB-Signature');

        if ($ts === '' || $sig === '' || ! ctype_digit($ts)) {
            return false;
        }
        if (abs(time() - (int) $ts) > self::TOLERANCE) {
            return false;
        }

        $expected = 'sha256=' . hash_hmac('sha256', $ts . '.' . $this->request->getBody(), $secret);

        return hash_equals($expected, $sig);
    }
}
