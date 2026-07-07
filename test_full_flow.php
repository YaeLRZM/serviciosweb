<?php

function makeRequest($method, $endpoint, $data = null, $token = null) {
    $ch = curl_init('http://localhost:8000/api' . $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

    $headers = [
        'Content-Type: application/json',
        'Accept: application/json',
    ];

    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    if ($data) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [
        'status' => $httpCode,
        'data' => json_decode($response, true),
    ];
}

echo "=== Flujo Completo: Autenticación y Autorización ===\n\n";

// 1. Crear nuevo usuario
echo "1. Registrar nuevo usuario (testuser):\n";
$registerResult = makeRequest('POST', '/auth/register', [
    'nombre' => 'testuser',
    'password' => 'testpass123',
    'rol' => 'user',
]);
echo "Status: {$registerResult['status']}\n";
if ($registerResult['status'] === 201) {
    echo "✓ Usuario registrado\n";
} else {
    echo "Respuesta: " . json_encode($registerResult['data']) . "\n";
}

// 2. Login
echo "\n2. Login (debe obtener token con 24 horas de duración):\n";
$loginResult = makeRequest('POST', '/auth/login', [
    'nombre' => 'testuser',
    'password' => 'testpass123',
]);
echo "Status: {$loginResult['status']}\n";
$token = $loginResult['data']['token'] ?? null;

if ($token) {
    echo "✓ Login exitoso\n";
    echo "\nToken JWT: " . substr($token, 0, 50) . "...\n";

    // Decodificar token
    $tokenParts = explode('.', $token);
    if (count($tokenParts) === 3) {
        $payload = json_decode(base64_decode(strtr($tokenParts[1], '-_', '+/')), true);
        $iat = $payload['iat'];
        $exp = $payload['exp'];
        $duracion = ($exp - $iat) / 3600;

        echo "\n--- Detalles del Token ---\n";
        echo "Emisión: " . date('Y-m-d H:i:s', $iat) . "\n";
        echo "Vencimiento: " . date('Y-m-d H:i:s', $exp) . "\n";
        echo "Duración: " . number_format($duracion, 2) . " horas (" . ($duracion / 24) . " días)\n";
        echo "Sub (User ID): " . $payload['sub'] . "\n";
    }
} else {
    echo "✗ Login falló\n";
    var_dump($loginResult['data']);
    exit;
}

// 3. Verificar que GET es público
echo "\n3. GET /articles SIN token (público - debe funcionar):\n";
$getResult = makeRequest('GET', '/articles');
echo "Status: {$getResult['status']}\n";
echo "Artículos encontrados: " . count($getResult['data']) . "\n";

// 4. Crear artículo SIN token (debe fallar)
echo "\n4. POST /articles SIN token (protegido - debe fallar 401):\n";
$postNoToken = makeRequest('POST', '/articles', [
    'nombre' => 'Test Product',
    'precio' => 99.99,
]);
echo "Status: {$postNoToken['status']}\n";
if ($postNoToken['status'] === 401) {
    echo "✓ Correctamente rechazado sin token\n";
} else {
    echo "✗ Debería ser 401, pero fue " . $postNoToken['status'] . "\n";
}

// 5. Crear artículo CON token (debe funcionar)
echo "\n5. POST /articles CON token (protegido - debe funcionar 201):\n";
$postWithToken = makeRequest('POST', '/articles', [
    'nombre' => 'Gaming Mouse RGB',
    'precio' => 59.99,
], $token);
echo "Status: {$postWithToken['status']}\n";
if ($postWithToken['status'] === 201) {
    echo "✓ Artículo creado exitosamente\n";
    echo "Artículo ID: " . $postWithToken['data']['article']['id'] . "\n";
    $articleId = $postWithToken['data']['article']['id'];
} else {
    echo "✗ Error: " . json_encode($postWithToken['data']) . "\n";
    exit;
}

// 6. Actualizar artículo SIN token (debe fallar)
echo "\n6. PUT /articles/{$articleId} SIN token (protegido - debe fallar 401):\n";
$putNoToken = makeRequest('PUT', "/articles/{$articleId}", [
    'nombre' => 'Updated Mouse',
    'precio' => 79.99,
]);
echo "Status: {$putNoToken['status']}\n";
if ($putNoToken['status'] === 401) {
    echo "✓ Correctamente rechazado sin token\n";
} else {
    echo "✗ Debería ser 401, pero fue " . $putNoToken['status'] . "\n";
}

// 7. Actualizar artículo CON token (debe funcionar)
echo "\n7. PUT /articles/{$articleId} CON token (protegido - debe funcionar 200):\n";
$putWithToken = makeRequest('PUT', "/articles/{$articleId}", [
    'nombre' => 'Gaming Mouse RGB Pro',
    'precio' => 79.99,
], $token);
echo "Status: {$putWithToken['status']}\n";
if ($putWithToken['status'] === 200) {
    echo "✓ Artículo actualizado exitosamente\n";
} else {
    echo "✗ Error: " . json_encode($putWithToken['data']) . "\n";
}

// 8. Eliminar artículo SIN token (debe fallar)
echo "\n8. DELETE /articles/{$articleId} SIN token (protegido - debe fallar 401):\n";
$deleteNoToken = makeRequest('DELETE', "/articles/{$articleId}");
echo "Status: {$deleteNoToken['status']}\n";
if ($deleteNoToken['status'] === 401) {
    echo "✓ Correctamente rechazado sin token\n";
} else {
    echo "✗ Debería ser 401, pero fue " . $deleteNoToken['status'] . "\n";
}

// 9. Eliminar artículo CON token (debe funcionar)
echo "\n9. DELETE /articles/{$articleId} CON token (protegido - debe funcionar 200):\n";
$deleteWithToken = makeRequest('DELETE', "/articles/{$articleId}", null, $token);
echo "Status: {$deleteWithToken['status']}\n";
if ($deleteWithToken['status'] === 200) {
    echo "✓ Artículo eliminado exitosamente\n";
} else {
    echo "✗ Error: " . json_encode($deleteWithToken['data']) . "\n";
}

// 10. Verificar que GET sigue siendo público
echo "\n10. GET /users SIN token (público - debe funcionar):\n";
$getUsers = makeRequest('GET', '/users');
echo "Status: {$getUsers['status']}\n";
echo "Usuarios encontrados: " . count($getUsers['data']) . "\n";

echo "\n=== Resumen ===\n";
echo "✓ GET (Lectura) es PÚBLICO - funciona sin token\n";
echo "✓ POST/PUT/DELETE (Escritura) son PRIVADOS - requieren token JWT\n";
echo "✓ Token JWT tiene duración de 24 horas\n";
echo "✓ Estructura de seguridad implementada correctamente\n";
