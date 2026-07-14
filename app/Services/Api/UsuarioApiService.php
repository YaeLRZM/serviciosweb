<?php

namespace App\Services\Api;

class UsuarioApiService extends ApiClient
{
    public function all(array $filters = [])
    {
        return $this->get('/usuarios', $filters);
    }

    public function find(int $id)
    {
        return $this->get("/usuarios/{$id}");
    }

    public function create(array $data)
    {
        return $this->post('/usuarios', $data);
    }

    public function update(int $id, array $data)
    {
        return $this->put("/usuarios/{$id}", $data);
    }

    public function remove(int $id)
    {
        return parent::delete("/usuarios/{$id}");
    }
}
