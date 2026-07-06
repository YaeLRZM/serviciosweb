<?php

/**
 * @OA\Info(
 *     title="API Rest - CRUD Usuarios y Artículos",
 *     version="1.0.0",
 *     description="API REST con autenticación JWT, gestión de usuarios y artículos con almacenamiento en JSON",
 *     @OA\Contact(
 *         email="admin@example.com"
 *     )
 * )
 * @OA\Server(
 *     url="http://localhost:8000",
 *     description="Servidor Local"
 * )
 * @OA\Components(
 *     @OA\SecurityScheme(
 *         type="http",
 *         description="Login con usuario y contraseña para obtener el token JWT",
 *         name="Token JWT",
 *         in="header",
 *         scheme="bearer",
 *         bearerFormat="JWT",
 *         securityScheme="bearerAuth"
 *     )
 * )
 */
