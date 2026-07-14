<?php

namespace App\Livewire\Concerns;

use Illuminate\Http\Client\Response;

trait InteractsWithApi
{
    protected function handleApiResponse(Response $response, ?string $successMessage = null): bool
    {
        if ($response->successful()) {
            if ($successMessage) {
                session()->flash('success', $successMessage);
            }
            return true;
        }

        if ($response->status() === 422) {
            foreach ($response->json('errors', []) as $field => $messages) {
                $this->addError($field, $messages[0]);
            }
            return false;
        }

        if ($response->status() === 401) {
            // Si llegamos aquí, ya se intentó refrescar el token y falló igual:
            // la sesión realmente expiró (fuera del refresh_ttl de 2 semanas)
            session()->forget(['api_token', 'api_user']);
            $this->redirect(route('login'), navigate: true);
            return false;
        }

        session()->flash('error', $response->json('message', 'Ocurrió un error al procesar la solicitud.'));
        return false;
    }
}
