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
}