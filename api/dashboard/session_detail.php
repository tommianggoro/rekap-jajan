<?php
require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../../helpers/Response.php';
require_once __DIR__ . '/../../helpers/Constants.php';
require_once __DIR__ . '/../../repositories/SessionRepository.php';
require_once __DIR__ . '/../../services/SessionService.php';

header('Content-Type: application/json');

// Menerima parameter string label
$label = trim($_GET['label'] ?? '');

if (empty($label)) {
    echo json_encode(
        Response::error('Parameter label wajib diisi.')
    );
    exit;
}
$service = new SessionService($pdo);
echo json_encode($service->getSessionDetailByLabel($label));