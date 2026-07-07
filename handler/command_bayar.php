<?php
// handler/command_bayar.php

// Regex: /bayar [nominal] [keterangan] #[label] dan/atau @username
// Contoh: /bayar 50000 makan siang #kantor @budi
// if (preg_match('/\/bayar\s+(\d+)\s+(.+?)(?:\s+#(\w+))?(?:\s+@(\w+))?$/', $text, $matches)) {
// if (preg_match('/\/bayar\s+(\d+)\s+([^\s#@]+)(?:\s+#(\w+))?(?:\s+@([a-zA-Z0-9_]+))?$/', $text, $matches)) {
// 1. Tangkap nominal dan seluruh sisa teks di belakangnya terlebih dahulu
if (preg_match('/\/bayar\s+(\d+)\s+(.+)$/', $text, $matches)) {
    $amount        = $matches[1];
    $remainingText = trim($matches[2]);

    // Set nilai default awal
    $label   = 'umum';
    $mention = null;

    // 2. Ekstrak Label (#) jika ada di posisi mana pun di dalam teks
    if (preg_match('/#(\w+)/', $remainingText, $labelMatches)) {
        $label = $labelMatches[1];
        // Hapus tag #label dari teks sisa agar tidak mengotori deskripsi
        $remainingText = trim(preg_replace('/#\w+/', '', $remainingText));
    }

    // 3. Ekstrak Mention (@) jika ada di posisi mana pun di dalam teks
    if (preg_match('/@([a-zA-Z0-9_]+)/', $remainingText, $mentionMatches)) {
        $mention = $mentionMatches[1];
        // Hapus tag @mention dari teks sisa agar tidak mengotori deskripsi
        $remainingText = trim(preg_replace('/@[a-zA-Z0-9_]+/', '', $remainingText));
    }

    // 4. Sisa teks yang sudah bersih otomatis menjadi deskripsi murni
    $description = preg_replace('/\s+/', ' ', $remainingText); // Merapikan spasi ganda jika ada

    // Jika setelah dihapus tag-nya ternyata deskripsi jadi kosong (misal cuma ngetik /bayar 50000 #juli)
    if (empty($description)) {
        $description = "Pengeluaran tanpa keterangan";
    }

    $paidBy = null;
    $payerName = null;

    // --- LOGIC PENENTUAN PEMBAYAR ---
    
    // Skenario 1: Fitur Reply
    if (isset($message['reply_to_message'])) {
        $paidBy = $message['reply_to_message']['from']['id'];
        $payerName = $message['reply_to_message']['from']['first_name'];
        
        // Pastikan pembayar terdaftar (Auto-Join)
        $stmt = $pdo->prepare("INSERT IGNORE INTO `members` (`user_id`, `chat_id`, `first_name`) VALUES (?, ?, ?)");
        $stmt->execute([$paidBy, $chatId, $payerName]);

    // Skenario 2: Fitur Tagging @username (jika tidak sedang reply)
    } elseif ($mention) {
        $stmt = $pdo->prepare("SELECT `user_id`, `first_name` FROM `members` WHERE `username` = ? AND `chat_id` = ? LIMIT 1");
        $stmt->execute([$mention, $chatId]);
        $member = $stmt->fetch();

        if ($member) {
            $paidBy = $member['user_id'];
            $payerName = $member['first_name'];
        } else {
            // Gunakan fungsi escape agar karakter "_" di username aman jika di-print di Markdown Telegram
            $safeMention = str_replace('_', '\_', $mention);
            sendMessage($chatId, "⚠️ User @$safeMention belum terdaftar di sistem grup ini. Minta dia ketik /join dulu.");
            exit;
        }

    // Skenario 3: Bayar Sendiri
    } else {
        $paidBy = $userId;
        $payerName = $firstName;
    }

    // --- PROSES SIMPAN DATABASE ---
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

        // AMBIL ID TRANSAKSI YANG BARU TERBENTUK
        $expenseId = $pdo->lastInsertId();

        // Bersihkan nama & keterangan dari karakter khusus Markdown agar bot tidak crash saat sendMessage
        $safePayerName = str_replace('_', '\_', $payerName);
        $safeFirstName = str_replace('_', '\_', $firstName);
        $safeDescription = str_replace('_', '\_', $description);

        // Susun pesan respon dengan format Markdown yang aman
        $msg = "✅ *Tercatat!*\n";
        $msg .= "🆔 ID Pengeluaran: $expenseId\n";
        $msg .= "💰 Rp " . number_format($amount, 0, ',', '.') . "\n";
        $msg .= "👤 Pembayar: *$safePayerName*\n";
        $msg .= "✍️ Dicatat oleh: $safeFirstName\n";
        $msg .= "🏷 Sesi: #$label\n";
        $msg .= "📝 Ket: $safeDescription";
        
        sendMessage($chatId, $msg);

    } catch (Exception $e) {
        error_log("DB Error: " . $e->getMessage());
        sendMessage($chatId, "❌ Error Database: " . $e->getMessage());
    }

} else {
    // Jika format salah/tidak sesuai pola awal
    sendMessage($chatId, "⚠️ Format salah!\nContoh:\n- `/bayar 50000 bakso` (bayar sendiri)\n- `/bayar 50000 bakso @budi #juli` (catatin budi)\n- Reply pesan orangnya + `/bayar 50000 bakso`");
}







/*
if (preg_match('/\/bayar\s+(\d+)\s+(.+?)(?:\s+#(\w+))?(?:\s+@([a-zA-Z0-9_]+))?$/', $text, $matches)) {
    $amount      = $matches[1];
    $description = trim($matches[2]);
    // $label       = $matches[3] ?? 'umum';
    // PERBAIKAN UTAMA: Jika kosong atau null, paksa jadi 'umum'
    $label       = (!isset($matches[3]) || $matches[3] === '') ? 'umum' : $matches[3];
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

        // AMBIL ID TRANSAKSI YANG BARU TERBENTUK
        $expenseId = $pdo->lastInsertId();

        $msg = "✅ *Tercatat!*\n";
        $msg .= "🆔 ID Pengeluaran: $expenseId\n";
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
*/