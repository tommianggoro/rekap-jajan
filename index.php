<?php
// index.php

require_once 'config/database.php';
require_once 'functions/telegram.php';

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
    require_once 'handler/command_bayar.php';

} elseif (strpos($text, '/rekap') === 0) {
    require_once 'handler/command_rekap.php';
} elseif (strpos($text, '/selesai') === 0) {
    require_once 'handler/command_selesai.php';
} elseif (strpos($text, '/history') === 0) {
    require_once 'handler/command_history.php';
} elseif (strpos($text, '/edit') === 0) {
    require_once 'handler/command_edit.php';
} elseif (strpos($text, '/cicil') === 0) {
    require_once 'handler/command_cicil.php';
} elseif (strpos($text, '/hapus') === 0) {
    require_once 'handler/command_hapus.php';
} elseif (strpos($text, '/help') === 0 || strpos($text, '/start') === 0) {
    require_once 'handler/command_help.php';
} else {
    // Balas dengan pesan bantuan jika perintah tidak dikenali
    sendMessage($chatId, "❓ Perintah tidak dikenali. Ketik /help untuk daftar perintah yang tersedia.");
}