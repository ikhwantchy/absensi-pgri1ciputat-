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
            'lastStatus' => $cfg['last_status'] ?? null,
            'lastMsg'    => $cfg['last_message'] ?? null,
            'logs'       => (new PpdbSyncLogModel())->orderBy('id', 'DESC')->findAll(50),
            'webhookUrl' => base_url('api/ppdb/webhook'),
        ]);
    }

    public function save()
    {
        $baseUrl = trim((string) $this->request->getPost('base_url'));
        if ($baseUrl === '' || ! filter_var($baseUrl, FILTER_VALIDATE_URL)) {
            return redirect()->to('admin/ppdb-integration')->with('error', 'URL PPDB tidak valid.');
        }

        // Kosong = pertahankan nilai lama.
        $token  = trim((string) $this->request->getPost('token'));
        $secret = trim((string) $this->request->getPost('webhook_secret'));

        $this->integration->saveConfig(
            rtrim($baseUrl, '/'),
            $token === '' ? null : $token,
            $secret === '' ? null : $secret
        );

        return redirect()->to('admin/ppdb-integration')->with('success', 'Pengaturan disimpan.');
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
