<?php
require_once __DIR__ . '/../cors.php';
require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../../helpers/Response.php';
require_once __DIR__ . '/../../helpers/Constants.php';
require_once __DIR__ . '/../../repositories/SessionRepository.php';
require_once __DIR__ . '/../../services/SessionService.php';

header('Content-Type: application/json');
$keyword = trim($_GET['keyword'] ?? '');
$sessionService = new SessionService($pdo);

echo json_encode(
    $sessionService->getActiveSessions($keyword)
);