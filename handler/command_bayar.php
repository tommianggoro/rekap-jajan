<?php
// handler/command_bayar.php

// Regex: /bayar [nominal] [keterangan] #[label]
if (preg_match('/\/bayar\s+(\d+)\s+(.+?)(?:\s+#(\w+))?$/', $text, $matches)) {
    $amount = $matches[1];
    $description = $matches[2];
    $label = $matches[3] ?? 'umum';

    // Logika menentukan pembayar (paid_by)
    if (isset($message['reply_to_message'])) {
        $paidBy = $message['reply_to_message']['from']['id'];
        $payerName = $message['reply_to_message']['from']['first_name'];
    } else {
        $paidBy = $userId;
        $payerName = $firstName;
    }

    try {
        // 1. Dapatkan/Buat Session ID
        $stmt = $pdo->prepare("INSERT IGNORE INTO sessions (chat_id, label, status) VALUES (?, ?, 'Active')");
        $stmt->execute([$chatId, $label]);

        $stmt = $pdo->prepare("SELECT id FROM sessions WHERE chat_id = ? AND label = ? AND status = 'Active'");
        $stmt->execute([$chatId, $label]);
        $sessionId = $stmt->fetchColumn();

        // 2. Simpan Transaksi
        $stmt = $pdo->prepare("INSERT INTO expenses (session_id, paid_by, recorded_by, amount, description) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$sessionId, $paidBy, $userId, $amount, $description]);

        $msg = "✅ *Tercatat!*\n";
        $msg .= "💰 Rp " . number_format($amount, 0, ',', '.') . "\n";
        $msg .= "👤 Pembayar: $payerName\n";
        $msg .= "🏷 Sesi: #$label\n";
        $msg .= "📝 Ket: $description";
        
        sendMessage($chatId, $msg);
    } catch (Exception $e) {
        sendMessage($chatId, "❌ Error: " . $e->getMessage());
    }
} else {
    sendMessage($chatId, "⚠️ Format salah! Gunakan: `/bayar 50000 bakso #label` atau reply pesan orangnya.");
}