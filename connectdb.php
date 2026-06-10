<?php

$env = [];

foreach (file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    if (strpos(trim($line), '#') === 0) {
        continue;
    }

    [$key, $value] = explode('=', $line, 2);
    $env[trim($key)] = trim($value);
}

if ($env === false) {
    die('Impossible de charger le fichier .env');
}

$db = new PDO(
    "mysql:host={$env['DB_HOST']};dbname={$env['DB_NAME']};charset=utf8mb4",
    $env['DB_USER'],
    $env['DB_PASS']
);

$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);