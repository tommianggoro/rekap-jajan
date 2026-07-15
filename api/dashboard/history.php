<?php
require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../../helpers/Response.php';
require_once __DIR__ . '/../../helpers/Constants.php';
require_once __DIR__ . '/../../repositories/SessionRepository.php';
require_once __DIR__ . '/../../services/SessionService.php';

header('Content-Type: application/json');

$label = $_GET['keyword'] ?? '';
$sessionService = new SessionService($pdo);
echo json_encode($sessionService->getAllSessions($label));