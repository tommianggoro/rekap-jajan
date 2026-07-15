<?php
require_once __DIR__ . '/../services/ExpenseService.php';
require_once __DIR__ . '/../helpers/Formatter.php';

// handler/command_history.php
// Regex: /history #[label]
if (preg_match('/\/history(?:\s+#(\w+))?/', $text, $matches)) {
    // Jika #label tidak diisi, otomatis menggunakan 'umum'
    $label = $matches[1] ?? 'umum';
    try {
        // Ambil riwayat pengeluaran dari semua sesi dengan label tersebut
        $expenseService = new ExpenseService($pdo);

        $result = $expenseService->getHistory($label, $chatId);

        if (!$result['success']) {
            sendMessage($chatId, "ℹ️ " . $result['message']);
            exit;
        }

        $history = $result['data']['history'];

        $totalHistory = $result['data']['total'];

        $listText = "";

        foreach ($history as $row) {

            $date = Formatter::shortDate($row['created_at']);
            $statusIcon = ($row['status'] == 'Active') ? "🟢" : "⚪";

            $listText .= "$statusIcon ID Pengeluaran: " . $row['id'] . " \n  [$date] " .
                $row['first_name'] . ": " .
                Formatter::rupiah($row['amount']) .
                " (" . $row['description'] . ")\n";
        }

        $msg = "📜 *RIWAYAT TRANSAKSI #$label*\n";
        $msg .= "--------------------------------\n";
        $msg .= $listText;
        $msg .= "--------------------------------\n";
        $msg .= "💰 *Total Akumulasi:* " . Formatter::rupiah($totalHistory) . "\n";
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