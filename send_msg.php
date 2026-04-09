<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.html');
    exit;
}

$name = trim($_POST['name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');
$destination = trim($_POST['destination'] ?? '');
$packageDescription = trim($_POST['package_description'] ?? '');

$allowedDestinations = ['France', 'Spain', 'Italy', 'Germany', 'Belgium', 'Netherlands', 'Portugal'];

if (
    $name === '' ||
    $phone === '' ||
    $email === '' ||
    $packageDescription === '' ||
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    !in_array($destination, $allowedDestinations, true)
) {
    header('Location: index.html?status=error#contact');
    exit;
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

    header('Location: index.html?status=success#contact');
    exit;
} catch (Throwable $exception) {
    header('Location: index.html?status=error#contact');
    exit;
}
