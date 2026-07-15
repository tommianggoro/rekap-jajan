<?php

require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../repositories/SessionRepository.php';

class SessionService
{
    private SessionRepository $sessionRepository;

    public function __construct(PDO $pdo)
    {
        $this->sessionRepository = new SessionRepository($pdo);
    }

    public function getActiveSessions(string $keyword = ''): array
    {
        $sessions = $this->sessionRepository->getActiveSessions($keyword);

        return Response::success($sessions);
    }

    public function getSessionDetail(int $id): array
    {
        $session = $this->sessionRepository->getSessionById($id);

        if (!$session) {
            return Response::error('Session tidak ditemukan.');
        }

        return Response::success($session);
    }

    public function getAllSessions(string $keyword = ''): array
    {
        $sessions = $this->sessionRepository->getAllSessions($keyword);

        return Response::success($sessions);
    }

    public function getSessionDetailByLabel(string $label): array
    {
        $session = $this->sessionRepository->getSessionDetailByLabel($label);

        if (!$session) {
            return Response::error('Session tidak ditemukan.');
        }

        return Response::success($session);
    }
}