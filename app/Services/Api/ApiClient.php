<?php

namespace App\Services\Api;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;

class ApiClient
{
    protected function request(): PendingRequest
    {
        $token = session('api_token');

        return Http::baseUrl(config('api.base_url'))
            ->timeout(config('api.timeout'))
            ->acceptJson()
            ->when($token, fn($http) => $http->withToken($token));
    }

    public function get(string $uri, array $query = []): Response
    {
        return $this->request()->get($uri, $query);
    }

    public function post(string $uri, array $data = []): Response
    {
        return $this->request()->post($uri, $data);
    }

    public function put(string $uri, array $data = []): Response
    {
        return $this->request()->put($uri, $data);
    }

    public function patch(string $uri, array $data = []): Response
    {
        return $this->request()->patch($uri, $data);
    }

    public function delete(string $uri): Response
    {
        return $this->request()->delete($uri);
    }
}
