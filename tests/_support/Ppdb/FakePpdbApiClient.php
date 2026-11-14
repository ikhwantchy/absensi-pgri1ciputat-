<?php

namespace Tests\Support\Ppdb;

use App\Libraries\Ppdb\PpdbApiClient;
use RuntimeException;

/**
 * Klien API PPDB palsu untuk tes: antrean respons per path, rekam panggilan,
 * dan bisa diset gagal pada panggilan GET ke-N atau pada POST.
 */
class FakePpdbApiClient implements PpdbApiClient
{
    /** @var array<string, list<array>> */
    private array $responses = [];

    /** @var list<array{path: string, query: array}> */
    public array $gets = [];

    /** @var list<array{path: string, body: array}> */
    public array $posts = [];

    private ?int $failOnGet = null;
    private bool $failPost  = false;

    public function queue(string $path, array $response): self
    {
        $this->responses[$path][] = $response;

        return $this;
    }

    /** Panggilan GET ke-$n (mulai 1) melempar RuntimeException. */
    public function failOnGet(int $n): self
    {
        $this->failOnGet = $n;

        return $this;
    }

    public function failPost(): self
    {
        $this->failPost = true;

        return $this;
    }

    public function get(string $path, array $query = []): array
    {
        $this->gets[] = ['path' => $path, 'query' => $query];

        if ($this->failOnGet !== null && count($this->gets) === $this->failOnGet) {
            throw new RuntimeException("PPDB API GET {$path} gagal: HTTP 500");
        }

        if (empty($this->responses[$path])) {
            throw new RuntimeException("Fake: tidak ada respons untuk {$path}");
        }

        return array_shift($this->responses[$path]);
    }

    public function post(string $path, array $body): array
    {
        $this->posts[] = ['path' => $path, 'body' => $body];

        if ($this->failPost) {
            throw new RuntimeException("PPDB API POST {$path} gagal: HTTP 422");
        }

        return ['synced_until' => $body['synced_until'] ?? null];
    }
}
