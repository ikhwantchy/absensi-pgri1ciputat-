<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\Ppdb\HttpPpdbApiClient;
use App\Models\PpdbIntegrationModel;
use App\Models\PpdbSyncLogModel;

class PpdbIntegration extends BaseController
{
    private PpdbIntegrationModel $integration;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        helper('user');
        if (! is_superadmin()) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        $this->integration = new PpdbIntegrationModel();
    }

    public function index()
    {
        $cfg = $this->integration->config();

        return view('admin/ppdb-integration/index', [
            'title'      => 'Integrasi PPDB',
            'ctx'        => 'ppdb-integration',
            'baseUrl'    => $cfg['base_url'] ?? '',
            'hasToken'   => ! empty($cfg['token']),
            'hasSecret'  => ! empty($cfg['webhook_secret']),
            'tokenBad'   => (bool) $cfg['token_unreadable'],
            'secretBad'  => (bool) $cfg['secret_unreadable'],
            'lastAt'     => $cfg['last_sync_at'] ?? null,
            'syncedUntil' => $cfg['synced_until'] ?? null,
            'lastStatus' => $cfg['last_status'] ?? null,
            'lastMsg'    => $cfg['last_message'] ?? null,
            'logs'       => (new PpdbSyncLogModel())->orderBy('id', 'DESC')->findAll(50),
            'webhookUrl' => base_url('api/ppdb/webhook'),
        ]);
    }

    public function save()
    {
        $baseUrl = trim((string) $this->request->getPost('base_url'));
        if ($error = $this->baseUrlError($baseUrl)) {
            return redirect()->to('admin/ppdb-integration')->with('error', $error);
        }

        // Kosong = pertahankan nilai lama.
        $token  = trim((string) $this->request->getPost('token'));
        $secret = trim((string) $this->request->getPost('webhook_secret'));

        // Host berubah: token lama tidak boleh terkirim ke server lain; wajib diisi ulang.
        $oldHost     = strtolower((string) parse_url((string) ($this->integration->config()['base_url'] ?? ''), PHP_URL_HOST));
        $hostChanged = $oldHost !== '' && $oldHost !== strtolower((string) parse_url($baseUrl, PHP_URL_HOST));
        $clearToken  = $hostChanged && $token === '';

        $this->integration->saveConfig(
            rtrim($baseUrl, '/'),
            $clearToken ? '' : ($token === '' ? null : $token),
            $secret === '' ? null : $secret
        );

        return redirect()->to('admin/ppdb-integration')->with(
            'success',
            $clearToken ? 'Pengaturan disimpan. Host PPDB berubah; isi ulang token.' : 'Pengaturan disimpan.'
        );
    }

    /** Wajib https; http hanya untuk localhost. Tanpa user/password di URL. */
    private function baseUrlError(string $url): ?string
    {
        $parts = $url === '' || ! filter_var($url, FILTER_VALIDATE_URL) ? false : parse_url($url);
        if ($parts === false || empty($parts['host'])) {
            return 'URL PPDB tidak valid.';
        }
        if (isset($parts['user']) || isset($parts['pass'])) {
            return 'URL PPDB tidak boleh berisi user atau password.';
        }

        $scheme = strtolower($parts['scheme'] ?? '');
        $local  = in_array(strtolower($parts['host']), ['localhost', '127.0.0.1'], true);
        if ($scheme !== 'https' && ! ($scheme === 'http' && $local)) {
            return 'URL PPDB wajib https:// (http:// hanya untuk localhost).';
        }

        return null;
    }

    public function test()
    {
        $cfg = $this->integration->config();

        try {
            $client = new HttpPpdbApiClient((string) ($cfg['base_url'] ?? ''), (string) ($cfg['token'] ?? ''));
            $res    = $client->get('api/v1/rombel');
            $n      = is_array($res['data'] ?? null) ? count($res['data']) : count($res);

            return redirect()->to('admin/ppdb-integration')->with('success', "Koneksi berhasil. {$n} rombel.");
        } catch (\Throwable $e) {
            return redirect()->to('admin/ppdb-integration')->with('error', 'Koneksi gagal: ' . $e->getMessage());
        }
    }

    public function sync()
    {
        $r   = service('ppdbSync')->run('manual');
        $msg = sprintf(
            '%s: baru %d, diubah %d, dinonaktifkan %d, dilewati %d. %s',
            $r->status,
            $r->created,
            $r->updated,
            $r->deactivated,
            $r->skipped,
            $r->message
        );

        return redirect()->to('admin/ppdb-integration')->with($r->status === 'failed' ? 'error' : 'success', $msg);
    }
}
