<?php
// handler/command_bayar.php

// Regex: /bayar [nominal] [keterangan] #[label] dan/atau @username
// Contoh: /bayar 50000 makan siang #kantor @budi
if (preg_match('/\/bayar\s+(\d+)\s+(.+?)(?:\s+#(\w+))?(?:\s+@(\w+))?$/', $text, $matches)) {
    $amount      = $matches[1];
    $description = trim($matches[2]);
    $label       = $matches[3] ?? 'umum';
    $mention     = $matches[4] ?? null;

    $paidBy = null;
    $payerName = null;

    // 1. PRIORITAS: Fitur Reply
    if (isset($message['reply_to_message'])) {
        $paidBy = $message['reply_to_message']['from']['id'];
        $payerName = $message['reply_to_message']['from']['first_name'];
        
        // Pastikan pembayar terdaftar (Auto-Join)
        $stmt = $pdo->prepare("INSERT IGNORE INTO `members` (`user_id`, `chat_id`, `first_name`) VALUES (?, ?, ?)");
        $stmt->execute([$paidBy, $chatId, $payerName]);

    // 2. KEDUA: Fitur Tagging @username (jika tidak sedang reply)
    } elseif ($mention) {
        $stmt = $pdo->prepare("SELECT `user_id`, `first_name` FROM `members` WHERE `username` = ? AND `chat_id` = ? LIMIT 1");
        $stmt->execute([$mention, $chatId]);
        $member = $stmt->fetch();

        if ($member) {
            $paidBy = $member['user_id'];
            $payerName = $member['first_name'];
        } else {
            sendMessage($chatId, "⚠️ User @$mention belum terdaftar di sistem grup ini. Minta dia ketik /join dulu.");
            exit;
        }

    // 3. TERAKHIR: Bayar Sendiri
    } else {
        $paidBy = $userId;
        $payerName = $firstName;
    }

    try {
        // Simpan/Dapatkan Session
        $stmt = $pdo->prepare("INSERT IGNORE INTO `sessions` (`chat_id`, `label`, `status`) VALUES (?, ?, 'Active')");
        $stmt->execute([$chatId, $label]);

        $stmt = $pdo->prepare("SELECT `id` FROM `sessions` WHERE `chat_id` = ? AND `label` = ? AND `status` = 'Active'");
        $stmt->execute([$chatId, $label]);
        $sessionId = $stmt->fetchColumn();

        // Simpan ke tabel expenses
        $stmt = $pdo->prepare("INSERT INTO `expenses` (`session_id`, `paid_by`, `recorded_by`, `amount`, `description`) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$sessionId, $paidBy, $userId, $amount, $description]);

        $msg = "✅ *Tercatat!*\n";
        $msg .= "💰 Rp " . number_format($amount, 0, ',', '.') . "\n";
        $msg .= "👤 Pembayar: *$payerName*\n";
        $msg .= "✍️ Dicatat oleh: $firstName\n";
        $msg .= "🏷 Sesi: #$label\n";
        $msg .= "📝 Ket: $description";
        
        sendMessage($chatId, $msg);
    } catch (Exception $e) {
        error_log("DB Error: " . $e->getMessage());
        sendMessage($chatId, "❌ Error Database: " . $e->getMessage());
    }
} else {
    sendMessage($chatId, "⚠️ Format salah!\nContoh:\n- `/bayar 50000 bakso` (bayar sendiri)\n- `/bayar 50000 bakso @budi` (catatin budi)\n- Reply pesan orangnya + `/bayar 50000 bakso`");
}