/**
 * Estabilidad de sesión del panel admin.
 *
 * Causa del popup "This page has expired...":
 * - Livewire recibe HTTP 419 (TokenMismatch / sesión CSRF vencida)
 * - y muestra confirm() en inglés del navegador.
 *
 * Aquí:
 * 1) keep-alive periódico para renovar la sesión y el token CSRF;
 * 2) interceptar 419 de Livewire sin confirm() del navegador;
 * 3) redirigir al login con mensaje amable.
 */

const KEEP_ALIVE_URL = '/session/keep-alive';
const KEEP_ALIVE_MS = 8 * 60 * 1000; // cada 8 minutos (sesión 8 h)

function loginExpiredUrl() {
    return '/login?expired=1';
}

function updateCsrfToken(token) {
    if (!token) return;

    const meta = document.querySelector('meta[name="csrf-token"]');
    if (meta) {
        meta.setAttribute('content', token);
    }

    // Axios (si se usa en el panel).
    if (window.axios?.defaults?.headers) {
        window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
    }

    // Inputs ocultos de formularios clásicos.
    document.querySelectorAll('input[name="_token"]').forEach((input) => {
        input.value = token;
    });

    // Livewire lee el token del meta; forzar si expone helper.
    if (window.Livewire && typeof window.Livewire.find === 'function') {
        // no-op: Livewire toma el meta en cada request
    }
}

async function keepSessionAlive() {
    try {
        const token = document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content');

        const response = await fetch(KEEP_ALIVE_URL, {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                ...(token ? { 'X-CSRF-TOKEN': token } : {}),
            },
        });

        if (response.status === 401 || response.status === 419) {
            window.location.href = loginExpiredUrl();
            return;
        }

        if (!response.ok) {
            return;
        }

        const data = await response.json();
        if (data?.csrf) {
            updateCsrfToken(data.csrf);
        }
    } catch (_) {
        // Sin red: no interrumpir al usuario; el próximo ping reintentará.
    }
}

function handleSessionExpired() {
    // Evitar bucles de redirección.
    if (window.__ixeSessionRedirecting) return;
    window.__ixeSessionRedirecting = true;
    window.location.href = loginExpiredUrl();
}

function installLivewire419Handler() {
    document.addEventListener('livewire:init', () => {
        if (!window.Livewire?.hook) return;

        // Livewire v3: interceptar fallos de request y anular el confirm() nativo en 419.
        Livewire.hook('request', ({ fail }) => {
            fail(({ status, preventDefault }) => {
                if (status === 419) {
                    preventDefault();
                    handleSessionExpired();
                }
            });
        });
    });
}

function installVisibilityRefresh() {
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') {
            keepSessionAlive();
        }
    });

    window.addEventListener('focus', () => {
        keepSessionAlive();
    });
}

function startKeepAlive() {
    // Primer toque al cargar el panel.
    keepSessionAlive();
    window.setInterval(keepSessionAlive, KEEP_ALIVE_MS);
}

// Solo en el panel (layout admin carga este script).
installLivewire419Handler();
installVisibilityRefresh();

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', startKeepAlive);
} else {
    startKeepAlive();
}
