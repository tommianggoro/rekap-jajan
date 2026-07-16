<?php
require_once __DIR__ . '/../cors.php';
require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../../helpers/Response.php';
require_once __DIR__ . '/../../helpers/Constants.php';
require_once __DIR__ . '/../../repositories/ExpenseRepository.php';
require_once __DIR__ . '/../../services/ExpenseService.php';

header('Content-Type: application/json');

$expenseService = new ExpenseService($pdo);

echo json_encode(
    $expenseService->getDashboardSummary()
);