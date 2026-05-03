<?php
// handler/command_selesai.php

if (preg_match('/\/selesai(?:\s+#(\w+))?/', $text, $matches)) {
    $label = $matches[1] ?? 'umum';

    try {
        // Update status sesi menjadi 'Closed'
        $stmt = $pdo->prepare("UPDATE `sessions` SET `status` = 'Closed' WHERE `chat_id` = ? AND `label` = ? AND `status` = 'Active'");
        $stmt->execute([$chatId, $label]);

        if ($stmt->rowCount() > 0) {
            sendMessage($chatId, "✅ Sesi #$label telah ditutup dan dilunasi. Pengeluaran baru untuk #$label akan memulai sesi baru yang bersih.");
        } else {
            sendMessage($chatId, "ℹ️ Tidak ada sesi aktif dengan label #$label yang bisa ditutup.");
        }
    } catch (Exception $e) {
        error_log("SELESAI ERROR: " . $e->getMessage());
        sendMessage($chatId, "❌ Gagal menutup sesi.");
    }
}