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

echo "=== Test de Roles y Permisos ===\n\n";

// 1. Crear usuario ADMIN
echo "1. Crear usuario ADMIN:\n";
$adminRegister = makeRequest('POST', '/auth/register', [
    'nombre' => 'admin_user',
    'password' => 'admin123',
    'rol' => 'admin',
]);
echo "Status: {$adminRegister['status']}\n";
if ($adminRegister['status'] === 201) {
    echo "✓ Usuario ADMIN creado\n";
} else {
    echo "Respuesta: " . json_encode($adminRegister['data']) . "\n";
}

// 2. Crear usuario USER
echo "\n2. Crear usuario USER:\n";
$userRegister = makeRequest('POST', '/auth/register', [
    'nombre' => 'regular_user',
    'password' => 'user123',
    'rol' => 'user',
]);
echo "Status: {$userRegister['status']}\n";
if ($userRegister['status'] === 201) {
    echo "✓ Usuario USER creado\n";
} else {
    echo "Respuesta: " . json_encode($userRegister['data']) . "\n";
}

// 3. Login como ADMIN
echo "\n3. Login como ADMIN:\n";
$adminLogin = makeRequest('POST', '/auth/login', [
    'nombre' => 'admin_user',
    'password' => 'admin123',
]);
$adminToken = $adminLogin['data']['token'] ?? null;
echo "Status: {$adminLogin['status']}\n";
if ($adminToken) {
    echo "✓ Token ADMIN obtenido\n";
}

// 4. Login como USER
echo "\n4. Login como USER:\n";
$userLogin = makeRequest('POST', '/auth/login', [
    'nombre' => 'regular_user',
    'password' => 'user123',
]);
$userToken = $userLogin['data']['token'] ?? null;
echo "Status: {$userLogin['status']}\n";
if ($userToken) {
    echo "✓ Token USER obtenido\n";
}

// ========== PRUEBAS CON ADMIN ==========
echo "\n\n=== PRUEBAS CON USUARIO ADMIN ===\n";

// 5. ADMIN - GET articles (debe funcionar)
echo "\n5. ADMIN - GET /articles (debe funcionar 200):\n";
$result = makeRequest('GET', '/articles', null, $adminToken);
echo "Status: {$result['status']}\n";
if ($result['status'] === 200) {
    echo "✓ ADMIN puede ver artículos\n";
}

// 6. ADMIN - POST article (debe funcionar)
echo "\n6. ADMIN - POST /articles (debe funcionar 201):\n";
$result = makeRequest('POST', '/articles', [
    'nombre' => 'Producto Admin',
    'precio' => 199.99,
], $adminToken);
echo "Status: {$result['status']}\n";
if ($result['status'] === 201) {
    echo "✓ ADMIN puede crear artículos\n";
    $adminArticleId = $result['data']['article']['id'];
}

// 7. ADMIN - PUT article (debe funcionar)
if (isset($adminArticleId)) {
    echo "\n7. ADMIN - PUT /articles/{$adminArticleId} (debe funcionar 200):\n";
    $result = makeRequest('PUT', "/articles/{$adminArticleId}", [
        'nombre' => 'Producto Admin Actualizado',
        'precio' => 249.99,
    ], $adminToken);
    echo "Status: {$result['status']}\n";
    if ($result['status'] === 200) {
        echo "✓ ADMIN puede actualizar artículos\n";
    }
}

// 8. ADMIN - DELETE article (debe funcionar)
if (isset($adminArticleId)) {
    echo "\n8. ADMIN - DELETE /articles/{$adminArticleId} (debe funcionar 200):\n";
    $result = makeRequest('DELETE', "/articles/{$adminArticleId}", null, $adminToken);
    echo "Status: {$result['status']}\n";
    if ($result['status'] === 200) {
        echo "✓ ADMIN puede eliminar artículos\n";
    }
}

// ========== PRUEBAS CON USER ==========
echo "\n\n=== PRUEBAS CON USUARIO USER ===\n";

// 9. USER - GET articles (debe funcionar)
echo "\n9. USER - GET /articles (debe funcionar 200):\n";
$result = makeRequest('GET', '/articles', null, $userToken);
echo "Status: {$result['status']}\n";
if ($result['status'] === 200) {
    echo "✓ USER puede ver artículos\n";
}

// 10. USER - POST article (debe fallar 403)
echo "\n10. USER - POST /articles (debe fallar 403):\n";
$result = makeRequest('POST', '/articles', [
    'nombre' => 'Producto Usuario',
    'precio' => 99.99,
], $userToken);
echo "Status: {$result['status']}\n";
if ($result['status'] === 403) {
    echo "✓ USER NO puede crear artículos (acceso denegado)\n";
    echo "Respuesta: " . json_encode($result['data']) . "\n";
} else {
    echo "✗ Debería ser 403, pero fue " . $result['status'] . "\n";
}

// 11. USER - PUT article (debe fallar 403)
echo "\n11. USER - PUT /articles/1 (debe fallar 403):\n";
$result = makeRequest('PUT', '/articles/1', [
    'nombre' => 'Actualización Usuario',
    'precio' => 149.99,
], $userToken);
echo "Status: {$result['status']}\n";
if ($result['status'] === 403) {
    echo "✓ USER NO puede actualizar artículos (acceso denegado)\n";
} else {
    echo "✗ Debería ser 403, pero fue " . $result['status'] . "\n";
}

// 12. USER - DELETE article (debe fallar 403)
echo "\n12. USER - DELETE /articles/1 (debe fallar 403):\n";
$result = makeRequest('DELETE', '/articles/1', null, $userToken);
echo "Status: {$result['status']}\n";
if ($result['status'] === 403) {
    echo "✓ USER NO puede eliminar artículos (acceso denegado)\n";
} else {
    echo "✗ Debería ser 403, pero fue " . $result['status'] . "\n";
}

echo "\n\n=== RESUMEN DE PERMISOS ===\n";
echo "ADMIN:\n";
echo "  ✓ GET (ver) artículos\n";
echo "  ✓ POST (crear) artículos\n";
echo "  ✓ PUT (actualizar) artículos\n";
echo "  ✓ DELETE (eliminar) artículos\n\n";
echo "USER:\n";
echo "  ✓ GET (ver) artículos\n";
echo "  ✗ POST (crear) artículos - 403 Forbidden\n";
echo "  ✗ PUT (actualizar) artículos - 403 Forbidden\n";
echo "  ✗ DELETE (eliminar) artículos - 403 Forbidden\n";
