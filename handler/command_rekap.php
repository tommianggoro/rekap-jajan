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

        // 2. Ambil SEMUA member grup dan total yang mereka bayar di sesi ini
        // Kita gunakan LEFT JOIN agar member yang belum bayar (0) tetap muncul
        $stmt = $pdo->prepare("
            SELECT 
                m.first_name, 
                IFNULL(SUM(e.amount), 0) as total 
            FROM `members` m
            LEFT JOIN `expenses` e ON m.user_id = e.paid_by AND e.session_id = ?
            WHERE m.chat_id = ?
            GROUP BY m.user_id
        ");
        $stmt->execute([$sessionId, $chatId]);
        $summary = $stmt->fetchAll();

        // 3. Hitung Total dan Bagi Rata
        $totalGrup = 0;
        foreach ($summary as $row) {
            $totalGrup += $row['total'];
        }

        $memberCount = count($summary); // Total member yang terdaftar di grup
        if ($memberCount == 0) exit;
        
        $perOrang = $totalGrup / $memberCount;

        // 4. Susun Detail Pengeluaran (Hanya tampilkan yang bayar > 0 agar ringkas)
        $detailText = "";
        foreach ($summary as $row) {
            if ($row['total'] > 0) {
                $detailText .= "👤 " . $row['first_name'] . ": Rp " . number_format($row['total'], 0, ',', '.') . "\n";
            }
        }

        $msg = "📊 *REKAP PENGELUARAN #$label*\n";
        $msg .= "--------------------------------\n";
        $msg .= $detailText;
        $msg .= "--------------------------------\n";
        $msg .= "💰 *Total Sesi:* Rp " . number_format($totalGrup, 0, ',', '.') . "\n";
        $msg .= "👥 *Bagi Rata ($memberCount orang):*\n";
        $msg .= "👉 Rp " . number_format($perOrang, 0, ',', '.') . " / orang\n\n";
        $msg .= "💡 _Gunakan /selesai #$label untuk menutup sesi ini._";

        // 5. Logika Settlement (Sekarang pasti mendeteksi orang yang bayar 0)
        $debtors = [];
        $creditors = [];

        foreach ($summary as $row) {
            $balance = $row['total'] - $perOrang; // Jika total bayar 0, maka balance -40.450
            if ($balance < -0.01) { // Gunakan margin kecil untuk menghindari error float
                $debtors[$row['first_name']] = abs($balance);
            } elseif ($balance > 0.01) {
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