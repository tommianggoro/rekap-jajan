<?php
// handler/command_history.php
// Regex: /history #[label]
if (preg_match('/\/history(?:\s+#(\w+))?/', $text, $matches)) {
    // Jika #label tidak diisi, otomatis menggunakan 'umum'
    $label = $matches[1] ?? 'umum';
    try {
        // Ambil riwayat pengeluaran dari semua sesi dengan label tersebut
        $stmt = $pdo->prepare("
            SELECT
            e.id,
            e.amount,
            e.description,
            e.created_at,
            m.first_name,
            s.status
            FROM `expenses` e
            JOIN `members` m ON e.paid_by = m.user_id
            JOIN `sessions` s ON e.session_id = s.id
            WHERE s.label = ? AND m.chat_id = ? AND s.status = 'Active'
            ORDER BY e.created_at DESC
        ");
        $stmt->execute([$label, $chatId]);
        $history = $stmt->fetchAll();
        if (!$history) {
            sendMessage($chatId, "i️ Belum ada riwayat transaksi untuk label #$label.");
            exit;
        }
        $totalHistory = 0;
        $listText = "";
        foreach ($history as $row) {
            $totalHistory += $row['amount'];
            $date = date('d/m/y', strtotime($row['created_at']));
            $statusIcon = ($row['status'] == 'Active') ? "🟢" : "⚪";
            $listText .= "ID Pengeluaran: " . $row['id'] . " $statusIcon [$date] " . $row['first_name'] . ": Rp " .
            number_format($row['amount'], 0, ',', '.') . " (" . $row['description'] . ")\n";
        }
        $msg = "📜 *RIWAYAT TRANSAKSI #$label*\n";
        $msg .= "--------------------------------\n";
        $msg .= $listText;
        $msg .= "--------------------------------\n";
        $msg .= "💰 *Total Akumulasi:* Rp " . number_format($totalHistory, 0, ',', '.') . "\n";
        $msg .= "\n_Keterangan:_\n🟢 = Sesi Aktif\n⚪ = Sesi Selesai";
        sendMessage($chatId, $msg);
    } catch (Exception $e) {
        error_log("HISTORY ERROR: " . $e->getMessage());
        sendMessage($chatId, "❌ Gagal memuat riwayat transaksi.");
    }
} else {
    sendMessage($chatId, "⚠️ Gunakan format: `/history #label`\nContoh: `/history #mei` atau
    `/history #kantor`.");
}
?>