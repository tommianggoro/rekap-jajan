<?php

require_once 'bootstrap.php';

echo getenv('MYSQLHOST');

echo "<br>";

echo password_hash('admin123', PASSWORD_DEFAULT);

echo "<br>";

session_start();

echo '<pre>';

print_r($_SESSION);

echo '</pre>';