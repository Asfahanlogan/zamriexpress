<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

$loginError = '';
$flashMessage = '';

if (isset($_GET['logout'])) {
    $_SESSION = [];
    session_destroy();
    header('Location: admin_db_database_messages_2026.php');
    exit;
}

if (!is_admin_logged_in() && $_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'login') {
    $username = trim($_POST['username'] ?? '');
    $password = (string) ($_POST['password'] ?? '');

    if ($username === ADMIN_USERNAME && $password === ADMIN_PASSWORD) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: admin_db_database_messages_2026.php');
        exit;
    }

    $loginError = 'Invalid username or password.';
}

if (is_admin_logged_in() && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);

    if ($id > 0) {
        if ($action === 'mark_read') {
            $stmt = db()->prepare('UPDATE orders SET is_read = 1 WHERE id = :id');
            $stmt->execute([':id' => $id]);
            $flashMessage = 'Order marked as read.';
        }

        if ($action === 'delete') {
            $stmt = db()->prepare('DELETE FROM orders WHERE id = :id');
            $stmt->execute([':id' => $id]);
            $flashMessage = 'Order deleted.';
        }
    }
}

if (!is_admin_logged_in()):
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zamri Express Admin Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="admin-body">
    <main class="login-wrap">
        <section class="login-panel">
            <span class="eyebrow">Admin Access</span>
            <h1>Zamri Express Dashboard</h1>
            <p class="dashboard-subtitle">Sign in to review incoming delivery requests and manage their status.</p>
            <?php if ($loginError !== ''): ?>
                <div class="form-alert error"><?= h($loginError) ?></div>
            <?php endif; ?>
            <form method="post" class="contact-form">
                <input type="hidden" name="action" value="login">
                <div class="form-group full">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group full">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group full">
                    <button type="submit" class="btn btn-primary form-submit">Login</button>
                </div>
            </form>
        </section>
    </main>
</body>
</html>
<?php
exit;
endif;

$orders = db()->query('SELECT * FROM orders ORDER BY created_at DESC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zamri Express Admin Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="admin-body">
    <main class="dashboard-shell">
        <div class="container">
            <section class="dashboard-hero">
                <div class="dashboard-header">
                    <div>
                        <span class="eyebrow">Admin Dashboard</span>
                        <h1>Incoming delivery requests</h1>
                        <p class="dashboard-subtitle">Review new messages, mark them as read, or remove them when they are no longer needed.</p>
                    </div>
                    <a href="admin.php?logout=1" class="btn btn-primary">Logout</a>
                </div>
            </section>

            <section class="dashboard-card">
                <?php if ($flashMessage !== ''): ?>
                    <div class="form-alert success"><?= h($flashMessage) ?></div>
                <?php endif; ?>

                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Name</th>
                            <th>Contact</th>
                            <th>Destination</th>
                            <th>Package</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($orders === []): ?>
                            <tr>
                                <td colspan="7">No delivery requests yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td>
                                        <span class="status-badge <?= (int) $order['is_read'] === 1 ? 'status-read' : 'status-new' ?>">
                                            <?= (int) $order['is_read'] === 1 ? 'Read' : 'New' ?>
                                        </span>
                                    </td>
                                    <td><?= h($order['name']) ?></td>
                                    <td>
                                        <?= h($order['phone']) ?><br>
                                        <?= h($order['email']) ?>
                                    </td>
                                    <td><?= h($order['destination']) ?></td>
                                    <td><?= nl2br(h($order['package_description'])) ?></td>
                                    <td><?= h($order['created_at']) ?></td>
                                    <td>
                                        <?php if ((int) $order['is_read'] === 0): ?>
                                            <form method="post" class="action-form">
                                                <input type="hidden" name="action" value="mark_read">
                                                <input type="hidden" name="id" value="<?= (int) $order['id'] ?>">
                                                <button type="submit" class="action-btn read">Mark as Read</button>
                                            </form>
                                        <?php endif; ?>
                                        <form method="post" class="action-form" onsubmit="return confirm('Delete this order?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= (int) $order['id'] ?>">
                                            <button type="submit" class="action-btn delete">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>
        </div>
    </main>
</body>
</html>
