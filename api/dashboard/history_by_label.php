<?php
require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../../helpers/Response.php';
require_once __DIR__ . '/../../helpers/Constants.php';
require_once __DIR__ . '/../../repositories/SessionRepository.php';
require_once __DIR__ . '/../../services/ExpenseService.php';

header('Content-Type: application/json');

// Ambil parameter label dari detail.php
$label = isset($_GET['label']) ? trim($_GET['label']) : '';

if ($label === '') {
    echo json_encode(Response::error('Parameter label tidak boleh kosong.'));
    exit;
}
$expenseService = new ExpenseService($pdo);
// Panggil service baru yang mengambil data per label
$history = $expenseService->getHistoryByLabel($label);

echo json_encode($history);