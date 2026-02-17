<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$user_id = (int)$_SESSION['user_id'];

// БЕЗОПАСНОСТЬ (Anti-IDOR): только заказы текущего пользователя
$sql = "
    SELECT 
        orders.id AS order_id,
        orders.created_at,
        orders.appointment_date,
        orders.status,
        services.title,
        services.price,
        services.image_url
    FROM orders
    JOIN services ON orders.service_id = services.id
    WHERE orders.user_id = ?
    ORDER BY orders.created_at DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$my_orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личный кабинет — Автосервис</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="index.php">Автосервис</a>
        <div class="d-flex">
            <span class="navbar-text text-white me-3">Вы вошли как: <b><?= h($_SESSION['user_role'] ?? 'client') ?></b></span>
            <a href="profile.php" class="btn btn-outline-light btn-sm me-2">Мои заказы</a>
            <a href="change_password.php" class="btn btn-outline-light btn-sm me-2">Сменить пароль</a>
            <a href="logout.php" class="btn btn-outline-light btn-sm">Выйти</a>
        </div>
    </div>
</nav>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h2 class="mb-0">Мои заказы / записи</h2>
                </div>
                <div class="card-body">
                    <?php if (!empty($_GET['updated'])): ?>
                        <div class="alert alert-success py-2 small mb-3">Запись перенесена.</div>
                    <?php endif; ?>
                    <?php if (count($my_orders) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>№ заказа</th>
                                        <th>Создан</th>
                                        <th>Дата визита</th>
                                        <th>Услуга</th>
                                        <th>Цена</th>
                                        <th>Статус</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($my_orders as $order): ?>
                                        <tr>
                                            <td>#<?= (int)$order['order_id'] ?></td>
                                            <td><?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></td>
                                            <td><?= !empty($order['appointment_date']) ? date('d.m.Y H:i', strtotime($order['appointment_date'])) : '—' ?></td>
                                            <td><strong><?= h($order['title']) ?></strong></td>
                                            <td><?= number_format((float)$order['price'], 0, '', ' ') ?> ₽</td>
                                            <td>
                                                <?php
                                                $status_color = 'secondary';
                                                if ($order['status'] === 'new') $status_color = 'primary';
                                                if ($order['status'] === 'processing') $status_color = 'warning';
                                                if ($order['status'] === 'done') $status_color = 'success';
                                                ?>
                                                <span class="badge bg-<?= $status_color ?>"><?= h($order['status']) ?></span>
                                            </td>
                                            <td>
                                                <a href="order_details.php?id=<?= (int)$order['order_id'] ?>" class="btn btn-sm btn-outline-primary">Подробнее</a>
                                                <?php
                                                $can_edit = ($order['status'] === 'new') && !empty($order['appointment_date']) && strtotime($order['appointment_date']) > time();
                                                if ($can_edit): ?>
                                                    <a href="edit_order.php?id=<?= (int)$order['order_id'] ?>" class="btn btn-sm btn-outline-warning">Перенести</a>
                                                <?php endif;
                                                $hours_left = !empty($order['appointment_date']) ? (strtotime($order['appointment_date']) - time()) / 3600 : 0;
                                                $can_cancel = ($order['status'] === 'new') && !empty($order['appointment_date']) && $hours_left >= 24;
                                                if ($can_cancel): ?>
                                                    <form action="cancel_booking.php" method="POST" class="d-inline" onsubmit="return confirm('Отменить запись?');">
                                                        <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
                                                        <input type="hidden" name="id" value="<?= (int)$order['order_id'] ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">Отменить</button>
                                                    </form>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <h4 class="text-muted">Вы ещё ничего не заказывали.</h4>
                            <a href="index.php" class="btn btn-primary mt-3">Перейти в каталог</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
