<?php

require_once __DIR__ . '/../repositories/ExpenseRepository.php';
require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../repositories/SessionRepository.php';
require_once __DIR__ . '/../repositories/PaymentRepository.php';

class ExpenseService
{
    private ExpenseRepository $expenseRepository;
    private SessionRepository $sessionRepository;
    private PaymentRepository $paymentRepository;

    public function __construct(PDO $pdo)
    {
        $this->expenseRepository = new ExpenseRepository($pdo);
        $this->sessionRepository = new SessionRepository($pdo);
        $this->paymentRepository = new PaymentRepository($pdo);
    }

    public function getHistory(string $label, string $chatId): array
    {
        $history = $this->expenseRepository->getHistory($label, $chatId);

        if (empty($history)) {
            return Response::error("Belum ada riwayat transaksi untuk label #{$label}.");
        }

        $total = 0;

        foreach ($history as $row) {
            $total += $row['amount'];
        }

        return Response::success(
            [
                'history' => $history,
                'total' => $total
            ],
            'Riwayat transaksi berhasil diambil.'
        );
    }

    public function getRecapDataOld(string $chatId, string $label): array
    {
        $sessionId = $this->sessionRepository->getActiveSessionId($chatId, $label);

        if (!$sessionId) {
            return Response::error("Tidak ada sesi aktif untuk label #{$label}.");
        }

        // 1. Tetap ambil ringkasan total bayar per user dari repo untuk keperluan display list atas
        $spentSummary = $this->expenseRepository->getSpentSummary($sessionId, $chatId);
        $payments = $this->paymentRepository->getPaymentsBySession($sessionId);

        // 2. AMBIL SEMUA TRANSAKSI MENTAH DI SESI INI
        // Pastikan method ini ada di ExpenseRepository kamu (atau jika belum, kamu bisa buat query sederhana)
        $rawExpenses = $this->expenseRepository->getHistoryBySessionId($sessionId);

        // 3. Inisialisasi struktur balance untuk memetakan nama dan kalkulasi nominal bersih
        $balances = [];
        foreach ($spentSummary as $row) {
            $balances[$row['user_id']] = [
                'name' => $row['first_name'],
                'paid' => 0,      // Total uang yang dikeluarkan orang tersebut
                'spending' => 0  // Total beban yang harus ditanggung orang tersebut
            ];
        }

        // Total sesi grup untuk pengeluaran biasa saja (di luar pinjaman)
        $totalGroup = 0; 
        $memberCount = count($spentSummary);

        // 4. ALGORITMA SPLIT BILL REAL-TIME
        foreach ($rawExpenses as $expense) {
            $amount = (float)$expense['amount'];
            $paidBy = $expense['paid_by'];
            $recordedBy = $expense['recorded_by'];
            $desc = $expense['description'];

            // Tambahkan ke total uang yang dikeluarkan si pembayar
            if (isset($balances[$paidBy])) {
                $balances[$paidBy]['paid'] += $amount;
            }

            // CEK APAKAH INI TRANSAKSI PINJAMAN / UTANG PRIBADI
            if (strpos($desc, '[Pinjaman]') !== false || strpos($desc, '[Utang]') !== false) {
                // Beban penuh 100% langsung ditimpakan ke target (recorded_by) tanpa dibagi rata!
                if (isset($balances[$recordedBy])) {
                    $balances[$recordedBy]['spending'] += $amount;
                }
            } else {
                // JIKA TRANSAKSI JAJAN BIASA:
                $totalGroup += $amount; // Masuk ke hitungan total sesi grup
                
                // Bagi rata beban ke semua member aktif di sesi ini
                if ($memberCount > 0) {
                    $share = $amount / $memberCount;
                    foreach ($balances as $userId => &$b) {
                        $b['spending'] += $share;
                    }
                    unset($b);
                }
            }
        }

        // Tentukan nilai bagi rata pengeluaran jajan umum per orang
        $perOrang = $memberCount > 0 ? $totalGroup / $memberCount : 0;

        // 5. HITUNG HITUNGAN BERSIH AKHIR (Paid - Spending)
        foreach ($balances as $userId => &$b) {
            $b['amount'] = $b['paid'] - $b['spending'];
        }
        unset($b);

        // 6. INTEGRASIKAN DENGAN PEMBAYARAN MANUAL / TRANSFER CICILAN (JIKA ADA)
        foreach ($payments as $payment) {
            if (isset($balances[$payment['from_user_id']])) {
                $balances[$payment['from_user_id']]['amount'] += $payment['total_payment'];
            }
            if (isset($balances[$payment['to_user_id']])) {
                $balances[$payment['to_user_id']]['amount'] -= $payment['total_payment'];
            }
        }

        // 7. KLASIFIKASI DEBTOR DAN CREDITOR (UNTUK SETTLEMENT)
        $debtors = [];
        $creditors = [];

        foreach ($balances as $balance) {
            if ($balance['amount'] < -1) {
                $debtors[] = [
                    'name' => $balance['name'],
                    'amount' => abs($balance['amount'])
                ];
            }
            if ($balance['amount'] > 1) {
                $creditors[] = [
                    'name' => $balance['name'],
                    'amount' => $balance['amount']
                ];
            }
        }

        // 8. ALGORITMA MATCHING (SIAPA BAYAR KE SIAPA)
        $settlements = [];
        foreach ($debtors as &$debtor) {
            foreach ($creditors as &$creditor) {
                if ($debtor['amount'] <= 0) {
                    break;
                }
                if ($creditor['amount'] <= 0) {
                    continue;
                }

                $transfer = min($debtor['amount'], $creditor['amount']);

                $settlements[] = [
                    'from' => $debtor['name'],
                    'to' => $creditor['name'],
                    'amount' => $transfer
                ];

                $debtor['amount'] -= $transfer;
                $creditor['amount'] -= $transfer;
            }
        }
        unset($debtor, $creditor);

        return Response::success([
            'sessionId'   => $sessionId,
            'spentSummary'=> $spentSummary,
            'payments'    => $payments,
            'totalGroup'  => $totalGroup,
            'memberCount' => $memberCount,
            'perPerson'   => $perOrang,
            'settlements' => $settlements
        ]);
    }

