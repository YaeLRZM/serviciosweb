<?php

$ch = curl_init('http://localhost:8000/api/auth/register');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
]);

$data = json_encode([
    'nombre' => 'Juan Pérez',
    'password' => '123456',
    'rol' => 'admin',
]);

curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

$response = curl_exec($ch);
curl_close($ch);

echo $response;
