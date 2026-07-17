<?php
// handler/command_hapus.php

// Pastikan ini adalah reply ke pesan bot
if (!isset($message['reply_to_message'])) {
    sendMessage($chatId, "⚠️ Balas (reply) pesan konfirmasi transaksi yang ingin dihapus.");
    exit;
}

try {
    $oldText = $message['reply_to_message']['text'] ?? '';
    
    // 1. STRATEGI UTAMA: Sesuaikan Regex dengan teks baru "🆔 ID Pengeluaran:"
    if (preg_match('/🆔 ID (?:Pengeluaran|Transaksi):\s+(\d+)/', $oldText, $idMatches)) {
        $expenseId = $idMatches[1];

        // Hapus spesifik berdasarkan ID (Sangat Akurat untuk data baru)
        $stmt = $pdo->prepare("
            DELETE FROM `expenses` 
            WHERE `id` = ? 
            AND `session_id` IN (SELECT id FROM `sessions` WHERE `chat_id` = ?)
        ");
        $stmt->execute([$expenseId, $chatId]);

        if ($stmt->rowCount() > 0) {
            sendMessage($chatId, "🗑️ **Transaksi Berhasil Dihapus!**\n🆔 ID Transaksi: $expenseId\n\n_Data pengeluaran telah disesuaikan kembali._");
        } else {
            sendMessage($chatId, "ℹ️ Transaksi dengan ID $expenseId tidak ditemukan atau sudah dihapus.");
        }

    // 2. STRATEGI FALLBACK: Jika tidak ada ID, pakai metode Deskripsi (Untuk data lama sebelum update)
    } else {
        preg_match('/Ket: (.+)/', $oldText, $oldDescMatches);
        $oldDesc = $oldDescMatches[1] ?? '';

        if (empty($oldDesc)) {
            sendMessage($chatId, "⚠️ Gagal mengidentifikasi transaksi yang akan dihapus.");
            exit;
        }

        // Jalankan perintah DELETE berdasarkan deskripsi dalam jangka 1 jam terakhir[cite: 3]
        $stmt = $pdo->prepare("
            DELETE FROM `expenses` 
            WHERE `description` = ? 
            AND `session_id` IN (SELECT id FROM `sessions` WHERE `chat_id` = ?)
            AND `created_at` > DATE_SUB(NOW(), INTERVAL 1 HOUR)
            LIMIT 1
        ");
        $stmt->execute([$oldDesc, $chatId]);

        if ($stmt->rowCount() > 0) {
            sendMessage($chatId, "🗑️ **Transaksi Lama Berhasil Dihapus!**\n📝 Ket: $oldDesc\n\n_Data pengeluaran telah disesuaikan kembali._");
        } else {
            sendMessage($chatId, "ℹ️ Transaksi lama tidak ditemukan atau sudah kedaluwarsa (lebih dari 1 jam)[cite: 3].");
        }
    }

} catch (Exception $e) {
    error_log("HAPUS ERROR: " . $e->getMessage());
    sendMessage($chatId, "❌ Gagal menghapus transaksi.");
}