    public function getRecapData(string $chatId, string $label): array
    {
        $sessionId = $this->sessionRepository->getActiveSessionId($chatId, $label);

        if (!$sessionId) {
            return Response::error("Tidak ada sesi aktif untuk label #{$label}.");
        }

        $spentSummary = $this->expenseRepository->getSpentSummary($sessionId, $chatId);
        $payments = $this->paymentRepository->getPaymentsBySession($sessionId);
        $rawExpenses = $this->expenseRepository->getHistoryBySessionId($sessionId);

        $balances = [];
        foreach ($spentSummary as $row) {
            $balances[$row['user_id']] = [
                'name' => $row['first_name'],
                'paid' => 0,      
                'spending' => 0,
                'pure_spent' => 0,
                'loan_details' => [], // 🟢 Tempat menyimpan relasi meminjamkan uang ke siapa saja
                'debt_details' => []  // 🟢 Tempat menyimpan relasi berutang uang ke siapa saja
            ];
        }

        $totalGroup = 0; 
        $memberCount = count($spentSummary);

        foreach ($rawExpenses as $expense) {
            $amount = (float)$expense['amount'];
            $paidBy = $expense['paid_by']; // ID pemberi uang/pinjaman[cite: 3, 4]
            $recordedBy = $expense['recorded_by']; // ID target/penerima utang[cite: 3, 4]
            $desc = $expense['description'];

            if (isset($balances[$paidBy])) {
                $balances[$paidBy]['paid'] += $amount;
            }

            // KONDISI A: TRANSAKSI PINJAMAN / UTANG PRIBADI (Tanpa pin emoji)
            if (strpos($desc, '[Pinjaman]') !== false || strpos($desc, '[Utang]') !== false) {
                if (isset($balances[$recordedBy])) {
                    $balances[$recordedBy]['spending'] += $amount;
                    
                    // Catat detail: Dia berutang ke $paidBy
                    $lenderName = $balances[$paidBy]['name'] ?? 'User';
                    if (!isset($balances[$recordedBy]['debt_details'][$lenderName])) {
                        $balances[$recordedBy]['debt_details'][$lenderName] = 0;
                    }
                    $balances[$recordedBy]['debt_details'][$lenderName] += $amount;
                }
                
                if (isset($balances[$paidBy])) {
                    // Catat detail: Dia meminjamkan uang ke $recordedBy
                    $borrowerName = $balances[$recordedBy]['name'] ?? 'User';
                    if (!isset($balances[$paidBy]['loan_details'][$borrowerName])) {
                        $balances[$paidBy]['loan_details'][$borrowerName] = 0;
                    }
                    $balances[$paidBy]['loan_details'][$borrowerName] += $amount;
                }
            } else {
                // KONDISI B: TRANSAKSI JAJAN BIASA
                $totalGroup += $amount; 
                if (isset($balances[$paidBy])) {
                    $balances[$paidBy]['pure_spent'] += $amount;
                }
                
                if ($memberCount > 0) {
                    $share = $amount / $memberCount;
                    foreach ($balances as $userId => &$b) {
                        $b['spending'] += $share;
                    }
                    unset($b);
                }
            }
        }

        // Jalankan pengurangan saldo cicilan dari tabel payments (jika ada)
        foreach ($payments as $payment) {
            $fromId = $payment['from_user_id'];
            $toId = $payment['to_user_id'];
            $payAmount = (float)$payment['total_payment'];

            if (isset($balances[$fromId])) {
                $balances[$fromId]['amount_payment_adjust'] = ($balances[$fromId]['amount_payment_adjust'] ?? 0) + $payAmount;
                $balances[$fromId]['paid'] += $payAmount; // Ditambahkan ke paid agar balance akhir impas
                
                // Kurangi catatan utang visualnya agar sinkron dengan cicilan yang masuk
                $toName = $balances[$toId]['name'] ?? '';
                if (isset($balances[$fromId]['debt_details'][$toName])) {
                    $balances[$fromId]['debt_details'][$toName] -= $payAmount;
                }
            }
            if (isset($balances[$toId])) {
                $balances[$toId]['spending'] += $payAmount; // Ditambahkan ke spending agar balance akhir impas
                
                // Kurangi catatan piutang visual si penerima cicilan
                $fromName = $balances[$fromId]['name'] ?? '';
                if (isset($balances[$toId]['loan_details'][$fromName])) {
                    $balances[$toId]['loan_details'][$fromName] -= $payAmount;
                }
            }
        }

        $perOrang = $memberCount > 0 ? $totalGroup / $memberCount : 0;

        foreach ($balances as $userId => &$b) {
            $b['amount'] = $b['paid'] - $b['spending'];
        }
        unset($b);

        // Bentuk Spent Summary Baru yang super lengkap
        $newSpentSummary = [];
        foreach ($balances as $userId => $b) {
            $newSpentSummary[] = [
                'user_id'      => $userId,
                'first_name'   => $b['name'],
                'pure_spent'   => $b['pure_spent'],
                'loan_details' => $b['loan_details'],
                'debt_details' => $b['debt_details']
            ];
        }

        // Logika Debtor/Creditor Settlement (Tetap seperti kode sebelumnya)
        $debtors = []; $creditors = [];
        foreach ($balances as $balance) {
            if ($balance['amount'] < -1) $debtors[] = ['name' => $balance['name'], 'amount' => abs($balance['amount'])];
            if ($balance['amount'] > 1) $creditors[] = ['name' => $balance['name'], 'amount' => $balance['amount']];
        }

        $settlements = [];
        foreach ($debtors as &$debtor) {
            foreach ($creditors as &$creditor) {
                if ($debtor['amount'] <= 0) break;
                if ($creditor['amount'] <= 0) continue;
                $transfer = min($debtor['amount'], $creditor['amount']);
                $settlements[] = ['from' => $debtor['name'], 'to' => $creditor['name'], 'amount' => $transfer];
                $debtor['amount'] -= $transfer; $creditor['amount'] -= $transfer;
            }
        }
        unset($debtor, $creditor);

        return Response::success([
            'sessionId'   => $sessionId,
            'spentSummary'=> $newSpentSummary,
            'payments'    => $payments,
            'totalGroup'  => $totalGroup,
            'memberCount' => $memberCount,
            'perPerson'   => $perOrang,
            'settlements' => $settlements
        ]);
    }

