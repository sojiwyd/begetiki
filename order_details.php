<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$order_id = (int)($_GET['id'] ?? 0);
$user_id = (int)$_SESSION['user_id'];

if ($order_id <= 0) {
    die("Заказ не найден или у вас нет прав на его просмотр. <a href='profile.php'>В мои заказы</a>");
}

// ПРОВЕРКА ВЛАДЕЛЬЦА (Anti-IDOR): заказ с таким ID И принадлежащий этому пользователю
$stmt = $pdo->prepare("
    SELECT orders.*, services.title, services.price, services.description, services.image_url
    FROM orders
    JOIN services ON orders.service_id = services.id
    WHERE orders.id = ? AND orders.user_id = ?
");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch();

if (!$order) {
    // Не пишем «чужой заказ» — защита от перебора (User Enumeration)
    die("Заказ не найден или у вас нет прав на его просмотр. <a href='profile.php'>В мои заказы</a>");
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Заказ #<?= (int)$order['id'] ?> — Автосервис</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-light bg-light mb-4">
    <div class="container">
        <a class="navbar-brand" href="index.php">Автосервис</a>
        <a href="profile.php" class="btn btn-outline-secondary btn-sm">← Мои заказы</a>
    </div>
</nav>
<div class="container">
    <div class="card shadow-sm">
        <div class="card-header"><h4 class="mb-0">Заказ #<?= (int)$order['id'] ?></h4></div>
        <div class="card-body">
            <p><strong>Создан:</strong> <?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></p>
            <?php if (!empty($order['appointment_date'])): ?>
            <p><strong>Дата визита:</strong> <?= date('d.m.Y H:i', strtotime($order['appointment_date'])) ?></p>
            <?php endif; ?>
            <p><strong>Услуга:</strong> <?= h($order['title']) ?></p>
            <p><strong>Цена:</strong> <?= h($order['price']) ?> ₽</p>
            <p><strong>Статус:</strong> <span class="badge bg-primary"><?= h($order['status']) ?></span></p>
            <?php if (!empty($order['description'])): ?>
                <p><strong>Описание:</strong> <?= h($order['description']) ?></p>
            <?php endif; ?>
            <?php if (!empty($order['damage_photo_url'])): ?>
                <p><strong>Фото повреждения:</strong></p>
                <img src="<?= h($order['damage_photo_url']) ?>" alt="Фото повреждения" class="img-fluid rounded" style="max-height: 300px;">
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
