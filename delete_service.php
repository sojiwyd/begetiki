<?php
session_start();
require 'db.php';
require 'check_admin.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: admin_services.php");
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    die("CSRF Attack blocked. <a href='admin_services.php'>Вернуться</a>");
}

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    header("Location: admin_services.php");
    exit;
}

$stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
$stmt->execute([$id]);

header("Location: admin_services.php");
exit;