    public function getRecapBySessionId(int $sessionId): array
    {
        $members = $this->expenseRepository->getSpentSummaryBySession($sessionId);

        if (empty($members)) {
            return Response::error('Data tidak ditemukan.');
        }

        $totalExpense = 0;

        foreach ($members as $member) {
            $totalExpense += $member['total_spent'];
        }

        $memberCount = count($members);

        $perPerson = $memberCount > 0
            ? $totalExpense / $memberCount
            : 0;
        
        foreach ($members as &$member) {

            $member['balance'] =
                (float)$member['total_spent'] - $perPerson;

            $member['status'] =
                $member['balance'] >= 0
                    ? 'creditor'
                    : 'debtor';

        }

        unset($member);

        $settlements = $this->calculateSettlement($members);

        return Response::success([
            'total_expense' => $totalExpense,
            'member_count'  => $memberCount,
            'per_person'    => $perPerson,
            'members'       => $members,
            'settlements'   => $settlements
        ]);
    }

    private function calculateSettlement(array $members): array
    {
        $creditors = [];
        $debtors = [];

        foreach ($members as $member) {

            $balance = (float) $member['balance'];

            if ($balance > 0) {

                $creditors[] = [
                    'name' => $member['first_name'],
                    'balance' => $balance
                ];

            } elseif ($balance < 0) {

                $debtors[] = [
                    'name' => $member['first_name'],
                    'balance' => abs($balance)
                ];

            }

        }

        $settlements = [];

        $i = 0;
        $j = 0;

        while (
            $i < count($debtors) &&
            $j < count($creditors)
        ) {

            $amount = min(
                $debtors[$i]['balance'],
                $creditors[$j]['balance']
            );

            $settlements[] = [

                'from' => $debtors[$i]['name'],

                'to' => $creditors[$j]['name'],

                'amount' => $amount

            ];

            $debtors[$i]['balance'] -= $amount;
            $creditors[$j]['balance'] -= $amount;

            if ($debtors[$i]['balance'] < 0.01) {
                $i++;
            }

            if ($creditors[$j]['balance'] < 0.01) {
                $j++;
            }

        }

        return $settlements;
    }

