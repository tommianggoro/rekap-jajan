<?php

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../../helpers/Response.php';
require_once __DIR__ . '/../../helpers/Constants.php';
require_once __DIR__ . '/../../repositories/ExpenseRepository.php';
require_once __DIR__ . '/../../repositories/PaymentRepository.php';
require_once __DIR__ . '/../../repositories/SessionRepository.php';
require_once __DIR__ . '/../../services/ExpenseService.php';

header('Content-Type: application/json');

$label = trim($_GET['label'] ?? '');
$expenseService = new ExpenseService($pdo);
echo json_encode($expenseService->getRecapByLabel($label));