<?php
// handler/command_cicil.php

// Regex: /cicil [nominal] @tujuan #label
if (preg_match('/\/cicil\s+(\d+)\s+@(\w+)(?:\s+#(\w+))?$/', $text, $matches)) {
    $amount = $matches[1];
    $targetUsername = $matches[2];
    $label = $matches[3] ?? 'umum';

    try {
        // 1. Cari ID Penerima
        $stmt = $pdo->prepare("SELECT user_id, first_name FROM `members` WHERE username = ? AND chat_id = ?");
        $stmt->execute([$targetUsername, $chatId]);
        $target = $stmt->fetch();

        // 2. Cari Sesi Aktif
        $stmt = $pdo->prepare("SELECT id FROM `sessions` WHERE chat_id = ? AND label = ? AND status = 'Active'");
        $stmt->execute([$chatId, $label]);
        $sessionId = $stmt->fetchColumn();

        if ($target && $sessionId) {
            $stmt = $pdo->prepare("INSERT INTO `payments` (session_id, from_user_id, to_user_id, amount) VALUES (?, ?, ?, ?)");
            $stmt->execute([$sessionId, $userId, $target['user_id'], $amount]);

            sendMessage($chatId, "✅ *Cicilan Tercatat!*\n👤 Dari: $firstName\n🤝 Ke: " . $target['first_name'] . "\n💰 Rp " . number_format($amount, 0, ',', '.'));
        }
    } catch (Exception $e) {
        sendMessage($chatId, "❌ Gagal mencatat cicilan.");
    }
}