    public function getHistoryBySessionId(int $sessionId): array
    {
        $history = $this->expenseRepository->getHistoryBySessionId($sessionId);

        return Response::success($history);
    }

    public function getDashboardSummary(): array
    {
        $summary = $this->expenseRepository->getDashboardSummary();

        $summary['total_session'] = (int) $summary['total_session'];
        $summary['active_session'] = (int) $summary['active_session'];
        $summary['closed_session'] = (int) $summary['closed_session'];
        $summary['total_expense'] = (float) $summary['total_expense'];

        return Response::success($summary);
    }

    public function getRecapByLabel(string $label): array
    {
        // Mengambil rekap member berdasarkan string label (gabungan dari semua sesi ber-label sama)
        $members = $this->expenseRepository->getSpentSummaryByLabel($label);

        if (empty($members)) {
            return Response::success([
                'total_expense' => 0,
                'member_count'  => 0,
                'per_person'    => 0,
                'members'       => [],
                'settlements'   => []
            ]);
        }

        $totalExpense = 0;
        foreach ($members as $member) {
            $totalExpense += $member['total_spent'];
        }

        $memberCount = count($members);
        $perPerson = $memberCount > 0 ? $totalExpense / $memberCount : 0;
        
        foreach ($members as &$member) {
            $member['balance'] = (float)$member['total_spent'] - $perPerson;
            $member['status'] = $member['balance'] >= 0 ? 'creditor' : 'debtor';
        }
        unset($member);

        // Kalkulasi pembagian settlement utang-piutang otomatis
        $settlements = $this->calculateSettlement($members);

        return Response::success([
            'total_expense' => $totalExpense,
            'member_count'  => $memberCount,
            'per_person'    => $perPerson,
            'members'       => $members,
            'settlements'   => $settlements
        ]);
    }

    public function getHistoryByLabel(string $label): array
    {
        // Mengambil semua transaksi dari semua sesi yang bernama label tersebut
        $history = $this->expenseRepository->getHistoryByLabelName($label);
        
        return Response::success($history);
    }
}