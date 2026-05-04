<?php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class AdminRequests {

    public function __construct(
        private HttpClientInterface $client
    ) {
    }

    public function get(string $uri, array $options = []): ResponseInterface {
        return $this->client->request('GET', $uri, $options);
    }

    public function post(string $uri, mixed $data = [], array $options = []): ResponseInterface {
        return $this->client->request('GET', $uri, [
            'body' => $data,
            ...$options
        ]);
    }
}