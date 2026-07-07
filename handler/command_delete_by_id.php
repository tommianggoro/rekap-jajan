<?php
// handler/command_delid.php

// Regex untuk mengambil angka ID setelah command /delid
if (preg_match('/^\/delid\s+(\d+)/', $text, $matches)) {
    $expenseId = $matches[1];

    try {
        // Hapus spesifik berdasarkan ID dan pastikan dalam chat_id grup/sesi yang benar
        $stmt = $pdo->prepare("
            DELETE FROM `expenses` 
            WHERE `id` = ? 
            AND `session_id` IN (SELECT id FROM `sessions` WHERE `chat_id` = ?)
        ");
        $stmt->execute([$expenseId, $chatId]);

        if ($stmt->rowCount() > 0) {
            // Gunakan format Markdown yang aman (tanpa karakter underscore yang aneh)
            sendMessage($chatId, "🗑️ *Transaksi Berhasil Dihapus!*\n🆔 ID Transaksi: $expenseId\n\n_Data pengeluaran telah disesuaikan kembali._");
        } else {
            sendMessage($chatId, "ℹ️ Transaksi dengan ID $expenseId tidak ditemukan, sudah dihapus, atau bukan milik grup ini.");
        }

    } catch (Exception $e) {
        error_log("DELID ERROR: " . $e->getMessage());
        sendMessage($chatId, "❌ Gagal menghapus transaksi.");
    }
} else {
    // Jika user hanya mengetik /delid tanpa format angka
    sendMessage($chatId, "⚠️ Format salah. Gunakan perintah: `/delid [angka_id]`\nContoh: `/delid 79`");
}