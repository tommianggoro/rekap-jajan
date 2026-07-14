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

    public function getRecapData(string $chatId, string $label): array
    {
        $sessionId = $this->sessionRepository->getActiveSessionId($chatId, $label);

        if (!$sessionId) {
            return Response::error("Tidak ada sesi aktif untuk label #{$label}.");
        }

        $spentSummary = $this->expenseRepository->getSpentSummary($sessionId, $chatId);

        $payments = $this->paymentRepository->getPaymentsBySession($sessionId);

        $totalGroup = 0;

        foreach ($spentSummary as $row) {
            $totalGroup += $row['total_spent'];
        }

        $memberCount = count($spentSummary);

        $perPerson = $memberCount > 0
            ? $totalGroup / $memberCount
            : 0;

        $balances = [];

        foreach ($spentSummary as $row) {
            $balances[$row['user_id']] = [
                'name' => $row['first_name'],
                'amount' => $row['total_spent'] - $perPerson
            ];
        }

        foreach ($payments as $payment) {

            if (isset($balances[$payment['from_user_id']])) {
                $balances[$payment['from_user_id']]['amount'] += $payment['total_payment'];
            }

            if (isset($balances[$payment['to_user_id']])) {
                $balances[$payment['to_user_id']]['amount'] -= $payment['total_payment'];
            }
        }

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
            'perPerson'   => $perPerson,
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

        return Response::success([
            'total_expense' => $totalExpense,
            'member_count'  => $memberCount,
            'per_person'    => $perPerson,
            'members'       => $members
        ]);
    }
}