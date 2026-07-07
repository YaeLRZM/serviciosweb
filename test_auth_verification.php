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

echo "=== Verificación de Autenticación y Autorización ===\n\n";

// 1. Verificar que GET es público
echo "1. GET /users SIN token (debe funcionar):\n";
$result = makeRequest('GET', '/users');
echo "Status: {$result['status']}\n";
var_dump($result['data']);

echo "\n2. GET /articles SIN token (debe funcionar):\n";
$result = makeRequest('GET', '/articles');
echo "Status: {$result['status']}\n";
var_dump($result['data']);

// 2. Obtener token
echo "\n3. Login para obtener token:\n";
$loginResult = makeRequest('POST', '/auth/login', [
    'nombre' => 'Juan Pérez',
    'password' => '123456',
]);
echo "Status: {$loginResult['status']}\n";
$token = $loginResult['data']['token'] ?? null;
if ($token) {
    echo "✓ Token obtenido\n";
    echo "Token: " . substr($token, 0, 50) . "...\n";

    // Decodificar el token para ver la información
    $tokenParts = explode('.', $token);
    if (count($tokenParts) === 3) {
        $payload = json_decode(base64_decode(strtr($tokenParts[1], '-_', '+/')), true);
        echo "\nTiempo de emisión: " . date('Y-m-d H:i:s', $payload['iat']) . "\n";
        echo "Tiempo de vencimiento: " . date('Y-m-d H:i:s', $payload['exp']) . "\n";
        echo "Duración: " . (($payload['exp'] - $payload['iat']) / 3600) . " horas\n";
    }
}

// 3. Intentar POST sin token
echo "\n4. POST /articles SIN token (debe fallar con 401):\n";
$result = makeRequest('POST', '/articles', [
    'nombre' => 'Test',
    'precio' => 100,
]);
echo "Status: {$result['status']}\n";
echo "Respuesta: ";
var_dump($result['data']);

// 4. POST con token (debe funcionar)
if ($token) {
    echo "\n5. POST /articles CON token (debe funcionar con 201):\n";
    $result = makeRequest('POST', '/articles', [
        'nombre' => 'Mouse Gaming',
        'precio' => 59.99,
    ], $token);
    echo "Status: {$result['status']}\n";
    var_dump($result['data']);
}

// 5. Intentar PUT sin token
echo "\n6. PUT /articles/1 SIN token (debe fallar con 401):\n";
$result = makeRequest('PUT', '/articles/1', [
    'nombre' => 'Articulo Actualizado',
    'precio' => 150,
]);
echo "Status: {$result['status']}\n";
echo "Respuesta: ";
var_dump($result['data']);

// 6. Intentar DELETE sin token
echo "\n7. DELETE /articles/1 SIN token (debe fallar con 401):\n";
$result = makeRequest('DELETE', '/articles/1');
echo "Status: {$result['status']}\n";
echo "Respuesta: ";
var_dump($result['data']);

echo "\n=== Verificación Completada ===\n";
