<?php

$ch = curl_init('http://127.0.0.1:8000/api/auth/pin-login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['pin' => '112233']));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
]);
$res = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "1. PIN LOGIN HTTP $httpCode: $res\n\n";

$data = json_decode($res, true);
$token = $data['data']['token'] ?? null;

if ($token) {
    $ch2 = curl_init('http://127.0.0.1:8000/api/auth/me');
    curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch2, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: Bearer ' . $token,
    ]);
    $res2 = curl_exec($ch2);
    $httpCode2 = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
    curl_close($ch2);

    echo "2. GET /api/auth/me HTTP $httpCode2: $res2\n\n";

    $ch3 = curl_init('http://127.0.0.1:8000/api/pos/bootstrap');
    curl_setopt($ch3, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch3, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: Bearer ' . $token,
    ]);
    $res3 = curl_exec($ch3);
    $httpCode3 = curl_getinfo($ch3, CURLINFO_HTTP_CODE);
    curl_close($ch3);

    echo "3. GET /api/pos/bootstrap HTTP $httpCode3: $res3\n\n";
}
