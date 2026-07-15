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

    public function getActiveSessions(string $keyword = ''): array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                id,
                label,
                created_at,
                status
            FROM sessions
            WHERE status = ? AND label LIKE ?
            ORDER BY created_at DESC
        ");

        $stmt->execute([
            Constants::SESSION_ACTIVE,
            "%{$keyword}%"
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllSessions(string $keyword = ''): array
    {
        $stmt = $this->pdo->prepare("
            SELECT 
                label,
                SUM(CASE WHEN status = 'Active' THEN 1 ELSE 0 END) AS active_count,
                SUM(CASE WHEN status = 'Closed' THEN 1 ELSE 0 END) AS closed_count
            FROM sessions
            WHERE label LIKE :keyword
            GROUP BY label
            ORDER BY label ASC;
        ");

        $stmt->execute([
            ":keyword" => "%{$keyword}%"
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

    public function getSessionDetailByLabel(string $label): array
    {
        // Panggil ke repository untuk mengecek status seluruh sesi dengan label tersebut
        $sessions = $this->getSessionsByLabelName($label);

        if (empty($sessions)) {
            return Response::error('Session dengan label tersebut tidak ditemukan.');
        }

        // Tentukan status grup: Jika ada minimal 1 session yang open, maka statusnya 'open'
        $status = 'Closed';
        foreach ($sessions as $s) {
            if ($s['status'] === 'Active') {
                $status = 'Active';
                break;
            }
        }

        $createdAt = $sessions[0]['created_at'] ?? null;
        $lastCreatedAt = end($sessions)['created_at'] ?? null;

        return Response::success([
            'label' => $label,
            'status' => $status,
            'created_at' => $createdAt,
            'last_created_at' => $lastCreatedAt,
            'total_sessions' => count($sessions)
        ]);
    }

    public function getSessionsByLabelName(string $label): array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                id,
                chat_id,
                label,
                created_at,
                status
            FROM sessions
            WHERE label = ?
        ");

        $stmt->execute([$label]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
