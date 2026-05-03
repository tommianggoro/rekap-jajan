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
    // Logika join singkat
    $stmt = $pdo->prepare("INSERT IGNORE INTO groups (chat_id, group_name) VALUES (?, ?)");
    $stmt->execute([$chatId, $message['chat']['title'] ?? 'Grup Rekap']);

    $stmt = $pdo->prepare("INSERT IGNORE INTO members (user_id, chat_id, first_name) VALUES (?, ?, ?)");
    $stmt->execute([$userId, $chatId, $firstName]);

    sendMessage($chatId, "🤝 *$firstName* sudah masuk dalam daftar patungan grup ini.");

} elseif (strpos($text, '/bayar') === 0) {
    require_once 'handler/command_bayar.php';

} elseif (strpos($text, '/rekap') === 0) {
    require_once 'handler/command_rekap.php';
}