# 🚀 Guía de Inicio Rápido

Instrucciones para ejecutar la API REST y el cliente web con CORS.

## 📋 Requisitos

- PHP 8.2+
- Node.js 12+
- Composer (para Laravel)
- NPM (para cliente)

## 🔧 Configuración Inicial (Solo la primera vez)

### 1. Instalar dependencias de Laravel

```bash
cd testlaravel
composer install
```

### 2. Instalar dependencias del cliente

```bash
cd testlaravel/client
npm install
```

## ▶️ Ejecutar la Aplicación

### Terminal 1: Ejecutar la API Laravel (Puerto 8000)

```bash
cd testlaravel
php artisan serve
```

Debería mostrar:
```
   INFO  Server running on [http://127.0.0.1:8000].
```

### Terminal 2: Ejecutar el cliente web (Puerto 3000)

```bash
cd testlaravel/client
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

## 🌐 Acceso

Abre tu navegador y ve a:
- **Cliente Web:** http://localhost:3000
- **API Documentation:** http://localhost:8000/api/documentation (si está configurado)

## 📝 Primeros Pasos

### 1. Registrar un usuario

1. Ve a la pestaña "Autenticación"
2. Rellena el formulario de "Registro"
   - Nombre: `test`
   - Contraseña: `123456`
   - Rol: `admin`
3. Haz clic en "Registrarse"

### 2. Iniciar sesión

1. Rellena el formulario de "Login"
   - Usuario: `test`
   - Contraseña: `123456`
2. Haz clic en "Iniciar Sesión"
3. Se mostrará tu token activo (válido por 2 minutos)

### 3. Crear un artículo

1. Ve a la pestaña "Artículos"
2. En "Crear Artículo":
   - Nombre: `Laptop`
   - Precio: `999.99`
3. Haz clic en "Crear"
4. Deberías ver la respuesta con el nuevo artículo

### 4. Ver artículos

1. En "Listar Artículos" haz clic en "Obtener Artículos"
2. Se mostrarán todos los artículos (sin autenticación requerida)

## 🔐 Sistema de Permisos

### Operaciones Públicas (Sin autenticación)
- ✅ `GET /api/articles` - Ver artículos
- ✅ `GET /api/articles/{id}` - Ver artículo específico
- ✅ `GET /api/users` - Ver usuarios
- ✅ `GET /api/users/{id}` - Ver usuario específico

### Operaciones Protegidas (Solo ADMIN)
- ❌ `POST /api/articles` - Crear artículos
- ❌ `PUT /api/articles/{id}` - Actualizar artículos
- ❌ `DELETE /api/articles/{id}` - Eliminar artículos
- ❌ `PUT /api/users/{id}` - Actualizar usuarios
- ❌ `DELETE /api/users/{id}` - Eliminar usuarios

### Operaciones de Autenticación
- ✅ `POST /api/auth/register` - Registrar usuario
- ✅ `POST /api/auth/login` - Iniciar sesión
- ✅ `POST /api/auth/logout` - Cerrar sesión (requiere JWT)

## 🔍 Prueba de CORS

El cliente está en **puerto 3000** y consume la API en **puerto 8000**.

Las solicitudes CORS funcionan correctamente si:
1. ✅ Puedes ver artículos sin autenticación
2. ✅ Puedes registrar usuarios
3. ✅ Puedes crear artículos (con token admin)
4. ✅ Ves errores de permiso (403) cuando intentas acciones sin admin

## 🧪 Usuarios de Prueba

Después de ejecutar los tests, puedes usar estos usuarios:

```
Usuario: testuser
Contraseña: testpass123
Rol: user
```

```
Usuario: admin_user
Contraseña: admin123
Rol: admin
```

## 📊 Información del Sistema

| Componente | Puerto | URL | Estado |
|------------|--------|-----|--------|
| API REST | 8000 | http://localhost:8000 | ✅ Laravel |
| Cliente Web | 3000 | http://localhost:3000 | ✅ Express |
| CORS | - | - | ✅ Habilitado |
| JWT TTL | - | - | ⏱️ 2 minutos |
| Almacenamiento | - | storage/app/data/ | 📁 JSON |

## 🛠 Desarrollo

### Crear artículos vía cliente

```javascript
// En la consola de navegador (F12)
fetch('http://localhost:8000/api/articles', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${localStorage.getItem('token')}`
    },
    body: JSON.stringify({
        nombre: 'Producto Test',
        precio: 99.99
    })
}).then(r => r.json()).then(console.log)
```

### Ver logs de la API

```bash
cd testlaravel
tail -f storage/logs/laravel.log
```

### Limpiar datos

Los datos se guardan en archivos JSON:
- `storage/app/data/users.json` - Usuarios
- `storage/app/data/articles.json` - Artículos

Para limpiar, simplemente borra estos archivos.

## 🐛 Problemas Comunes

### "Cannot find module 'express'"
```bash
cd testlaravel/client
npm install
```

### Error 403 Forbidden al crear artículos
Tu usuario no es admin. Usa `admin_user` o registra un nuevo admin con rol "admin".

### Error 401 Unauthenticated
Tu token ha expirado (máximo 2 minutos). Inicia sesión nuevamente.

### Error de CORS
- Verifica que la API está en http://localhost:8000
- Verifica que el cliente está en http://localhost:3000
- Abre la consola del navegador (F12) para ver detalles del error

### Puerto 3000 en uso
```bash
# Cambiar puerto en client/server.js línea 4
const PORT = 3001; // O cualquier puerto disponible
```

## 📚 Documentación

Para más información:
- **API:** Ver `README_API.md`
- **Cliente:** Ver `client/README.md`
- **Tests:** Ver `test_*.php` en la raíz

## 🎯 Próximos Pasos

1. ✅ Ejecutar API en puerto 8000
2. ✅ Ejecutar cliente en puerto 3000
3. ✅ Probar CORS desde el navegador
4. ✅ Verificar autenticación JWT
5. ✅ Probar permisos de roles
6. ✅ Crear/actualizar/eliminar artículos

---

¡Listo! Tu aplicación REST con CORS está funcionando. 🎉
