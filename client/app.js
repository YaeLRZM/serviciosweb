// Configuración
const API_URL = 'http://localhost:8000/api';
let token = localStorage.getItem('token');
let currentUser = localStorage.getItem('currentUser');

// Inicializar
document.addEventListener('DOMContentLoaded', () => {
    updateTokenInfo();
    if (token) {
        addLog('Token encontrado en localStorage', 'info');
    }
});

// ============ FUNCIONES AUXILIARES ============

function addLog(message, type = 'info') {
    const logsDiv = document.getElementById('logs');
    const timestamp = new Date().toLocaleTimeString();
    const entry = document.createElement('div');
    entry.className = `log-entry ${type}`;
    entry.textContent = `[${timestamp}] ${message}`;
    logsDiv.insertBefore(entry, logsDiv.firstChild);

    // Mantener solo los últimos 50 logs
    while (logsDiv.children.length > 50) {
        logsDiv.removeChild(logsDiv.lastChild);
    }
}

function clearLogs() {
    document.getElementById('logs').innerHTML = '';
    addLog('Logs limpios', 'info');
}

function showResponse(elementId, data, isSuccess = true) {
    const element = document.getElementById(elementId);
    element.className = `response show ${isSuccess ? 'success' : 'error'}`;
    element.textContent = JSON.stringify(data, null, 2);
}

function switchTab(tabName) {
    // Ocultar todos los tabs
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });

    // Desactivar todos los botones
    document.querySelectorAll('.tab-button').forEach(btn => {
        btn.classList.remove('active');
    });

    // Mostrar el tab seleccionado
    document.getElementById(tabName).classList.add('active');

    // Activar el botón correspondiente
    event.target.classList.add('active');
}

function updateTokenInfo() {
    const tokenInfoDiv = document.getElementById('tokenInfo');
    const tokenDetailsDiv = document.getElementById('tokenDetails');

    if (token) {
        tokenInfoDiv.style.display = 'block';

        // Decodificar token
        const parts = token.split('.');
        if (parts.length === 3) {
            try {
                const payload = JSON.parse(atob(parts[1]));
                const iat = new Date(payload.iat * 1000);
                const exp = new Date(payload.exp * 1000);
                const ahora = new Date();
                const tiempoRestante = Math.floor((payload.exp - Date.now() / 1000));

                tokenDetailsDiv.innerHTML = `
                    <strong>Usuario:</strong> ${currentUser || 'Usuario'}<br>
                    <strong>Emitido:</strong> ${iat.toLocaleString()}<br>
                    <strong>Vencimiento:</strong> ${exp.toLocaleString()}<br>
                    <strong>Tiempo restante:</strong> ${tiempoRestante} segundos<br>
                    <strong>Token:</strong> ${token.substring(0, 50)}...
                `;

                addLog(`Token activo - Vencimiento en ${tiempoRestante}s`, 'info');
            } catch (e) {
                tokenDetailsDiv.textContent = 'Error al decodificar token';
            }
        }
    } else {
        tokenInfoDiv.style.display = 'none';
    }
}

// ============ AUTENTICACIÓN ============

async function register() {
    const nombre = document.getElementById('regNombre').value;
    const password = document.getElementById('regPassword').value;
    const rol = document.getElementById('regRol').value;

    if (!nombre || !password) {
        showResponse('regResponse', { error: 'Rellena todos los campos' }, false);
        return;
    }

    try {
        addLog(`Registrando usuario: ${nombre}`, 'info');

        const response = await fetch(`${API_URL}/auth/register`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ nombre, password, rol })
        });

        const data = await response.json();

        if (response.ok) {
            addLog(`Usuario ${nombre} registrado exitosamente`, 'success');
            showResponse('regResponse', data, true);
            document.getElementById('regNombre').value = '';
            document.getElementById('regPassword').value = '';
        } else {
            addLog(`Error en registro: ${data.message}`, 'error');
            showResponse('regResponse', data, false);
        }
    } catch (error) {
        addLog(`Error de conexión: ${error.message}`, 'error');
        showResponse('regResponse', { error: error.message }, false);
    }
}

async function login() {
    const nombre = document.getElementById('loginNombre').value;
    const password = document.getElementById('loginPassword').value;

    if (!nombre || !password) {
        showResponse('loginResponse', { error: 'Rellena todos los campos' }, false);
        return;
    }

    try {
        addLog(`Login: ${nombre}`, 'info');

        const response = await fetch(`${API_URL}/auth/login`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ nombre, password })
        });

        const data = await response.json();

        if (response.ok) {
            token = data.token;
            currentUser = nombre;
            localStorage.setItem('token', token);
            localStorage.setItem('currentUser', currentUser);

            addLog(`Login exitoso como ${nombre}`, 'success');
            showResponse('loginResponse', data, true);
            updateTokenInfo();

            document.getElementById('loginNombre').value = '';
            document.getElementById('loginPassword').value = '';
        } else {
            addLog(`Login fallido: ${data.message}`, 'error');
            showResponse('loginResponse', data, false);
        }
    } catch (error) {
        addLog(`Error de conexión: ${error.message}`, 'error');
        showResponse('loginResponse', { error: error.message }, false);
    }
}

