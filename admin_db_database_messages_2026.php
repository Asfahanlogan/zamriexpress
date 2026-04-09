<?php
declare(strict_types=1);

$target = 'admin_db_database_messages_2026.html';
if (!empty($_SERVER['QUERY_STRING'])) {
    $target .= '?' . $_SERVER['QUERY_STRING'];
}

header('Location: ' . $target);
exit;
