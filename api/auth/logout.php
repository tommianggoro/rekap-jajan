<?php
require_once __DIR__ . '/../cors.php';
require_once __DIR__ . '/../../bootstrap.php';

session_unset();
session_destroy();

header('Content-Type: application/json');

require_once __DIR__ . '/../../helpers/Response.php';

echo json_encode(
    Response::success(null, 'Logout berhasil.')
);