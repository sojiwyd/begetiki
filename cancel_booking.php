<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: profile.php");
    exit;
}

if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
    die("Ошибка безопасности: неверный CSRF-токен. <a href='profile.php'>Вернуться</a>");
}

$booking_id = (int)($_POST['id'] ?? 0);
$user_id = $_SESSION['user_id'];

if ($booking_id <= 0) {
    die("Неверный заказ. <a href='profile.php'>Вернуться</a>");
}

$sql = "DELETE FROM orders 
        WHERE id = ? 
        AND user_id = ? 
        AND status = 'new'
        AND appointment_date > DATE_ADD(NOW(), INTERVAL 24 HOUR)";

$stmt = $pdo->prepare($sql);
$stmt->execute([$booking_id, $user_id]);

if ($stmt->rowCount() > 0) {
    $success = true;
} else {
    $success = false;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Отмена записи — Автосервис</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center min-vh-100">
<div class="card shadow-sm text-center" style="max-width: 400px;">
    <div class="card-body p-4">
        <?php if ($success): ?>
            <div class="text-success mb-3"><svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-check-circle" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm3.857-9.809a.5.5 0 0 0-.708-.708L7 7.793 6.146 6.95a.5.5 0 1 0-.708.708l1 1a.5.5 0 0 0 .708 0l3.5-3.5z"/></svg></div>
            <h5 class="card-title">Запись успешно отменена</h5>
            <a href="profile.php" class="btn btn-primary mt-2">В мои заказы</a>
        <?php else: ?>
            <div class="text-danger mb-3"><svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-x-circle" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zM4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/></svg></div>
            <h5 class="card-title">Не удалось отменить</h5>
            <p class="text-muted small">Либо запись не ваша, либо осталось меньше 24 часов до визита, либо дата уже прошла.</p>
            <a href="profile.php" class="btn btn-outline-secondary mt-2">Вернуться</a>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
