<?php

require_once __DIR__ . '/../helpers/Constants.php';

class SessionRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getActiveSessionId(string $chatId, string $label)
    {
        $stmt = $this->pdo->prepare("
            SELECT id
            FROM sessions
            WHERE chat_id = ?
            AND label = ?
            AND status = ?
            LIMIT 1
        ");

        $stmt->execute([$chatId, $label, Constants::SESSION_ACTIVE]);

        return $stmt->fetchColumn();
    }

    public function getActiveSessions(): array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                id,
                label,
                created_at,
                status
            FROM sessions
            WHERE status = ?
            ORDER BY created_at DESC
        ");

        $stmt->execute([
            Constants::SESSION_ACTIVE
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSessionById(int $id)
    {
        $stmt = $this->pdo->prepare("
            SELECT
                id,
                chat_id,
                label,
                created_at,
                status
            FROM sessions
            WHERE id = ?
            LIMIT 1
        ");

        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
