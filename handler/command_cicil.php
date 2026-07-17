<?php
// handler/command_cicil.php

// Regex fleksibel: /cicil [nominal] [keterangan/opsional] @username #label
// Contoh: /cicil 50000 bayar utang bensin @rara #umum

if (preg_match('/\/cicil\s+(\d+)\s+(.+)$/', $text, $matches)) {
    $amount        = $matches[1];
    $remainingText = trim($matches[2]);

    $label   = 'umum';
    $mention = null;

    // 1. Ekstrak Label (#) jika ada di posisi mana pun
    if (preg_match('/#(\w+)/', $remainingText, $labelMatches)) {
        $label = $labelMatches[1];
        $remainingText = trim(preg_replace('/#\w+/', '', $remainingText));
    }

    // 2. Ekstrak Mention (@) jika ada di posisi mana pun
    if (preg_match('/@([a-zA-Z0-9_]+)/', $remainingText, $mentionMatches)) {
        $mention = $mentionMatches[1];
        $remainingText = trim(preg_replace('/@[a-zA-Z0-9_]+/', '', $remainingText));
    }

    // 3. Sisa teks menjadi keterangan cicilan
    $description = preg_replace('/\s+/', ' ', $remainingText);
    if (empty($description)) {
        $description = "Pelunasan / Cicilan";
    }

    // Pastikan ada target yang di-mention
    if (!$mention) {
        sendMessage($chatId, "⚠️ Kamu mau mencicil ke siapa? Wajib tag orangnya (contoh: `@username`)!");
        exit;
    }

    try {
        // 4. Cari ID Penerima Cicilan di DB[cite: 8]
        $stmt = $pdo->prepare("SELECT user_id, first_name FROM `members` WHERE username = ? AND chat_id = ? LIMIT 1");
        $stmt->execute([$mention, $chatId]);
        $target = $stmt->fetch();

        if (!$target) {
            $safeMention = str_replace('_', '\_', $mention);
            sendMessage($chatId, "⚠️ User @$safeMention belum terdaftar di grup ini.");
            exit;
        }

        // 5. Cari / Pastikan Sesi Aktif Ada[cite: 8]
        $stmt = $pdo->prepare("INSERT IGNORE INTO `sessions` (`chat_id`, `label`, `status`) VALUES (?, ?, 'Active')");
        $stmt->execute([$chatId, $label]);

        $stmt = $pdo->prepare("SELECT id FROM `sessions` WHERE chat_id = ? AND label = ? AND status = 'Active'");
        $stmt->execute([$chatId, $label]);
        $sessionId = $stmt->fetchColumn();

        // 6. PROSES SIMPAN DATABASE TO TABEL PAYMENTS[cite: 8]
        $stmt = $pdo->prepare("INSERT INTO `payments` (session_id, from_user_id, to_user_id, amount) VALUES (?, ?, ?, ?)");
        $stmt->execute([$sessionId, $userId, $target['user_id'], $amount]);
        
        // Ambil ID payment yang baru terbentuk
        $paymentId = $pdo->lastInsertId();

        // Bersihkan teks respons untuk keamanan Markdown[cite: 1]
        $safeFirstName = str_replace('_', '\_', $firstName);
        $safeTargetName = str_replace('_', '\_', $target['first_name']);
        $safeDesc = str_replace('_', '\_', $description);

        // Susun pesan konfirmasi bot
        $msg = "✅ *Cicilan / Pelunasan Tercatat!*\n";
        $msg .= "🆔 ID Pembayaran: $paymentId\n";
        $msg .= "💰 Rp " . number_format($amount, 0, ',', '.') . "\n";
        $msg .= "👤 Dari: *$safeFirstName*\n";
        $msg .= "🤝 Ke: *$safeTargetName*\n";
        $msg .= "🏷 Sesi: #$label\n";
        $msg .= "📝 Ket: $safeDesc";

        sendMessage($chatId, $msg);

    } catch (Exception $e) {
        error_log("CICIL ERROR: " . $e->getMessage());
        sendMessage($chatId, "❌ Gagal mencatat cicilan: " . $e->getMessage());
    }
} else {
    sendMessage($chatId, "⚠️ Format salah!\nContoh: `/cicil 50000 bayar bakso @rara #umum`");
}