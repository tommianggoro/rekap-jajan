<?php
// 1. Ambil origin dari request header
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

// 2. Tentukan domain apa saja yang diizinkan
$allowed_origins = [
    'http://localhost',
    'https://rekap-jajan.unaux.com/' 
];

// 3. Jika origin cocok, berikan izin secara dinamis
if (in_array($origin, $allowed_origins)) {
    header("Access-Control-Allow-Origin: " . $origin);
} elseif (empty($origin)) {
    // Jalur alternatif jika request dikirim tanpa header Origin (misal dari Postman/Curl)
    header("Access-Control-Allow-Origin: *");
}

// 4. Header pendukung wajib CORS
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// 5. Potong langsung di sini jika method-nya OPTIONS (Preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}