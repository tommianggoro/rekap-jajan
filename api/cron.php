<?php
// Mengambil kredensial dari Environment Variables Vercel
$host = getenv('MYSQLHOST');
$db   = getenv('MYSQLDATABASE');
$user = getenv('MYSQLUSER');
$pass = getenv('MYSQLPASSWORD');
$port = getenv('MYSQLPORT') ?: '3306'; // Default ke 3306 jika kosong

try {
    // Mencoba membuka koneksi ke database Aiven
    $db = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Jalankan query super ringan 'SELECT 1' hanya untuk memicu aktivitas internet ke Aiven
    $stmt = $db->query("SELECT 1");
    $stmt->execute();
    
    // Output sukses untuk log Vercel
    error_log("Sukses: Database Aiven berhasil dipancing agar tetap bangun!");
    echo "OK";
} catch (PDOException $e) {
    // Jika gagal, log error akan tercatat di Vercel Logs
    header("HTTP/1.1 500 Internal Server Error");
    $msg = "Gagal memancing database: " . $e->getMessage();
    error_log($msg);
    echo $msg;
}