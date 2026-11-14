<?php

namespace App\Libraries\Ppdb;

use CodeIgniter\HTTP\ResponseInterface;
use RuntimeException;
use Throwable;

class HttpPpdbApiClient implements PpdbApiClient
{
    private string $baseUrl;

    public function __construct(string $baseUrl, private string $token)
    {
        $this->baseUrl = rtrim($baseUrl, '/') . '/';
    }

    public function get(string $path, array $query = []): array
    {
        return $this->send('GET', $path, $query === [] ? [] : ['query' => $query]);
    }

    public function post(string $path, array $body): array
    {
        return $this->send('POST', $path, ['json' => $body]);
    }

    private function send(string $method, string $path, array $options): array
    {
        // Instance baru per permintaan supaya opsi (query/json) tidak terbawa ke permintaan berikutnya.
        $client = service('curlrequest', [
            'baseURI'     => $this->baseUrl,
            'timeout'     => 20,
            'http_errors' => false,
        ], null, null, false);

        $options['headers'] = [
            'Authorization' => 'Bearer ' . $this->token,
            'Accept'        => 'application/json',
        ];

        // Path relatif terhadap baseURI agar base URL dengan subfolder tetap terpakai.
        $url = ltrim($path, '/');

        try {
            /** @var ResponseInterface $response */
            $response = $client->request($method, $url, $options);
        } catch (Throwable $e) {
            throw new RuntimeException("PPDB API {$method} {$path} gagal terhubung: " . $e->getMessage(), 0, $e);
        }

        $status = $response->getStatusCode();
        if ($status < 200 || $status >= 300) {
            throw new RuntimeException("PPDB API {$method} {$path} gagal: HTTP {$status}");
        }

        $data = json_decode((string) $response->getBody(), true);
        if (! is_array($data)) {
            throw new RuntimeException("PPDB API {$method} {$path}: respons HTTP {$status} bukan JSON yang valid");
        }

        return $data;
    }
}
