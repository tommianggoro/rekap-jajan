<?php

require_once __DIR__ . '/../../bootstrap.php';

// Ambil origin dari request header
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

// Tentukan domain apa saja yang diizinkan (ganti domain-dashboard-anda.com dengan domain asli nanti)
$allowed_origins = [
    'http://localhost',
    'http://localhost:3000', // jika pakai React/Vue/Vite
    'https://domain-dashboard-anda.com' 
];

// Jika origin yang meminta ada di dalam daftar, berikan izin secara dinamis
if (in_array($origin, $allowed_origins)) {
    header("Access-Control-Allow-Origin: " . $origin);
}

// Header pendukung lainnya
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Handle Preflight Request (Sangat penting untuk fetch/axios)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

header('Content-Type: application/json');

echo json_encode([
    'success' => true,
    'message' => 'Dashboard API OK',
    'time' => date('Y-m-d H:i:s')
]);