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

    public function getActiveSessions(): array
    {
        $sessions = $this->sessionRepository->getActiveSessions();

        return Response::success($sessions);
    }
}