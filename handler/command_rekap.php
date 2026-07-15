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