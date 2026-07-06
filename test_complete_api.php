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
    curl_close($ch);

    return json_decode($response, true);
}

echo "=== API Test Suite ===\n\n";

// 1. Register user
echo "1. Registering user...\n";
$registerResponse = makeRequest('POST', '/auth/register', [
    'nombre' => 'Juan Pérez',
    'password' => '123456',
    'rol' => 'admin',
]);
print_r($registerResponse);

// 2. Login
echo "\n2. Logging in...\n";
$loginResponse = makeRequest('POST', '/auth/login', [
    'nombre' => 'Juan Pérez',
    'password' => '123456',
]);
print_r($loginResponse);
$token = $loginResponse['token'] ?? null;

// 3. Get users
echo "\n3. Getting all users...\n";
$usersResponse = makeRequest('GET', '/users', null, $token);
print_r($usersResponse);

// 4. Create article
echo "\n4. Creating article...\n";
$articleResponse = makeRequest('POST', '/articles', [
    'nombre' => 'Laptop',
    'precio' => 999.99,
], $token);
print_r($articleResponse);

// 5. Get articles
echo "\n5. Getting all articles...\n";
$articlesResponse = makeRequest('GET', '/articles', null, $token);
print_r($articlesResponse);

// 6. Get article by ID
if (!empty($articlesResponse[0]['id'])) {
    echo "\n6. Getting article by ID...\n";
    $articleResponse = makeRequest('GET', '/articles/' . $articlesResponse[0]['id'], null, $token);
    print_r($articleResponse);
}

// 7. Update article
if (!empty($articlesResponse[0]['id'])) {
    echo "\n7. Updating article...\n";
    $updateResponse = makeRequest('PUT', '/articles/' . $articlesResponse[0]['id'], [
        'nombre' => 'Desktop Computer',
        'precio' => 1299.99,
    ], $token);
    print_r($updateResponse);
}

// 8. Logout
echo "\n8. Logging out...\n";
$logoutResponse = makeRequest('POST', '/auth/logout', null, $token);
print_r($logoutResponse);

echo "\n=== Test Complete ===\n";
