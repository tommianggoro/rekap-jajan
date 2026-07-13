<?php

// ========================================
// LOAD .ENV (khusus local development)
// ========================================
if (file_exists(__DIR__ . '/.env')) {

    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {

        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        list($name, $value) = explode('=', $line, 2);

        $name = trim($name);
        $value = trim($value);

        putenv("$name=$value");
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
    }
}

// ========================================
// DATABASE
// ========================================
require_once __DIR__ . '/config/database.php';