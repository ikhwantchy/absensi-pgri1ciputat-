<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Melindungi endpoint /api/* dengan Bearer token dari env ABSENSI_API_TOKEN.
 * Dipakai sistem PPDB untuk mengirim data siswa baru.
 */
class ApiTokenFilter implements FilterInterface
{
   public function before(RequestInterface $request, $arguments = null)
   {
      $expected = (string) env('ABSENSI_API_TOKEN', '');
      $header = $request->getHeaderLine('Authorization');
      $given = str_starts_with($header, 'Bearer ') ? substr($header, 7) : '';

      if ($expected === '' || !hash_equals($expected, $given)) {
         return service('response')->setStatusCode(401)->setJSON(['message' => 'Token API tidak valid.']);
      }
   }

   public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
   {
   }
}
