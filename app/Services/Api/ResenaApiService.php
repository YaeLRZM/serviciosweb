<?php

namespace App\Services\Api;

class ResenaApiService extends ApiClient
{
    public function all(array $filters = [])
    {
        return $this->get('/resenas', $filters);
    }
}
