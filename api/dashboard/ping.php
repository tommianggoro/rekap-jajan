<?php
require_once __DIR__ . '/../cors.php';
require_once __DIR__ . '/../../bootstrap.php';

header('Content-Type: application/json');

echo json_encode([
    'success' => true,
    'message' => 'Dashboard API OK',
    'time' => date('Y-m-d H:i:s')
]);