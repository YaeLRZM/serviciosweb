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

echo "=== Verificación de Duración del Token (2 minutos) ===\n\n";

// 1. Login
echo "1. Login:\n";
$loginResult = makeRequest('POST', '/auth/login', [
    'nombre' => 'admin_user',
    'password' => 'admin123',
]);

$token = $loginResult['token'] ?? null;

if ($token) {
    echo "✓ Token obtenido\n\n";

    // Decodificar token
    $tokenParts = explode('.', $token);
    if (count($tokenParts) === 3) {
        $payload = json_decode(base64_decode(strtr($tokenParts[1], '-_', '+/')), true);

        $iat = $payload['iat'];
        $exp = $payload['exp'];
        $duracion_segundos = $exp - $iat;
        $duracion_minutos = $duracion_segundos / 60;

        echo "--- Detalles del Token ---\n";
        echo "Fecha emisión:  " . date('Y-m-d H:i:s', $iat) . "\n";
        echo "Fecha vencimiento: " . date('Y-m-d H:i:s', $exp) . "\n";
        echo "Duración: " . $duracion_minutos . " minutos (" . $duracion_segundos . " segundos)\n";
        echo "\n";

        if ($duracion_minutos == 2) {
            echo "✓ Token configurado correctamente a 2 minutos\n";
        } else {
            echo "✗ Token NO tiene 2 minutos de duración\n";
        }
    }
} else {
    echo "✗ Error al obtener token\n";
    var_dump($loginResult);
}
