<?php
require_once __DIR__ . '/../repositories/SessionRepository.php';
require_once __DIR__ . '/../repositories/ExpenseRepository.php';
require_once __DIR__ . '/../repositories/PaymentRepository.php';
require_once __DIR__ . '/../helpers/Formatter.php';
require_once __DIR__ . '/../services/ExpenseService.php';

// handler/command_rekap.php

if (preg_match('/\/rekap(?:\s+#(\w+))?/', $text, $matches)) {
    $label = $matches[1] ?? 'umum';

    try {
        // 1. Cari Session ID aktif
        $expenseService = new ExpenseService($pdo);

        $result = $expenseService->getRecapData($chatId, $label);

        if (!$result['success']) {
            sendMessage($chatId, "ℹ️ " . $result['message']);
            exit;
        }

        $data = $result['data'];

        $sessionId   = $data['sessionId'];
        $spentSummary = $data['spentSummary'];
        $payments     = $data['payments'];
        $settlements = $data['settlements'];

        $totalGrup   = $data['totalGroup'];
        $memberCount = $data['memberCount'];
        $perOrang    = $data['perPerson'];

        // 5. Susun Pesan (Ubah bagian tampilan list)
        $msg = "📊 *REKAP PENGELUARAN #$label*\n";
        $msg .= "--------------------------------\n";
        foreach ($spentSummary as $row) {
            $hasLoan = count(array_filter($row['loan_details'])) > 0;
            $hasDebt = count(array_filter($row['debt_details'])) > 0;

            if ($row['pure_spent'] > 0 || $hasLoan || $hasDebt) {
                $msg .= "👤 *" . $row['first_name'] . "*\n";
                
                // 1. Tampilkan belanja umum grup
                if ($row['pure_spent'] > 0) {
                    $msg .= "   ↳ 🛒 Belanja Grup: Rp " . number_format($row['pure_spent'], 0, ',', '.') . "\n";
                }
                
                // 2. Tampilkan detail meminjamkan ke siapa saja
                foreach ($row['loan_details'] as $targetName => $loanAmt) {
                    if ($loanAmt > 0) {
                        $msg .= "   ↳ 💸 Meminjamkan ke $targetName: Rp " . number_format($loanAmt, 0, ',', '.') . "\n";
                    }
                }
                
                // 3. Tampilkan detail berutang ke siapa saja
                foreach ($row['debt_details'] as $lenderName => $debtAmt) {
                    if ($debtAmt > 0) {
                        $msg .= "   ↳ 📥 Berutang ke $lenderName: Rp " . number_format($debtAmt, 0, ',', '.') . "\n";
                    }
                }
            }
        }
        $msg .= "--------------------------------\n";
        $msg .= "👥 *Total Anggota:* $memberCount\n";
        $msg .= "💰 *Total Sesi (Umum):* Rp " . number_format($totalGrup, 0, ',', '.') . "\n";
        $msg .= "👥 *Bagi Rata Jajan:* Rp " . number_format($perOrang, 0, ',', '.') . " / org\n\n";

        $settlementText = "💸 *Sisa Hutang Pelunasan:*\n";

        if (empty($settlements)) {
            $settlementText .= "Semua sudah lunas! ✅\n";
        } else {
            foreach ($settlements as $item) {
                $settlementText .= "🔸 {$item['from']} ➡️ {$item['to']}: ";
                $settlementText .= Formatter::rupiah($item['amount']);
                $settlementText .= "\n";
            }
        }
        
        $msg .= $settlementText;
        sendMessage($chatId, $msg);

    } catch (Exception $e) {
        sendMessage($chatId, "❌ Error Rekap: " . $e->getMessage());
    }
}