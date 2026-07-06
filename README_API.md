# API Rest - CRUD Usuarios y Artículos

## Descripción

API REST con autenticación JWT, gestión de usuarios y artículos con almacenamiento en JSON local.

## Requisitos

- PHP 8.2+
- Composer
- Laravel 12

## Instalación

1. Clonar o descargar el proyecto
2. Ejecutar: `composer install`
3. Copiar `.env.example` a `.env`
4. Ejecutar: `php artisan key:generate`
5. Ejecutar: `php artisan jwt:secret`

## Iniciar el servidor

```bash
php artisan serve
```

El servidor estará disponible en `http://localhost:8000`

## Endpoints

### Autenticación

**Registrar nuevo usuario:**
```
POST /api/auth/register
Content-Type: application/json

{
  "nombre": "Juan Pérez",
  "password": "123456",
  "rol": "admin"
}
```

**Respuesta (201):**
```json
{
  "message": "Usuario registrado exitosamente",
  "user": {
    "id": 1,
    "nombre": "Juan Pérez",
    "rol": "admin"
  }
}
```

**Login:**
```
POST /api/auth/login
Content-Type: application/json

{
  "nombre": "Juan Pérez",
  "password": "123456"
}
```

**Respuesta (200):**
```json
{
  "message": "Login exitoso",
  "token": "eyJhbGc...",
  "user": {
    "id": 1,
    "nombre": "Juan Pérez",
    "rol": "admin"
  }
}
```

**Logout:**
```
POST /api/auth/logout
Authorization: Bearer {token}
```

### Usuarios (requiere autenticación)

**Obtener todos los usuarios:**
```
GET /api/users
Authorization: Bearer {token}
```

**Obtener usuario por ID:**
```
GET /api/users/{id}
Authorization: Bearer {token}
```

**Actualizar usuario:**
```
PUT /api/users/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "nombre": "Nuevo Nombre",
  "password": "nueva_contraseña",
  "rol": "user"
}
```

**Eliminar usuario:**
```
DELETE /api/users/{id}
Authorization: Bearer {token}
```

### Artículos (requieren autenticación)

**Obtener todos los artículos:**
```
GET /api/articles
Authorization: Bearer {token}
```

**Crear artículo:**
```
POST /api/articles
Authorization: Bearer {token}
Content-Type: application/json

{
  "nombre": "Laptop",
  "precio": 999.99
}
```

**Obtener artículo por ID:**
```
GET /api/articles/{id}
Authorization: Bearer {token}
```

**Actualizar artículo:**
```
PUT /api/articles/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "nombre": "Nuevo nombre",
  "precio": 1299.99
}
```

**Eliminar artículo:**
```
DELETE /api/articles/{id}
Authorization: Bearer {token}
```

## Documentación Swagger

La documentación interactiva de la API está disponible en:
```
http://localhost:8000/api/documentation
```

## Almacenamiento de Datos

Los datos se almacenan en archivos JSON en la carpeta `storage/app/data/`:

- `users.json` - Almacena todos los usuarios
- `articles.json` - Almacena todos los artículos

## Autenticación JWT

Todos los endpoints (excepto register y login) requieren un token JWT valido en el header:

```
Authorization: Bearer {token}
```

El token se obtiene al hacer login y expira después de 1 hora (configurable en `config/jwt.php`).

## Manejo de Errores

La API devuelve códigos de estado HTTP estándar:

- `200` - OK
- `201` - Created
- `400` - Bad Request (validación fallida)
- `401` - Unauthorized (token inválido o expirado)
- `404` - Not Found (recurso no encontrado)
- `500` - Internal Server Error

## Ejemplo de uso con cURL

```bash
# 1. Registrar un usuario
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{"nombre":"Juan","password":"123456","rol":"admin"}'

# 2. Login
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"nombre":"Juan","password":"123456"}'

# 3. Crear artículo (usar el token obtenido)
curl -X POST http://localhost:8000/api/articles \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {TOKEN}" \
  -d '{"nombre":"Laptop","precio":999.99}'

# 4. Obtener artículos
curl -H "Authorization: Bearer {TOKEN}" \
  http://localhost:8000/api/articles
```
