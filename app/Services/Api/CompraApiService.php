<?php

namespace App\Services\Api;


class CompraApiService extends ApiClient
{
    public function all(array $filters = [])
    {
        return $this->get('/compras', $filters);
    }

    public function find(int $id)
    {
        return $this->get("/compras/{$id}");
    }

    public function create(array $data)
    {
        return $this->post('/compras', $data);
    }

    public function update(int $id, array $data)
    {
        return $this->put("/compras/{$id}", $data);
    }

    public function remove(int $id)
    {
        return parent::delete("/compras/{$id}");
    }
}
