<?php
// handler/command_edit.php

// Pastikan ini adalah reply ke pesan bot
if (!isset($message['reply_to_message'])) {
    sendMessage($chatId, "⚠️ Balas (reply) pesan konfirmasi transaksi yang ingin diedit.");
    exit;
}

// Regex: /edit [nominal] [keterangan] #[label] @username
if (preg_match('/\/edit\s+(\d+)\s+(.+?)(?:\s+#(\w+))?(?:\s+@(\w+))?$/', $text, $matches)) {
    $newAmount      = $matches[1];
    $newDescription = trim($matches[2]);
    $newLabel       = $matches[3] ?? 'umum';
    $newMention     = $matches[4] ?? null;

    try {
        // 1. Ambil info transaksi lama dari reply (Logika: Cari di DB berdasarkan deskripsi/waktu di pesan reply)
        // Note: Idealnya simpan ID transaksi di pesan bot, tapi kita bisa cari berdasarkan konteks
        $oldText = $message['reply_to_message']['text'];
        preg_match('/Ket: (.+)/', $oldText, $oldDescMatches);
        $oldDesc = $oldDescMatches[1] ?? '';

        // 2. Tentukan Payer Baru (jika ada mention)
        $paidBy = null;
        if ($newMention) {
            $stmt = $pdo->prepare("SELECT user_id FROM `members` WHERE username = ? AND chat_id = ?");
            $stmt->execute([$newMention, $chatId]);
            $paidBy = $stmt->fetchColumn();
        }

        // 3. Cari Session Baru jika label berubah
        $sessionId = null;
        if ($newLabel) {
            $stmt = $pdo->prepare("INSERT IGNORE INTO `sessions` (`chat_id`, `label`, `status`) VALUES (?, ?, 'Active')");
            $stmt->execute([$chatId, $newLabel]);
            
            $stmt = $pdo->prepare("SELECT id FROM `sessions` WHERE chat_id = ? AND label = ? AND status = 'Active'");
            $stmt->execute([$chatId, $newLabel]);
            $sessionId = $stmt->fetchColumn();
        }

        // 4. Update Database
        $sql = "UPDATE `expenses` SET amount = ?, description = ?";
        $params = [$newAmount, $newDescription];

        if ($paidBy) {
            $sql .= ", paid_by = ?";
            $params[] = $paidBy;
        }
        if ($sessionId) {
            $sql .= ", session_id = ?";
            $params[] = $sessionId;
        }

        // Filter berdasarkan deskripsi lama (sebagai kunci sederhana)
        $sql .= " WHERE description = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR) LIMIT 1";
        $params[] = $oldDesc;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        sendMessage($chatId, "✅ **Berhasil Diperbarui!**\n💰 Menjadi: Rp " . number_format($newAmount, 0, ',', '.'));

    } catch (Exception $e) {
        sendMessage($chatId, "❌ Gagal edit: " . $e->getMessage());
    }
} else {
    sendMessage($chatId, "⚠️ Format salah. Gunakan: `/edit [nominal] [keterangan] #[label] @username` (semua opsional kecuali nominal dan keterangan).");
}