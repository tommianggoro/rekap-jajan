<?php
// config/database.php

// Pastikan mengambil variable dari Railway
$host = getenv('MYSQLHOST');
$db   = getenv('MYSQLDATABASE');
$user = getenv('MYSQLUSER');
$pass = getenv('MYSQLPASSWORD');
$port = getenv('MYSQLPORT') ?: '3306'; // Default ke 3306 jika kosong

// Jika $host kosong, PHP akan mencoba mencari socket lokal dan menyebabkan error tersebut
if (!$host) {
    error_log("DATABASE ERROR: Variable MYSQLHOST tidak ditemukan di Environment Variables.");
    exit("Gagal koneksi: Konfigurasi host kosong.");
}

try {
    // Menambahkan 'host=' secara eksplisit memaksa PDO menggunakan TCP/IP, bukan socket
    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
    
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        // Tambahan opsi untuk memastikan stabilitas koneksi remote
        PDO::ATTR_TIMEOUT => 5, 
    ]);
} catch (PDOException $e) {
    error_log("DATABASE ERROR: " . $e->getMessage());
    exit("Gagal koneksi database: " . $e->getMessage()); 
}