async function logout() {
    try {
        addLog('Cerrando sesión', 'info');

        const response = await fetch(`${API_URL}/auth/logout`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`
            }
        });

        const data = await response.json();

        token = null;
        currentUser = null;
        localStorage.removeItem('token');
        localStorage.removeItem('currentUser');

        addLog('Sesión cerrada', 'success');
        updateTokenInfo();
    } catch (error) {
        addLog(`Error al cerrar sesión: ${error.message}`, 'error');
    }
}

// ============ ARTÍCULOS ============

async function createArticle() {
    if (!token) {
        showResponse('createArtResponse', { error: 'Necesitas estar autenticado' }, false);
        addLog('Necesitas autenticación para crear artículos', 'error');
        return;
    }

    const nombre = document.getElementById('artNombre').value;
    const precio = document.getElementById('artPrecio').value;

    if (!nombre || !precio) {
        showResponse('createArtResponse', { error: 'Rellena todos los campos' }, false);
        return;
    }

    try {
        addLog(`Creando artículo: ${nombre}`, 'info');

        const response = await fetch(`${API_URL}/articles`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify({ nombre, precio: parseFloat(precio) })
        });

        const data = await response.json();

        if (response.ok) {
            addLog(`Artículo ${nombre} creado`, 'success');
            showResponse('createArtResponse', data, true);
            document.getElementById('artNombre').value = '';
            document.getElementById('artPrecio').value = '';
        } else {
            addLog(`Error: ${data.message}`, 'error');
            showResponse('createArtResponse', data, false);
        }
    } catch (error) {
        addLog(`Error: ${error.message}`, 'error');
        showResponse('createArtResponse', { error: error.message }, false);
    }
}

async function getArticles() {
    try {
        addLog('Obteniendo artículos', 'info');

        const response = await fetch(`${API_URL}/articles`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            }
        });

        const data = await response.json();

        if (response.ok) {
            addLog(`${data.length} artículos obtenidos`, 'success');
            showResponse('articlesResponse', data, true);
        } else {
            addLog('Error al obtener artículos', 'error');
            showResponse('articlesResponse', data, false);
        }
    } catch (error) {
        addLog(`Error: ${error.message}`, 'error');
        showResponse('articlesResponse', { error: error.message }, false);
    }
}

async function updateArticle() {
    if (!token) {
        showResponse('updateArtResponse', { error: 'Necesitas estar autenticado' }, false);
        return;
    }

    const id = document.getElementById('updateArtId').value;
    const nombre = document.getElementById('updateArtNombre').value;
    const precio = document.getElementById('updateArtPrecio').value;

    if (!id) {
        showResponse('updateArtResponse', { error: 'Especifica el ID del artículo' }, false);
        return;
    }

    try {
        addLog(`Actualizando artículo ${id}`, 'info');

        const body = {};
        if (nombre) body.nombre = nombre;
        if (precio) body.precio = parseFloat(precio);

        const response = await fetch(`${API_URL}/articles/${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify(body)
        });

        const data = await response.json();

        if (response.ok) {
            addLog(`Artículo ${id} actualizado`, 'success');
            showResponse('updateArtResponse', data, true);
            document.getElementById('updateArtId').value = '';
            document.getElementById('updateArtNombre').value = '';
            document.getElementById('updateArtPrecio').value = '';
        } else {
            addLog(`Error: ${data.message}`, 'error');
            showResponse('updateArtResponse', data, false);
        }
    } catch (error) {
        addLog(`Error: ${error.message}`, 'error');
        showResponse('updateArtResponse', { error: error.message }, false);
    }
}

async function deleteArticle() {
    if (!token) {
        showResponse('deleteArtResponse', { error: 'Necesitas estar autenticado' }, false);
        return;
    }

    const id = document.getElementById('deleteArtId').value;

    if (!id) {
        showResponse('deleteArtResponse', { error: 'Especifica el ID del artículo' }, false);
        return;
    }

    try {
        addLog(`Eliminando artículo ${id}`, 'info');

        const response = await fetch(`${API_URL}/articles/${id}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`
            }
        });

        const data = await response.json();

        if (response.ok) {
            addLog(`Artículo ${id} eliminado`, 'success');
            showResponse('deleteArtResponse', data, true);
            document.getElementById('deleteArtId').value = '';
        } else {
            addLog(`Error: ${data.message}`, 'error');
            showResponse('deleteArtResponse', data, false);
        }
    } catch (error) {
        addLog(`Error: ${error.message}`, 'error');
        showResponse('deleteArtResponse', { error: error.message }, false);
    }
}

