<?php
declare(strict_types=1);

define('SESSION_DIR', __DIR__ . '/.sessions');

if (!is_dir(SESSION_DIR)) {
    mkdir(SESSION_DIR, 0775, true);
}

session_save_path(SESSION_DIR);
session_start();

define('DB_PATH', __DIR__ . '/database_local_hidded_by_admin_here_3036.db');

define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', 'change-this-password');

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $pdo = new PDO('sqlite:' . DB_PATH, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS orders (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            phone TEXT NOT NULL,
            email TEXT NOT NULL,
            destination TEXT NOT NULL,
            package_description TEXT NOT NULL,
            is_read INTEGER NOT NULL DEFAULT 0,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        )'
    );

    return $pdo;
}

function is_admin_logged_in(): bool
{
    return !empty($_SESSION['admin_logged_in']);
}

function require_admin_login(): void
{
    if (!is_admin_logged_in()) {
        header('Location: admin_db_database_messages_2026.html');
        exit;
    }
}

function h(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
