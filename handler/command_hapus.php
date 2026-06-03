<?php
// handler/command_hapus.php

// Pastikan ini adalah reply ke pesan bot
if (!isset($message['reply_to_message'])) {
    sendMessage($chatId, "⚠️ Balas (reply) pesan konfirmasi transaksi yang ingin dihapus.");
    exit;
}

try {
    // Ambil info deskripsi transaksi lama dari teks reply bot
    $oldText = $message['reply_to_message']['text'];
    preg_match('/Ket: (.+)/', $oldText, $oldDescMatches);
    $oldDesc = $oldDescMatches[1] ?? '';

    if (empty($oldDesc)) {
        sendMessage($chatId, "⚠️ Gagal mengidentifikasi transaksi yang akan dihapus.");
        exit;
    }

    // Jalankan perintah DELETE berdasarkan deskripsi di chat tersebut dalam 1 jam terakhir (aman untuk testing)
    $stmt = $pdo->prepare("
        DELETE FROM `expenses` 
        WHERE `description` = ? 
        AND `session_id` IN (SELECT id FROM `sessions` WHERE `chat_id` = ?)
        AND `created_at` > DATE_SUB(NOW(), INTERVAL 1 HOUR)
        LIMIT 1
    ");
    $stmt->execute([$oldDesc, $chatId]);

    if ($stmt->rowCount() > 0) {
        sendMessage($chatId, "🗑️ **Transaksi Berhasil Dihapus!**\n📝 Ket: $oldDesc\n\n_Data pengeluaran telah disesuaikan kembali._");
    } else {
        sendMessage($chatId, "ℹ️ Transaksi tidak ditemukan atau sudah kedaluwarsa (lebih dari 1 jam).");
    }

} catch (Exception $e) {
    error_log("HAPUS ERROR: " . $e->getMessage());
    sendMessage($chatId, "❌ Gagal menghapus transaksi.");
}