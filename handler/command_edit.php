<?php
// handler/command_edit.php

// 1. Pastikan ini adalah reply ke pesan bot
if (!isset($message['reply_to_message'])) {
    sendMessage($chatId, "⚠️ Balas (reply) pesan konfirmasi transaksi yang ingin diedit.");
    exit;
}

$oldText = $message['reply_to_message']['text'] ?? '';

// ==========================================
// CABANG A: JIKA YANG DI-REPLY ADALAH CICILAN/PEMBAYARAN
// ==========================================
if (preg_match('/🆔 ID Pembayaran:\s+(\d+)/', $oldText, $paymentMatches)) {
    $paymentId = $paymentMatches[1];

    // Tangkap nominal baru dari command: /edit [nominal] [keterangan/opsional]
    if (preg_match('/\/edit\s+(\d+)/', $text, $amtMatches)) {
        $newAmount = $amtMatches[1];

        try {
            $stmt = $pdo->prepare("
                UPDATE `payments` 
                SET `amount` = ? 
                WHERE `id` = ? 
                AND `session_id` IN (SELECT id FROM `sessions` WHERE `chat_id` = ?)
            ");
            $stmt->execute([$newAmount, $paymentId, $chatId]);

            if ($stmt->rowCount() > 0) {
                sendMessage($chatId, "✅ **Cicilan Berhasil Diperbarui!**\n🆔 ID Pembayaran: $paymentId\n💰 Menjadi: Rp " . number_format($newAmount, 0, ',', '.'));
            } else {
                sendMessage($chatId, "ℹ️ Data pembayaran tidak ditemukan atau tidak ada perubahan.");
            }
        } catch (Exception $e) {
            error_log("EDIT PAYMENT ERROR: " . $e->getMessage());
            sendMessage($chatId, "❌ Gagal mengedit cicilan: " . $e->getMessage());
        }
    } else {
        sendMessage($chatId, "⚠️ Format salah. Untuk mengedit cicilan cukup ketik: `/edit [nominal_baru]`");
    }
    exit;
}

// ==========================================
// CABANG B: JIKA YANG DI-REPLY ADALAH EXPENSES (PENGELUARAN/PINJAMAN/UTANG)
// ==========================================
$expenseId = null;
if (preg_match('/🆔 ID (?:Pengeluaran|Transaksi):\s+(\d+)/', $oldText, $idMatches)) {
    $expenseId = $idMatches[1];
} else {
    sendMessage($chatId, "⚠️ Gagal mengenali ID Transaksi atau Pembayaran dari pesan yang kamu balas.");
    exit;
}

// Tangkap nominal baru dan sisa teks (menggunakan teknik regex bertahap seperti /bayar)
if (preg_match('/\/edit\s+(\d+)\s+(.+)$/', $text, $matches)) {
    $newAmount     = $matches[1];
    $remainingText = trim($matches[2]);

    $newLabel   = null;
    $newMention = null;

    // Ekstrak Label (#) jika ada
    if (preg_match('/#(\w+)/', $remainingText, $labelMatches)) {
        $newLabel = $labelMatches[1];
        $remainingText = trim(preg_replace('/#\w+/', '', $remainingText));
    }

    // Ekstrak Mention (@) jika ada
    if (preg_match('/@([a-zA-Z0-9_]+)/', $remainingText, $mentionMatches)) {
        $newMention = $mentionMatches[1];
        $remainingText = trim(preg_replace('/@[a-zA-Z0-9_]+/', '', $remainingText));
    }

    // Sisa teks menjadi deskripsi baru
    $newDescription = preg_replace('/\s+/', ' ', $remainingText);
    if (empty($newDescription)) {
        $newDescription = "Pengeluaran tanpa keterangan";
    }

    try {
        // Cek dulu data transaksi lama di DB untuk tahu jenis transaksinya
        $stmt = $pdo->prepare("SELECT description, paid_by, recorded_by FROM `expenses` WHERE id = ? LIMIT 1");
        $stmt->execute([$expenseId]);
        $oldExpense = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$oldExpense) {
            sendMessage($chatId, "❌ Data transaksi lama tidak ditemukan di database.");
            exit;
        }

        // Cek apakah ini transaksi Pinjaman/Utang berdasarkan deskripsi lamanya (Tanpa karakter pin)
        $isLoan = (strpos($oldExpense['description'], '[Pinjaman]') !== false || strpos($oldExpense['description'], '[Utang]') !== false);

        // Jika ini transaksi pinjaman, selipkan kembali tag-nya agar format rekap tidak rusak
        if ($isLoan && strpos($newDescription, '[') === false) {
            // Ambil jenis prefix aslinya ([Pinjaman] atau [Utang])
            $prefix = strpos($oldExpense['description'], '[Utang]') !== false ? '[Utang] ' : '[Pinjaman] ';
            $newDescription = $prefix . $newDescription;
        }

        // Tentukan Payer / Target Baru berdasarkan mention (jika diinput)
        $targetUserId = null;
        if ($newMention) {
            $stmt = $pdo->prepare("SELECT user_id FROM `members` WHERE username = ? AND chat_id = ? LIMIT 1");
            $stmt->execute([$newMention, $chatId]);
            $targetUserId = $stmt->fetchColumn();

            if (!$targetUserId) {
                sendMessage($chatId, "⚠️ User @$newMention belum terdaftar di grup ini.");
                exit;
            }
        }

        // Cari/Buat Session ID Baru jika label diganti
        $sessionId = null;
        if ($newLabel) {
            $stmt = $pdo->prepare("INSERT IGNORE INTO `sessions` (`chat_id`, `label`, `status`) VALUES (?, ?, 'Active')");
            $stmt->execute([$chatId, $newLabel]);
            
            $stmt = $pdo->prepare("SELECT id FROM `sessions` WHERE chat_id = ? AND label = ? AND status = 'Active'");
            $stmt->execute([$chatId, $newLabel]);
            $sessionId = $stmt->fetchColumn();
        }

        // 4. PROSES UPDATE DENGAN DYNAMIC QUERY
        $sql = "UPDATE `expenses` SET amount = ?, description = ?";
        $params = [$newAmount, $newDescription];

        if ($sessionId) {
            $sql .= ", session_id = ?";
            $params[] = $sessionId;
        }

        // LOGIKA PENYESUAIAN USER (PAID_BY & RECORDED_BY)
        if ($targetUserId) {
            if ($isLoan) {
                // Jika transaksi PINJAMAN: yang mengetik edit diasumsikan mempertahankan posisinya,
                // dan @mention baru akan menggantikan target pasangannya. (Tanpa karakter emoji pin)
                if (strpos($oldExpense['description'], '[Pinjaman]') !== false) {
                    // Kasus /pinjam: paid_by tetap (si pemberi), recorded_by diganti ke target baru
                    $sql .= ", recorded_by = ?";
                    $params[] = $targetUserId;
                } else {
                    // Kasus /utang: recorded_by tetap (si penerima), paid_by diganti ke target baru
                    $sql .= ", paid_by = ?";
                    $params[] = $targetUserId;
                }
            } else {
                // Jika TRANSAKSI BIASA (/bayar): @mention menggantikan paid_by (pembayar)
                $sql .= ", paid_by = ?";
                $params[] = $targetUserId;
            }
        }

        $sql .= " WHERE id = ?";
        $params[] = $expenseId;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        // Bersihkan teks respons untuk keamanan Markdown
        $safeDesc = str_replace('_', '\_', $newDescription);

        $msg = "✅ **Transaksi Berhasil Diperbarui!**\n";
        $msg .= "🆔 ID: $expenseId\n";
        $msg .= "💰 Menjadi: Rp " . number_format($newAmount, 0, ',', '.') . "\n";
        $msg .= "📝 Ket: $safeDesc";
        
        sendMessage($chatId, $msg);

    } catch (Exception $e) {
        error_log("EDIT EXPENSE ERROR: " . $e->getMessage());
        sendMessage($chatId, "❌ Gagal mengedit transaksi: " . $e->getMessage());
    }
} else {
    sendMessage($chatId, "⚠️ Format salah!\nContoh: `/edit 75000 nasi goreng #esok @budi`\n_(Wajib reply pesan konfirmasinya)_");
}