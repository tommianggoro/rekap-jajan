<?php
require_once __DIR__ . '/../cors.php';
require_once __DIR__ . '/../../bootstrap.php';
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
error_log('SESSION LOGIN: ' . print_r($_SESSION, true));
echo json_encode($result);