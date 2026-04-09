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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['ok' => false, 'error' => 'method_not_allowed'], 405);
}

$name = trim((string) ($_POST['name'] ?? ''));
$phone = trim((string) ($_POST['phone'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$destination = trim((string) ($_POST['destination'] ?? ''));
$packageDescription = trim((string) ($_POST['package_description'] ?? ''));

$allowedDestinations = ['France', 'Spain', 'Italy', 'Germany', 'Belgium', 'Netherlands', 'Portugal'];

if (
    $name === '' ||
    $phone === '' ||
    $email === '' ||
    $packageDescription === '' ||
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    !in_array($destination, $allowedDestinations, true)
) {
    json_response(['ok' => false, 'error' => 'validation_failed'], 400);
}

try {
    $stmt = db()->prepare(
        'INSERT INTO orders (name, phone, email, destination, package_description, is_read, created_at)
         VALUES (:name, :phone, :email, :destination, :package_description, 0, datetime("now"))'
    );

    $stmt->execute([
        ':name' => $name,
        ':phone' => $phone,
        ':email' => $email,
        ':destination' => $destination,
        ':package_description' => $packageDescription,
    ]);

    json_response(['ok' => true]);
} catch (Throwable $exception) {
    json_response(['ok' => false, 'error' => 'server_error'], 500);
}

