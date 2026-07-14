<?php

namespace App\Services\Api;

class ArticuloApiService extends ApiClient
{
    public function all(array $filters = [])
    {
        return $this->get('/articulos', $filters);
    }

    public function find(int $id)
    {
        return $this->get("/articulos/{$id}");
    }

    public function create(array $data)
    {
        return $this->post('/articulos', $data);
    }

    public function update(int $id, array $data)
    {
        return $this->put("/articulos/{$id}", $data);
    }

    public function remove(int $id)
    {
        return parent::delete("/articulos/{$id}");
    }
}
