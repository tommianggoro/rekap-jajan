<?php

require_once __DIR__ . '/../../bootstrap.php';

session_start();

require_once __DIR__ . '/../../helpers/Response.php';

require_once __DIR__ . '/../../repositories/UserRepository.php';
require_once __DIR__ . '/../../services/AuthService.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(
        Response::error('Method tidak diizinkan.')
    );
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

if ($username === '' || $password === '') {
    echo json_encode(
        Response::error('Username dan password wajib diisi.')
    );
    exit;
}

$authService = new AuthService($pdo);

$result = $authService->login($username, $password);

if ($result['success']) {

    $_SESSION['user'] = $result['data'];

}

echo json_encode($result);