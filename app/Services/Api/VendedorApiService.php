<?php

namespace App\Services\Api;

class VendedorApiService extends ApiClient
{
    /**
     * Listado de vendedores (opcionalmente filtrado).
     *
     * Endpoint esperado: GET /vendedores
     * Query opcional: estatus, busqueda
     */
    public function all(array $filters = [])
    {
        return $this->get('/vendedores', $filters);
    }

    /**
     * Solicitudes pendientes de verificación para ser vendedor.
     *
     * Endpoint esperado: GET /vendedores/solicitudes
     */
    public function solicitudes()
    {
        return $this->get('/vendedores/solicitudes');
    }

    /**
     * Detalle de un vendedor / solicitud.
     *
     * Endpoint esperado: GET /vendedores/{id}
     */
    public function find(int $id)
    {
        return $this->get("/vendedores/{$id}");
    }

    /**
     * Actualiza el estatus de verificación (aceptar / rechazar / etc.).
     *
     * Endpoint esperado: PATCH /vendedores/{id}/estatus
     * Body: { "estatus": "Verificado" | "Rechazado" | "En Revisión" | "Suspendido" }
     */
    public function actualizarEstatus(int $id, string $estatus)
    {
        return $this->patch("/vendedores/{$id}/estatus", ['estatus' => $estatus]);
    }
}
