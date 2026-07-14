<?php

namespace App\Services\Api;

class DashboardApiService extends ApiClient
{
    public function ventasPorRegion(string $categoria)
    {
        return $this->get('/dashboard/ventas-por-region', ['categoria' => $categoria]);
    }

    public function alertasModeracion()
    {
        return $this->get('/dashboard/alertas-moderacion');
    }

    public function vendedoresDestacados()
    {
        return $this->get('/dashboard/vendedores-destacados');
    }
}
