<?php
/**
 * Script de test rápido para verificar la API
 * Ejecutar desde terminal: php api/test.php
 */

echo "=== StatTracker API Test ===\n\n";

// Configuración base
$baseUrl = 'http://localhost:8000';
$email = 'test@example.com';
$password = 'Password123';

echo "1. Probando LOGIN...\n";
$loginData = json_encode(['email' => $email, 'password' => $password]);
$ch = curl_init("$baseUrl/api/auth/login");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $loginData);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$loginResponse = curl_exec($ch);
curl_close($ch);

$loginResult = json_decode($loginResponse, true);
if (isset($loginResult['token'])) {
    $token = $loginResult['token'];
    echo "✅ Login exitoso!\n";
    echo "   Token: " . substr($token, 0, 50) . "...\n\n";
} else {
    echo "❌ Login fallido: " . ($loginResult['error'] ?? 'Error desconocido') . "\n\n";
    exit(1);
}

echo "2. Probando GET /api/profile...\n";
$ch = curl_init("$baseUrl/api/profile");
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token"]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$profileResponse = curl_exec($ch);
curl_close($ch);
$profileResult = json_decode($profileResponse, true);
if (isset($profileResult['success'])) {
    echo "✅ Perfil obtenido!\n";
    echo "   Usuario: {$profileResult['profile']['nombre']} {$profileResult['profile']['apellidos']}\n\n";
} else {
    echo "❌ Error: " . ($profileResult['error'] ?? 'Error desconocido') . "\n\n";
}

echo "3. Probando GET /api/metrics...\n";
$ch = curl_init("$baseUrl/api/metrics");
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token"]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$metricsResponse = curl_exec($ch);
curl_close($ch);
$metricsResult = json_decode($metricsResponse, true);
if (isset($metricsResult['success'])) {
    echo "✅ Métricas obtenidas!\n";
    echo "   Total: {$metricsResult['count']} registros\n\n";
} else {
    echo "❌ Error: " . ($metricsResult['error'] ?? 'Error desconocido') . "\n\n";
}

echo "4. Probando POST /api/metrics (crear nueva)...\n";
$newMetric = json_encode(['peso' => 75.5, 'altura' => 1.75, 'fecha_registro' => date('Y-m-d')]);
$ch = curl_init("$baseUrl/api/metrics");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $newMetric);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer $token"
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$createResponse = curl_exec($ch);
curl_close($ch);
$createResult = json_decode($createResponse, true);
if (isset($createResult['success'])) {
    echo "✅ Métrica creada!\n";
    echo "   ID: {$createResult['metric']['id']}, IMC: {$createResult['metric']['imc']}\n\n";
} else {
    echo "❌ Error: " . ($createResult['error'] ?? 'Error desconocido') . "\n\n";
}

echo "=== Tests completados ===\n";
