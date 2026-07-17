<?php
// handler/command_utang.php

// KONDISI: Rara mencatat kalau dia berutang ke Tommy
// Format: /utang 200000 buat beli bensin @tommy #umum

if (preg_match('/\/utang\s+(\d+)\s+(.+)$/', $text, $matches)) {
    $amount        = $matches[1];
    $remainingText = trim($matches[2]);

    $label   = 'umum';
    $mention = null;

    if (preg_match('/#(\w+)/', $remainingText, $labelMatches)) {
        $label = $labelMatches[1];
        $remainingText = trim(preg_replace('/#\w+/', '', $remainingText));
    }

    if (preg_match('/@([a-zA-Z0-9_]+)/', $remainingText, $mentionMatches)) {
        $mention = $mentionMatches[1];
        $remainingText = trim(preg_replace('/@[a-zA-Z0-9_]+/', '', $remainingText));
    }

    $description = preg_replace('/\s+/', ' ', $remainingText);
    if (empty($description)) {
        $description = "Pinjaman tanpa keterangan";
    }
    
    // Beri label pembeda di deskripsi
    $description = "[Utang] " . $description;

    $lenderId = null;   // Si Pemberi Pinjaman (yang keluar uang -> paid_by)
    $lenderName = null; // Nama pemberi pinjaman untuk notifikasi

    // --- LOGIC PENENTUAN SI PEMBERI PINJAMAN (TARGET MENTION/REPLY) ---
    
    // Skenario 1: Fitur Reply (Rara mereply pesan Tommy lalu ketik /utang 200000)
    if (isset($message['reply_to_message'])) {
        $lenderId = $message['reply_to_message']['from']['id'];
        $lenderName = $message['reply_to_message']['from']['first_name'];
        
        $stmt = $pdo->prepare("INSERT IGNORE INTO `members` (`user_id`, `chat_id`, `first_name`) VALUES (?, ?, ?)");
        $stmt->execute([$lenderId, $chatId, $lenderName]);

    // Skenario 2: Fitur Tagging @username (Contoh: /utang 200000 @tommy)
    } elseif ($mention) {
        $stmt = $pdo->prepare("SELECT `user_id`, `first_name` FROM `members` WHERE `username` = ? AND `chat_id` = ? LIMIT 1");
        $stmt->execute([$mention, $chatId]);
        $member = $stmt->fetch();

        if ($member) {
            $lenderId = $member['user_id'];
            $lenderName = $member['first_name'];
        } else {
            $safeMention = str_replace('_', '\_', $mention);
            sendMessage($chatId, "⚠️ User @$safeMention belum terdaftar. Minta dia ketik /join dulu.");
            exit;
        }

    // Skenario 3: Jika tidak ada target
    } else {
        sendMessage($chatId, "⚠️ Kamu berutang ke siapa? Wajib tag orangnya (contoh: `@username`) atau reply pesannya!");
        exit;
    }

    // Siapa yang berutang/menanggung beban? Yaitu si pengetik perintah itu sendiri (Rara)
    $recordedBy = $userId; 
    $borrowerName = $firstName;

    // --- PROSES SIMPAN DATABASE ---
    try {
        $stmt = $pdo->prepare("INSERT IGNORE INTO `sessions` (`chat_id`, `label`, `status`) VALUES (?, ?, 'Active')");
        $stmt->execute([$chatId, $label]);

        $stmt = $pdo->prepare("SELECT `id` FROM `sessions` WHERE `chat_id` = ? AND `label` = ? AND `status` = 'Active'");
        $stmt->execute([$chatId, $label]);
        $sessionId = $stmt->fetchColumn();

        // LOGIKA DI-BALIK:
        // paid_by = lenderId (Uang keluar dari Tommy yang di-mention/reply)
        // recorded_by = recordedBy (Beban masuk ke Rara yang ngetik command)
        $stmt = $pdo->prepare("INSERT INTO `expenses` (`session_id`, `paid_by`, `recorded_by`, `amount`, `description`) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$sessionId, $lenderId, $recordedBy, $amount, $description]);

        $expenseId = $pdo->lastInsertId();

        $safeLenderName = str_replace('_', '\_', $lenderName);
        $safeBorrowerName = str_replace('_', '\_', $borrowerName);
        $safeDescription = str_replace('_', '\_', $description);

        $msg = "📌 *Utang Tercatat!*\n";
        $msg .= "🆔 ID Transaksi: $expenseId\n";
        $msg .= "💰 Rp " . number_format($amount, 0, ',', '.') . "\n";
        $msg .= "👤 Pemberi Uang: *$safeLenderName*\n";
        $msg .= "👤 Penerima Uang (Beban): *$safeBorrowerName*\n";
        $msg .= "🏷 Sesi: #$label\n";
        $msg .= "📝 Ket: $safeDescription";
        
        sendMessage($chatId, $msg);

    } catch (Exception $e) {
        error_log("DB Error: " . $e->getMessage());
        sendMessage($chatId, "❌ Error Database: " . $e->getMessage());
    }

} else {
    sendMessage($chatId, "⚠️ Format salah!\nContoh:\n- `/utang 200000 beli bensin @tommy`\n- Reply pesan Tommy + `/utang 200000 beli bensin`");
}