// ============ USUARIOS ============

async function getUsers() {
    try {
        addLog('Obteniendo usuarios', 'info');

        const response = await fetch(`${API_URL}/users`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            }
        });

        const data = await response.json();

        if (response.ok) {
            addLog(`${data.length} usuarios obtenidos`, 'success');
            showResponse('usersResponse', data, true);
        } else {
            addLog('Error al obtener usuarios', 'error');
            showResponse('usersResponse', data, false);
        }
    } catch (error) {
        addLog(`Error: ${error.message}`, 'error');
        showResponse('usersResponse', { error: error.message }, false);
    }
}

async function getUser() {
    const id = document.getElementById('getUserId').value;

    if (!id) {
        showResponse('getUserResponse', { error: 'Especifica el ID del usuario' }, false);
        return;
    }

    try {
        addLog(`Obteniendo usuario ${id}`, 'info');

        const response = await fetch(`${API_URL}/users/${id}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            }
        });

        const data = await response.json();

        if (response.ok) {
            addLog(`Usuario ${id} obtenido`, 'success');
            showResponse('getUserResponse', data, true);
        } else {
            addLog(`Error: ${data.message}`, 'error');
            showResponse('getUserResponse', data, false);
        }
    } catch (error) {
        addLog(`Error: ${error.message}`, 'error');
        showResponse('getUserResponse', { error: error.message }, false);
    }
}

async function updateUser() {
    if (!token) {
        showResponse('updateUserResponse', { error: 'Necesitas estar autenticado' }, false);
        return;
    }

    const id = document.getElementById('updateUserId').value;

    if (!id) {
        showResponse('updateUserResponse', { error: 'Especifica el ID del usuario' }, false);
        return;
    }

    try {
        addLog(`Actualizando usuario ${id}`, 'info');

        const body = {};
        const nombre = document.getElementById('updateUserNombre').value;
        const password = document.getElementById('updateUserPassword').value;
        const rol = document.getElementById('updateUserRol').value;

        if (nombre) body.nombre = nombre;
        if (password) body.password = password;
        if (rol) body.rol = rol;

        const response = await fetch(`${API_URL}/users/${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify(body)
        });

        const data = await response.json();

        if (response.ok) {
            addLog(`Usuario ${id} actualizado`, 'success');
            showResponse('updateUserResponse', data, true);
            document.getElementById('updateUserId').value = '';
            document.getElementById('updateUserNombre').value = '';
            document.getElementById('updateUserPassword').value = '';
            document.getElementById('updateUserRol').value = '';
        } else {
            addLog(`Error: ${data.message}`, 'error');
            showResponse('updateUserResponse', data, false);
        }
    } catch (error) {
        addLog(`Error: ${error.message}`, 'error');
        showResponse('updateUserResponse', { error: error.message }, false);
    }
}

async function deleteUser() {
    if (!token) {
        showResponse('deleteUserResponse', { error: 'Necesitas estar autenticado' }, false);
        return;
    }

    const id = document.getElementById('deleteUserId').value;

    if (!id) {
        showResponse('deleteUserResponse', { error: 'Especifica el ID del usuario' }, false);
        return;
    }

    try {
        addLog(`Eliminando usuario ${id}`, 'info');

        const response = await fetch(`${API_URL}/users/${id}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`
            }
        });

        const data = await response.json();

        if (response.ok) {
            addLog(`Usuario ${id} eliminado`, 'success');
            showResponse('deleteUserResponse', data, true);
            document.getElementById('deleteUserId').value = '';
        } else {
            addLog(`Error: ${data.message}`, 'error');
            showResponse('deleteUserResponse', data, false);
        }
    } catch (error) {
        addLog(`Error: ${error.message}`, 'error');
        showResponse('deleteUserResponse', { error: error.message }, false);
    }
}

// ============ STATUS ============

async function checkApiStatus() {
    try {
        addLog('Verificando estado de la API', 'info');

        const response = await fetch(`${API_URL}/test`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            }
        });

        const data = await response.json();

        if (response.ok) {
            addLog('API funcionando correctamente', 'success');
            const status = {
                status: 'OK',
                message: data.message,
                timestamp: new Date().toLocaleString(),
                cors: 'Habilitado',
                jwt_ttl: '2 minutos'
            };
            showResponse('statusResponse', status, true);
        } else {
            addLog('Error en la API', 'error');
            showResponse('statusResponse', data, false);
        }
    } catch (error) {
        addLog(`Error de conexión: ${error.message}`, 'error');
        showResponse('statusResponse', { error: error.message }, false);
    }
}

// Actualizar token info cada segundo
setInterval(updateTokenInfo, 1000);
