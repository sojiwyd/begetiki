<?php
require 'check_admin.php'; // Только админ!
require 'db.php';

// JOIN: объединяем 3 таблицы — orders + users (email) + services (название, цена)
$sql = "
    SELECT 
        orders.id AS order_id,
        orders.created_at,
        orders.appointment_date,
        orders.damage_photo_url,
        orders.status,
        users.email,
        services.title,
        services.price
    FROM orders
    JOIN users ON orders.user_id = users.id
    JOIN services ON orders.service_id = services.id
    ORDER BY orders.id DESC
";
$stmt = $pdo->query($sql);
$orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Управление заказами — Автосервис</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
    <h1>Все заказы / записи</h1>
    <a href="admin_panel.php" class="btn btn-secondary mb-2">← В админку</a>
    <a href="index.php" class="btn btn-outline-primary mb-2">На главную</a>

    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>ID заказа</th>
                <th>Создан</th>
                <th>Дата визита</th>
                <th>Фото</th>
                <th>Статус</th>
                <th>Клиент (Email)</th>
                <th>Услуга</th>
                <th>Цена</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
            <tr>
                <td><?= (int)$order['order_id'] ?></td>
                <td><?= h($order['created_at']) ?></td>
                <td><?= !empty($order['appointment_date']) ? h($order['appointment_date']) : '—' ?></td>
                <td><?php if (!empty($order['damage_photo_url'])): ?><a href="<?= h($order['damage_photo_url']) ?>" target="_blank">Просмотр</a><?php else: ?>—<?php endif; ?></td>
                <td><?= h($order['status']) ?></td>
                <td><?= h($order['email']) ?></td>
                <td><?= h($order['title']) ?></td>
                <td><?= h($order['price']) ?> ₽</td>
            </tr>
            <?php endforeach; ?>
            <?php if (count($orders) === 0): ?>
            <tr><td colspan="8" class="text-muted">Заказов пока нет.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
