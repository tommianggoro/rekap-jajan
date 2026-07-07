<?php
// index.php

// --- START BACA FILE .ENV MANUAL ---
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Abaikan jika baris berupa komentar
        if (strpos(trim($line), '#') === 0) continue;
        
        // Pisahkan key dan value berdasarkan tanda sama dengan (=)
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        
        // Masukkan ke getenv(), $_ENV, dan $_SERVER agar bisa dibaca di mana saja
        putenv("{$name}={$value}");
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
    }
}
// --- END BACA FILE .ENV MANUAL ---

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../functions/telegram.php';
// Tambahkan ini untuk memastikan $pdo tidak null
if (!isset($pdo)) {
    error_log("Variabel PDO tidak ditemukan!");
    exit;
}

$content = file_get_contents("php://input");
$update = json_decode($content, true);

if (!isset($update['message'])) exit;

$message = $update['message'];
$chatId  = $message['chat']['id'];
$userId  = $message['from']['id'];
$firstName = $message['from']['first_name'];
$text    = $message['text'] ?? '';

// Routing Perintah
if (strpos($text, '/join') === 0) {
    // Simpan data grup
    $stmt = $pdo->prepare("INSERT IGNORE INTO `groups` (`chat_id`, `group_name`) VALUES (?, ?)");
    $stmt->execute([$chatId, $message['chat']['title'] ?? 'Grup Rekap']);

    // Simpan data member lengkap dengan username
    $username = $message['from']['username'] ?? null; // Ambil username dari Telegram
    $stmt = $pdo->prepare("INSERT INTO `members` (`user_id`, `chat_id`, `first_name`, `username`) 
                           VALUES (?, ?, ?, ?) 
                           ON DUPLICATE KEY UPDATE `username` = VALUES(`username`), `first_name` = VALUES(`first_name`)");
    $stmt->execute([$userId, $chatId, $firstName, $username]);

    sendMessage($chatId, "🤝 *$firstName* (@$username) berhasil terdaftar!");
} elseif (strpos($text, '/bayar') === 0) {
    require_once __DIR__ . '/../handler/command_bayar.php';

} elseif (strpos($text, '/rekap') === 0) {
    require_once __DIR__ . '/../handler/command_rekap.php';
} elseif (strpos($text, '/selesai') === 0) {
    require_once __DIR__ . '/../handler/command_selesai.php';
} elseif (strpos($text, '/history') === 0) {
    require_once __DIR__ . '/../handler/command_history.php';
} elseif (strpos($text, '/edit') === 0) {
    require_once __DIR__ . '/../handler/command_edit.php';
} elseif (strpos($text, '/cicil') === 0) {
    require_once __DIR__ . '/../handler/command_cicil.php';
} elseif (strpos($text, '/hapus') === 0) {
    require_once __DIR__ . '/../handler/command_hapus.php';
} elseif (strpos($text, '/help') === 0 || strpos($text, '/start') === 0) {
    require_once __DIR__ . '/../handler/command_help.php';
} elseif(strpos($text, '/delid') === 0){
    require_once __DIR__ . '/../handler/command_delete_by_id.php';
} else {
    // Balas dengan pesan bantuan jika perintah tidak dikenali
    // sendMessage($chatId, "❓ Perintah tidak dikenali. Ketik /help untuk daftar perintah yang tersedia.");
}