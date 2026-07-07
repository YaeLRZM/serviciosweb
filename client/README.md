# Cliente Web API REST - CORS Test

Cliente web interactivo para consumir la API REST implementada en Laravel. Se ejecuta en un puerto diferente (3000) para probar CORS.

## 🚀 Características

- ✅ Interfaz web moderna y responsiva
- ✅ CORS habilitado (consumo desde diferente puerto)
- ✅ Sistema de autenticación JWT
- ✅ Gestión de artículos y usuarios
- ✅ Sistema de roles (admin/user)
- ✅ Consola de logs en tiempo real
- ✅ Almacenamiento de token en localStorage
- ✅ Visualización de duración del token

## 📋 Requisitos

- Node.js 12+ instalado
- NPM (incluido con Node.js)
- API Laravel ejecutándose en http://localhost:8000

## 🔧 Instalación

### 1. Instalar dependencias

```bash
cd client
npm install
```

### 2. Verificar que la API está corriendo

En otra terminal:
```bash
cd testlaravel
php artisan serve
```

Debería mostrar:
```
   INFO  Server running on [http://127.0.0.1:8000].
```

### 3. Iniciar el cliente

```bash
npm start
```

Debería mostrar:
```
╔═══════════════════════════════════════╗
║  Cliente API ejecutándose en:         ║
║  http://localhost:3000                ║
║                                       ║
║  API disponible en:                   ║
║  http://localhost:8000                ║
╚═══════════════════════════════════════╝
```

## 🎯 Acceso

Abre tu navegador y ve a:
```
http://localhost:3000
```

## 📖 Cómo Usar

### 1. Autenticación

**Registro:**
- Rellena nombre, contraseña y rol
- Haz clic en "Registrarse"
- Se mostrará la confirmación

**Login:**
- Ingresa usuario y contraseña
- Haz clic en "Iniciar Sesión"
- Se guardará el token en localStorage
- Se mostrará el token activo con duración

### 2. Artículos

**Listar (Público):**
- Haz clic en "Obtener Artículos"
- No requiere autenticación

**Crear (Requiere Admin):**
- Ingresa nombre y precio
- Haz clic en "Crear"
- Solo usuarios con rol admin pueden crear

**Actualizar (Requiere Admin):**
- Ingresa ID, nombre y precio
- Haz clic en "Actualizar"
- Solo admin puede actualizar

**Eliminar (Requiere Admin):**
- Ingresa ID del artículo
- Haz clic en "Eliminar"
- Solo admin puede eliminar

### 3. Usuarios

**Listar (Público):**
- Haz clic en "Obtener Usuarios"
- Muestra todos los usuarios sin mostrar contraseñas

**Obtener por ID (Público):**
- Ingresa ID
- Muestra datos del usuario

**Actualizar (Requiere Admin):**
- Ingresa ID y los datos a cambiar
- Solo admin puede actualizar

**Eliminar (Requiere Admin):**
- Ingresa ID del usuario
- Solo admin puede eliminar

## 🔐 Roles y Permisos

### Usuario (user)
- ✅ Ver artículos (GET)
- ✅ Ver usuarios (GET)
- ❌ Crear/Actualizar/Eliminar (403 Forbidden)

### Administrador (admin)
- ✅ Ver artículos (GET)
- ✅ Crear artículos (POST)
- ✅ Actualizar artículos (PUT)
- ✅ Eliminar artículos (DELETE)
- ✅ Ver usuarios (GET)
- ✅ Actualizar usuarios (PUT)
- ✅ Eliminar usuarios (DELETE)

## 👥 Usuarios de Prueba

### Usuario Regular
- **Usuario:** testuser
- **Contraseña:** testpass123
- **Rol:** user

### Administrador
- **Usuario:** admin_user
- **Contraseña:** admin123
- **Rol:** admin

## 🔗 Relación con API

| Método | Endpoint | Puerto | Autenticación | Descripción |
|--------|----------|--------|---|---|
| GET | /api/articles | 8000 | No | Listar artículos |
| POST | /api/articles | 8000 | JWT + Admin | Crear artículo |
| PUT | /api/articles/{id} | 8000 | JWT + Admin | Actualizar |
| DELETE | /api/articles/{id} | 8000 | JWT + Admin | Eliminar |
| GET | /api/users | 8000 | No | Listar usuarios |
| GET | /api/users/{id} | 8000 | No | Obtener usuario |
| POST | /api/auth/register | 8000 | No | Registrar |
| POST | /api/auth/login | 8000 | No | Login |
| POST | /api/auth/logout | 8000 | JWT | Logout |

## 📊 Características de CORS

El cliente está en puerto 3000 y consume la API en puerto 8000. Las solicitudes CORS son manejadas por:

- **Header CORS en Express:** `app.use(cors())`
- **API Laravel:** Acepta solicitudes desde cualquier origen
- **Métodos soportados:** GET, POST, PUT, DELETE
- **Headers soportados:** Content-Type, Authorization

## 🛠 Desarrollo

### Estructura del Proyecto

```
client/
├── index.html       # Página principal
├── style.css        # Estilos
├── app.js          # Lógica JavaScript
├── server.js       # Servidor Express
├── package.json    # Dependencias
└── README.md       # Este archivo
```

### Modificar

Para cambiar la URL de la API, edita `app.js`:

```javascript
const API_URL = 'http://localhost:8000/api'; // Aquí
```

## 🐛 Solución de Problemas

### Error: "Cannot find module 'express'"
```bash
npm install
```

### Error de CORS
- Verifica que la API está corriendo en puerto 8000
- Verifica que el cliente está en puerto 3000
- Revisa la consola del navegador (F12)

### Error 401 Unauthenticated
- Haz login primero
- Verifica que el token no ha expirado (máximo 2 minutos)
- Token se guarda en localStorage

### Error 403 Forbidden
- Tu usuario no tiene permisos para esta acción
- Usa usuario admin para operaciones protegidas
- O registra un nuevo admin

## 📝 Consola de Logs

El cliente muestra logs en tiempo real en la pestaña "Estado":
- **Verde:** Operaciones exitosas
- **Rojo:** Errores
- **Azul:** Información general

## 🎨 Interfaz

- **Pestaña Autenticación:** Login, registro, token activo
- **Pestaña Artículos:** CRUD de artículos
- **Pestaña Usuarios:** CRUD de usuarios
- **Pestaña Estado:** Estado de la API, información del sistema, logs

## 🔄 Actualización Automática

El token se actualiza automáticamente cada segundo mostrando:
- Tiempo restante
- Fecha de emisión
- Fecha de vencimiento

## 📞 Soporte

Para más información sobre la API, ver:
- `../README_API.md` - Documentación de la API
- `../storage/api-docs/api-docs.json` - Especificación OpenAPI

---

**Nota:** El cliente y la API funcionan juntos. Asegúrate de que ambos están corriendo en sus respectivos puertos.
