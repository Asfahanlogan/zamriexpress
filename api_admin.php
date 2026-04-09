<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function json_response(array $payload, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_SLASHES);
    exit;
}

function require_admin_json(): void
{
    if (!is_admin_logged_in()) {
        json_response(['ok' => false, 'error' => 'unauthorized'], 401);
    }
}

$action = (string) ($_GET['action'] ?? $_POST['action'] ?? '');

if ($action === 'status') {
    json_response(['ok' => true, 'logged_in' => is_admin_logged_in()]);
}

if ($action === 'login') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_response(['ok' => false, 'error' => 'method_not_allowed'], 405);
    }

    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if ($username === ADMIN_USERNAME && $password === ADMIN_PASSWORD) {
        $_SESSION['admin_logged_in'] = true;
        json_response(['ok' => true]);
    }

    json_response(['ok' => false, 'error' => 'invalid_credentials'], 401);
}

if ($action === 'logout') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_response(['ok' => false, 'error' => 'method_not_allowed'], 405);
    }

    $_SESSION = [];
    session_destroy();
    json_response(['ok' => true]);
}

if ($action === 'list') {
    require_admin_json();

    $orders = db()->query('SELECT * FROM orders ORDER BY created_at DESC')->fetchAll();
    json_response(['ok' => true, 'orders' => $orders]);
}

if ($action === 'mark_read') {
    require_admin_json();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_response(['ok' => false, 'error' => 'method_not_allowed'], 405);
    }

    $id = (int) ($_POST['id'] ?? 0);
    if ($id <= 0) {
        json_response(['ok' => false, 'error' => 'invalid_id'], 400);
    }

    $stmt = db()->prepare('UPDATE orders SET is_read = 1 WHERE id = :id');
    $stmt->execute([':id' => $id]);
    json_response(['ok' => true]);
}

if ($action === 'delete') {
    require_admin_json();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_response(['ok' => false, 'error' => 'method_not_allowed'], 405);
    }

    $id = (int) ($_POST['id'] ?? 0);
    if ($id <= 0) {
        json_response(['ok' => false, 'error' => 'invalid_id'], 400);
    }

    $stmt = db()->prepare('DELETE FROM orders WHERE id = :id');
    $stmt->execute([':id' => $id]);
    json_response(['ok' => true]);
}

json_response(['ok' => false, 'error' => 'unknown_action'], 400);

