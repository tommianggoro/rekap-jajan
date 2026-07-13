<?php

require_once __DIR__ . '/../../bootstrap.php';

require_once __DIR__ . '/../../helpers/Response.php';
require_once __DIR__ . '/../../helpers/Constants.php';

require_once __DIR__ . '/../../repositories/SessionRepository.php';
require_once __DIR__ . '/../../repositories/ExpenseRepository.php';
require_once __DIR__ . '/../../repositories/PaymentRepository.php';

require_once __DIR__ . '/../../services/ExpenseService.php';

header('Content-Type: application/json');

$chatId = $_GET['chat_id'] ?? '';
$label  = $_GET['label'] ?? '';

if (empty($chatId) || empty($label)) {

    echo json_encode(
        Response::error('Parameter chat_id dan label wajib diisi.')
    );

    exit;
}

$expenseService = new ExpenseService($pdo);

$result = $expenseService->getRecapData(
    $chatId,
    $label
);

echo json_encode($result);