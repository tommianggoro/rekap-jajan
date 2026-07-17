<?php
// handler/command_pinjam.php

// Regex: /pinjam [nominal] [keterangan] #[label] dan wajib mention @username atau reply orangnya
// Contoh: /pinjam 200000 buat beli bensin @rara #umum

// 1. Tangkap nominal dan seluruh sisa teks di belakangnya terlebih dahulu
if (preg_match('/\/pinjam\s+(\d+)\s+(.+)$/', $text, $matches)) {
    $amount        = $matches[1];
    $remainingText = trim($matches[2]);

    // Set nilai default awal
    $label   = 'umum';
    $mention = null;

    // 2. Ekstrak Label (#) jika ada di posisi mana pun di dalam teks
    if (preg_match('/#(\w+)/', $remainingText, $labelMatches)) {
        $label = $labelMatches[1];
        $remainingText = trim(preg_replace('/#\w+/', '', $remainingText));
    }

    // 3. Ekstrak Mention (@) jika ada di posisi mana pun di dalam teks
    if (preg_match('/@([a-zA-Z0-9_]+)/', $remainingText, $mentionMatches)) {
        $mention = $mentionMatches[1];
        $remainingText = trim(preg_replace('/@[a-zA-Z0-9_]+/', '', $remainingText));
    }

    // 4. Sisa teks yang sudah bersih menjadi deskripsi murni
    $description = preg_replace('/\s+/', ' ', $remainingText);

    if (empty($description)) {
        $description = "Pinjaman tanpa keterangan";
    }
    
    // Tambahkan prefix agar di dashboard terlihat jelas kalau ini transaksi pinjaman
    $description = "[Pinjaman] " . $description;

    $borrowerId = null;   // Si Peminjam (akan masuk ke recorded_by sebagai beban)
    $borrowerName = null; // Nama peminjam untuk notifikasi teks

    // --- LOGIC PENENTUAN SI PEMINJAM (TARGET) ---
    
    // Skenario 1: Fitur Reply (Tommy mereply pesan Rara lalu ketik /pinjam 200000)
    if (isset($message['reply_to_message'])) {
        $borrowerId = $message['reply_to_message']['from']['id'];
        $borrowerName = $message['reply_to_message']['from']['first_name'];
        
        // Pastikan peminjam terdaftar (Auto-Join)
        $stmt = $pdo->prepare("INSERT IGNORE INTO `members` (`user_id`, `chat_id`, `first_name`) VALUES (?, ?, ?)");
        $stmt->execute([$borrowerId, $chatId, $borrowerName]);

    // Skenario 2: Fitur Tagging @username (Contoh: /pinjam 200000 @rara)
    } elseif ($mention) {
        $stmt = $pdo->prepare("SELECT `user_id`, `first_name` FROM `members` WHERE `username` = ? AND `chat_id` = ? LIMIT 1");
        $stmt->execute([$mention, $chatId]);
        $member = $stmt->fetch();

        if ($member) {
            $borrowerId = $member['user_id'];
            $borrowerName = $member['first_name'];
        } else {
            $safeMention = str_replace('_', '\_', $mention);
            sendMessage($chatId, "⚠️ User @$safeMention belum terdaftar di grup ini. Minta dia ketik /join dulu.");
            exit;
        }

    // Skenario 3: Jika tidak reply dan tidak ada mention, pinjam ke siapa? Error.
    } else {
        sendMessage($chatId, "⚠️ Kamu meminjamkan ke siapa? Wajib tag orangnya (contoh: `@username`) atau balas/reply pesannya!");
        exit;
    }

    // Siapa yang meminjamkan uang? Yaitu si pengetik perintah saat ini (Tommy)
    $paidBy = $userId; 
    $payerName = $firstName;

    // --- PROSES SIMPAN DATABASE ---
    try {
        // Simpan/Dapatkan Session
        $stmt = $pdo->prepare("INSERT IGNORE INTO `sessions` (`chat_id`, `label`, `status`) VALUES (?, ?, 'Active')");
        $stmt->execute([$chatId, $label]);

        $stmt = $pdo->prepare("SELECT `id` FROM `sessions` WHERE `chat_id` = ? AND `label` = ? AND `status` = 'Active'");
        $stmt->execute([$chatId, $label]);
        $sessionId = $stmt->fetchColumn();

        // TRICK UTAMA: 
        // paid_by = Tommy (Uang keluar dari Tommy)
        // recorded_by = borrowerId (Beban 100% ditimpakan ke Rara)
        $stmt = $pdo->prepare("INSERT INTO `expenses` (`session_id`, `paid_by`, `recorded_by`, `amount`, `description`) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$sessionId, $paidBy, $borrowerId, $amount, $description]);

        $expenseId = $pdo->lastInsertId();

        // Bersihkan teks untuk keamanan Markdown
        $safePayerName = str_replace('_', '\_', $payerName);
        $safeBorrowerName = str_replace('_', '\_', $borrowerName);
        $safeDescription = str_replace('_', '\_', $description);

        // Susun pesan respon bot
        $msg = "📌 *Pinjaman Tercatat!*\n";
        $msg .= "🆔 ID Transaksi: $expenseId\n";
        $msg .= "💰 Rp " . number_format($amount, 0, ',', '.') . "\n";
        $msg .= "👤 Pemberi Pinjaman: *$safePayerName*\n";
        $msg .= "👤 Penerima Pinjaman: *$safeBorrowerName*\n";
        $msg .= "🏷 Sesi: #$label\n";
        $msg .= "📝 Ket: $safeDescription";
        
        sendMessage($chatId, $msg);

    } catch (Exception $e) {
        error_log("DB Error: " . $e->getMessage());
        sendMessage($chatId, "❌ Error Database: " . $e->getMessage());
    }

} else {
    // Jika format salah
    sendMessage($chatId, "⚠️ Format salah!\nContoh:\n- `/pinjam 200000 beli bensin @rara`\n- Reply pesan Rara + `/pinjam 200000 beli bensin`");
}