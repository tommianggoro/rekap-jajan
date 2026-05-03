<?php
// handler/command_rekap.php

// Regex: /rekap #[label]
if (preg_match('/\/rekap(?:\s+#(\w+))?/', $text, $matches)) {
    $label = $matches[1] ?? 'umum';

    try {
        // 1. Cari Session ID yang aktif berdasarkan label
        $stmt = $pdo->prepare("SELECT id FROM `sessions` WHERE `chat_id` = ? AND `label` = ? AND `status` = 'Active' LIMIT 1");
        $stmt->execute([$chatId, $label]);
        $sessionId = $stmt->fetchColumn();

        if (!$sessionId) {
            sendMessage($chatId, "ℹ️ Tidak ada sesi aktif untuk label #$label.");
            exit;
        }

        // 2. Ambil total pengeluaran per orang (Paid By)
        $stmt = $pdo->prepare("
            SELECT m.first_name, SUM(e.amount) as total 
            FROM `expenses` e
            JOIN `members` m ON e.paid_by = m.user_id AND e.session_id = (
                SELECT id FROM `sessions` WHERE chat_id = ? AND label = ? AND status = 'Active' LIMIT 1
            )
            WHERE m.chat_id = ?
            GROUP BY e.paid_by
        ");
        $stmt->execute([$chatId, $label, $chatId]);
        $summary = $stmt->fetchAll();

        // 3. Ambil daftar semua member yang ikut (untuk pembagi rata)
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM `members` WHERE `chat_id` = ?");
        $stmt->execute([$chatId]);
        $memberCount = $stmt->fetchColumn();

        if ($memberCount == 0) exit;

        $totalGrup = 0;
        $detailText = "";
        foreach ($summary as $row) {
            $totalGrup += $row['total'];
            $detailText .= "👤 " . $row['first_name'] . ": Rp " . number_format($row['total'], 0, ',', '.') . "\n";
        }

        $perOrang = $totalGrup / $memberCount;

        // 4. Susun Pesan Rekap
        $msg = "📊 *REKAP PENGELUARAN #$label*\n";
        $msg .= "--------------------------------\n";
        $msg .= $detailText;
        $msg .= "--------------------------------\n";
        $msg .= "💰 *Total Sesi:* Rp " . number_format($totalGrup, 0, ',', '.') . "\n";
        $msg .= "👥 *Bagi Rata ($memberCount orang):*\n";
        $msg .= "👉 Rp " . number_format($perOrang, 0, ',', '.') . " / orang\n\n";
        $msg .= "💡 _Gunakan /selesai #$label untuk menutup sesi ini._";

        $debtors = [];
        $creditors = [];

        foreach ($summary as $row) {
            $balance = $row['total'] - $perOrang;
            if ($balance < 0) {
                $debtors[$row['first_name']] = abs($balance);
            } elseif ($balance > 0) {
                $creditors[$row['first_name']] = $balance;
            }
        }

        $settlementText = "\n💸 *Rencana Pelunasan:*\n";
        if (empty($debtors) && empty($creditors)) {
            $settlementText .= "Semua sudah pas! Tidak ada hutang.\n";
        } else {
            foreach ($debtors as $debtor => $amount) {
                foreach ($creditors as $creditor => $creditAmount) {
                    if ($amount <= 0) break;
                    if ($creditAmount <= 0) continue;

                    $transfer = min($amount, $creditAmount);
                    $settlementText .= "🔸 $debtor transfer ke $creditor: Rp " . number_format($transfer, 0, ',', '.') . "\n";
                    
                    $amount -= $transfer;
                    $creditors[$creditor] -= $transfer;
                }
            }
        }

        $msg .= $settlementText; // Gabungkan ke pesan utama

        sendMessage($chatId, $msg);

    } catch (Exception $e) {
        error_log("REKAP ERROR: " . $e->getMessage());
        sendMessage($chatId, "❌ Gagal menarik data rekap.");
    }
}