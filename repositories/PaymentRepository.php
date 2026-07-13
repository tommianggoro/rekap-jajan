<?php

class PaymentRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getPaymentsBySession(int $sessionId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                from_user_id,
                to_user_id,
                SUM(amount) AS total_payment
            FROM payments
            WHERE session_id = ?
            GROUP BY from_user_id, to_user_id
        ");

        $stmt->execute([$sessionId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}