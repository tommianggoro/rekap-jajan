<?php
// handler/command_rekap.php

if (preg_match('/\/rekap(?:\s+#(\w+))?/', $text, $matches)) {
    $label = $matches[1] ?? 'umum';

    try {
        // 1. Cari Session ID aktif
        $stmt = $pdo->prepare("SELECT id FROM `sessions` WHERE `chat_id` = ? AND `label` = ? AND `status` = 'Active' LIMIT 1");
        $stmt->execute([$chatId, $label]);
        $sessionId = $stmt->fetchColumn();

        if (!$sessionId) {
            sendMessage($chatId, "ℹ️ Tidak ada sesi aktif untuk label #$label.");
            exit;
        }

        // 2. Ambil total yang DIBAYARKAN (pengeluaran) oleh masing-masing orang
        $stmt = $pdo->prepare("
            SELECT m.user_id, m.first_name, IFNULL(SUM(e.amount), 0) as total_spent
            FROM `members` m
            LEFT JOIN `expenses` e ON m.user_id = e.paid_by AND e.session_id = ?
            WHERE m.chat_id = ?
            GROUP BY m.user_id
        ");
        $stmt->execute([$sessionId, $chatId]);
        $spentSummary = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 3. Ambil total CICILAN yang sudah dilakukan (siapa bayar ke siapa)
        $stmt = $pdo->prepare("
            SELECT from_user_id, to_user_id, SUM(amount) as total_payment
            FROM `payments`
            WHERE session_id = ?
            GROUP BY from_user_id, to_user_id
        ");
        $stmt->execute([$sessionId]);
        $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 4. Hitung Total Grup & Bagi Rata
        $totalGrup = 0;
        foreach ($spentSummary as $row) {
            $totalGrup += $row['total_spent'];
        }
        $memberCount = count($spentSummary);
        $perOrang = ($memberCount > 0) ? ($totalGrup / $memberCount) : 0;

        // 5. Susun Pesan
        $msg = "📊 *REKAP PENGELUARAN #$label*\n";
        $msg .= "--------------------------------\n";
        foreach ($spentSummary as $row) {
            if ($row['total_spent'] > 0) {
                $msg .= "👤 " . $row['first_name'] . ": Rp " . number_format($row['total_spent'], 0, ',', '.') . "\n";
            }
        }
        $msg .= "--------------------------------\n";
        $msg .= "👥 *Total Anggota:* $memberCount\n";
        $msg .= "💰 *Total Sesi:* Rp " . number_format($totalGrup, 0, ',', '.') . "\n";
        $msg .= "👥 *Bagi Rata:* Rp " . number_format($perOrang, 0, ',', '.') . " / org\n\n";

        // 6. Logika Settlement Real-Time (Dikurangi Cicilan)
        $balances = [];
        foreach ($spentSummary as $row) {
            // Saldo awal: apa yang sudah dibayar - beban bagi rata
            $balances[$row['user_id']] = [
                'name' => $row['first_name'],
                'amount' => $row['total_spent'] - $perOrang
            ];
        }

        // Sesuaikan saldo dengan data cicilan dari tabel payments
        foreach ($payments as $p) {
            if (isset($balances[$p['from_user_id']])) {
                $balances[$p['from_user_id']]['amount'] += $p['total_payment'];
            }
            if (isset($balances[$p['to_user_id']])) {
                $balances[$p['to_user_id']]['amount'] -= $p['total_payment'];
            }
        }

        $debtors = [];
        $creditors = [];
        foreach ($balances as $id => $data) {
            if ($data['amount'] < -1) { // Hutang
                $debtors[$data['name']] = abs($data['amount']);
            } elseif ($data['amount'] > 1) { // Nombok
                $creditors[$data['name']] = $data['amount'];
            }
        }

        $settlementText = "💸 *Sisa Hutang Pelunasan:*\n";
        if (empty($debtors) && empty($creditors)) {
            $settlementText .= "Semua sudah lunas! ✅\n";
        } else {
            foreach ($debtors as $debtor => $amount) {
                foreach ($creditors as $creditor => $creditAmount) {
                    if ($amount <= 0) break;
                    if ($creditAmount <= 0) continue;

                    $transfer = min($amount, $creditAmount);
                    $settlementText .= "🔸 $debtor ➡️ $creditor: Rp " . number_format($transfer, 0, ',', '.') . "\n";
                    
                    $amount -= $transfer;
                    $creditors[$creditor] -= $transfer;
                }
            }
        }
        
        $msg .= $settlementText;
        sendMessage($chatId, $msg);

    } catch (Exception $e) {
        sendMessage($chatId, "❌ Error Rekap: " . $e->getMessage());
    }
}