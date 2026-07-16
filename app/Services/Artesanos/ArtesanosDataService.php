<?php

namespace App\Services\Artesanos;

use App\Models\Artesano;
use App\Support\Mock\ArtesanosMock;

class ArtesanosDataService
{
    private const ESTADOS_COLA = ['revision', 'nueva', 'documentos'];

    private const ESTADO_LABELS = [
        'revision' => 'En revisión',
        'nueva' => 'Nueva solicitud',
        'documentos' => 'Documentación pendiente',
    ];

    protected function usarMock(): bool
    {
        return (bool) config('features.mock_artesanos', true);
    }

    /**
     * Solicitudes pendientes de revisión, para la cola de verificación.
     */
    public function colaVerificacion(): array
    {
        if ($this->usarMock()) {
            return ArtesanosMock::colaVerificacion();
        }

        return Artesano::query()
            ->whereIn('estado', self::ESTADOS_COLA)
            ->get()
            ->map(fn (Artesano $a) => [
                ...$a->toArray(),
                'estadoLabel' => self::ESTADO_LABELS[$a->estado] ?? $a->estado,
                'accionLabel' => $a->estado === 'documentos' ? 'Contactar' : 'Ver detalle',
            ])
            ->values()
            ->all();
    }

    /**
     * Socios artesanos ya aprobados, ordenados por ventas totales.
     */
    public function activos(): array
    {
        if ($this->usarMock()) {
            return ArtesanosMock::activos();
        }

        return Artesano::query()
            ->where('estado', 'aprobado')
            ->orderByDesc('ventas_total')
            ->get()
            ->map(fn (Artesano $a) => [...$a->toArray(), 'verificado' => true])
            ->values()
            ->all();
    }

    public function find(int $id): ?array
    {
        if ($this->usarMock()) {
            return ArtesanosMock::find($id);
        }

        return Artesano::find($id)?->toArray();
    }

    public function guardarDictamen(int $id, string $dictamen, string $notas): void
    {
        if ($this->usarMock()) {
            ArtesanosMock::guardarDictamen($id, $dictamen, $notas);

            return;
        }

        $nuevoEstado = match ($dictamen) {
            'Aprobar' => 'aprobado',
            'Rechazar' => 'rechazado',
            default => 'documentos', // 'Solicitar información'
        };

        Artesano::findOrFail($id)->update([
            'estado' => $nuevoEstado,
            'notas_moderacion' => $notas,
        ]);
    }

    public function alternarDestacado(int $id): void
    {
        if ($this->usarMock()) {
            ArtesanosMock::alternarDestacado($id);

            return;
        }

        $artesano = Artesano::findOrFail($id);
        $artesano->update(['destacado' => ! $artesano->destacado]);
    }
}
