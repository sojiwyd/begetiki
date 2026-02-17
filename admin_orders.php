<?php
require 'check_admin.php'; // Только админ!
require 'db.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$allowed_statuses = ['new', 'processing', 'done'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        die("CSRF Attack blocked. <a href='admin_orders.php'>Вернуться</a>");
    }

    $order_id = (int)($_POST['order_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($order_id > 0 && $action === 'update_status') {
        $new_status = $_POST['status'] ?? 'new';
        if (in_array($new_status, $allowed_statuses, true)) {
            $upd = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $upd->execute([$new_status, $order_id]);
            header("Location: admin_orders.php?updated=1");
            exit;
        }
    }

    if ($order_id > 0 && $action === 'delete_order') {
        $del = $pdo->prepare("DELETE FROM orders WHERE id = ?");
        $del->execute([$order_id]);
        header("Location: admin_orders.php?deleted=1");
        exit;
    }
}

$q = trim($_GET['q'] ?? '');
$status_filter = trim($_GET['status'] ?? '');
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$where = [];
$params = [];
if ($q !== '') {
    $where[] = "(users.email LIKE ? OR services.title LIKE ?)";
    $params[] = '%' . $q . '%';
    $params[] = '%' . $q . '%';
}
if ($status_filter !== '' && in_array($status_filter, $allowed_statuses, true)) {
    $where[] = "orders.status = ?";
    $params[] = $status_filter;
}

$where_sql = $where ? (" WHERE " . implode(" AND ", $where)) : "";

$count_sql = "
    SELECT COUNT(*)
    FROM orders
    JOIN users ON orders.user_id = users.id
    JOIN services ON orders.service_id = services.id
" . $where_sql;
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_rows = (int)$count_stmt->fetchColumn();
$total_pages = max(1, (int)ceil($total_rows / $limit));

if ($page > $total_pages) {
    $page = $total_pages;
    $offset = ($page - 1) * $limit;
}

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
" . $where_sql . "
    ORDER BY orders.id DESC
    LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
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
    <?php if (!empty($_GET['updated'])): ?>
        <div class="alert alert-success py-2 mb-2">Статус заказа обновлен.</div>
    <?php endif; ?>
    <?php if (!empty($_GET['deleted'])): ?>
        <div class="alert alert-success py-2 mb-2">Заказ удален.</div>
    <?php endif; ?>
    <a href="admin_panel.php" class="btn btn-secondary mb-2">← В админку</a>
    <a href="index.php" class="btn btn-outline-primary mb-2">На главную</a>

    <form method="GET" class="row g-2 mt-2 mb-2">
        <div class="col-md-6">
            <input type="text" name="q" class="form-control" placeholder="Поиск по email или услуге..." value="<?= h($q) ?>">
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">Все статусы</option>
                <?php foreach ($allowed_statuses as $status): ?>
                    <option value="<?= h($status) ?>" <?= ($status_filter === $status) ? 'selected' : '' ?>><?= h($status) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3 d-grid">
            <button type="submit" class="btn btn-primary">Фильтровать</button>
        </div>
    </form>

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
                <th>Действия</th>
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
                <td>
                    <form method="POST" class="d-flex gap-1 mb-1">
                        <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
                        <input type="hidden" name="order_id" value="<?= (int)$order['order_id'] ?>">
                        <input type="hidden" name="action" value="update_status">
                        <select name="status" class="form-select form-select-sm">
                            <?php foreach ($allowed_statuses as $status): ?>
                                <option value="<?= h($status) ?>" <?= ($order['status'] === $status) ? 'selected' : '' ?>><?= h($status) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-sm btn-warning">Сохранить</button>
                    </form>
                    <form method="POST" onsubmit="return confirm('Удалить заказ безвозвратно?');">
                        <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
                        <input type="hidden" name="order_id" value="<?= (int)$order['order_id'] ?>">
                        <input type="hidden" name="action" value="delete_order">
                        <button type="submit" class="btn btn-sm btn-danger w-100">Удалить</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (count($orders) === 0): ?>
            <tr><td colspan="9" class="text-muted">Заказов пока нет.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if ($total_pages > 1): ?>
    <nav>
        <ul class="pagination">
            <?php
            $query_params = $_GET;
            for ($i = 1; $i <= $total_pages; $i++):
                $query_params['page'] = $i;
                $url = 'admin_orders.php?' . http_build_query($query_params);
            ?>
            <li class="page-item <?= ($i === $page) ? 'active' : '' ?>">
                <a class="page-link" href="<?= h($url) ?>"><?= $i ?></a>
            </li>
            <?php endfor; ?>
        </ul>
    </nav>
    <?php endif; ?>
</body>
</html>
