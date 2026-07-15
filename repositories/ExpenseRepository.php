<?php

class ExpenseRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Mengambil riwayat transaksi berdasarkan label dan chat.
     */
    public function getHistory(string $label, string $chatId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                e.id,
                e.amount,
                e.description,
                e.created_at,
                m.first_name,
                s.status
            FROM expenses e
            JOIN members m ON e.paid_by = m.user_id
            JOIN sessions s ON e.session_id = s.id
            WHERE
                s.label = ?
                AND m.chat_id = ?
                AND s.status = 'Active'
            ORDER BY e.created_at DESC
        ");

        $stmt->execute([$label, $chatId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSpentSummary(int $sessionId, string $chatId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                m.user_id,
                m.first_name,
                IFNULL(SUM(e.amount), 0) AS total_spent
            FROM members m
            LEFT JOIN expenses e
                ON m.user_id = e.paid_by
                AND e.session_id = ?
            WHERE m.chat_id = ?
            GROUP BY m.user_id
        ");

        $stmt->execute([$sessionId, $chatId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSpentSummaryBySession(int $sessionId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                m.user_id,
                m.first_name,
                IFNULL(SUM(e.amount), 0) AS total_spent
            FROM members m
            LEFT JOIN expenses e
                ON m.user_id = e.paid_by
                AND e.session_id = ?
            WHERE m.chat_id = (
                SELECT chat_id
                FROM sessions
                WHERE id = ?
            )
            GROUP BY
                m.user_id,
                m.first_name
            ORDER BY
                m.first_name
        ");

        $stmt->execute([
            $sessionId,
            $sessionId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getHistoryBySessionId(int $sessionId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                e.id,
                e.description,
                e.amount,
                e.created_at,
                m.first_name AS paid_by
            FROM expenses e
            JOIN members m
                ON e.paid_by = m.user_id
            WHERE e.session_id = ?
            ORDER BY e.created_at DESC
        ");

        $stmt->execute([$sessionId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDashboardSummary(): array
    {
        $stmt = $this->pdo->query("
            SELECT
                COUNT(*) AS total_session,
                SUM(CASE WHEN status = 'Active' THEN 1 ELSE 0 END) AS active_session,
                SUM(CASE WHEN status = 'Closed' THEN 1 ELSE 0 END) AS closed_session
            FROM sessions
        ");

        $summary = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt = $this->pdo->query("
            SELECT
                COALESCE(SUM(amount), 0) AS total_expense
            FROM expenses
        ");

        $expense = $stmt->fetch(PDO::FETCH_ASSOC);

        $summary['total_expense'] = $expense['total_expense'];

        return $summary;
    }

    public function getSpentSummaryByLabel(string $label): array
    {
        $stmt = $this->pdo->prepare("
            SELECT 
                m.user_id, 
                m.first_name, 
                SUM(e.amount) as total_spent 
            FROM expenses e
            JOIN sessions s ON e.session_id = s.id
            JOIN members m ON e.paid_by = m.user_id AND s.chat_id = m.chat_id
            WHERE s.label = :label
            GROUP BY m.user_id, m.first_name
        ");

        $stmt->execute(['label' => $label]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getHistoryByLabelName(string $label): array
    {
        $stmt = $this->pdo->prepare("
            SELECT 
                e.id,
                e.session_id,
                e.amount,
                e.description,
                e.created_at,
                m.first_name as paid_by_name
            FROM expenses e
            JOIN sessions s ON e.session_id = s.id
            JOIN members m ON e.paid_by = m.user_id AND s.chat_id = m.chat_id
            WHERE s.label = :label
            ORDER BY e.created_at DESC
        ");

        $stmt->execute(['label' => $label]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}