<?php

require_once __DIR__ . '/../../bootstrap.php';

require_once __DIR__ . '/../../helpers/Response.php';
require_once __DIR__ . '/../../helpers/Constants.php';

require_once __DIR__ . '/../../repositories/SessionRepository.php';
require_once __DIR__ . '/../../services/SessionService.php';

header('Content-Type: application/json');

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(
        Response::error('Parameter id wajib diisi.')
    );
    exit;
}

$service = new SessionService($pdo);

echo json_encode(
    $service->getSessionDetail($id)
);