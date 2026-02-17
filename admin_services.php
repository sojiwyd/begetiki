<?php
require 'check_admin.php';
require 'db.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;
$q = trim($_GET['q'] ?? '');

$where_sql = '';
$params = [];
if ($q !== '') {
    $where_sql = " WHERE title LIKE ?";
    $params[] = '%' . $q . '%';
}

$count_sql = "SELECT COUNT(*) FROM services" . $where_sql;
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_rows = (int)$count_stmt->fetchColumn();
$total_pages = max(1, (int)ceil($total_rows / $limit));

if ($page > $total_pages) {
    $page = $total_pages;
    $offset = ($page - 1) * $limit;
}

$sql = "SELECT * FROM services" . $where_sql . " ORDER BY id DESC LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$services = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Управление услугами — Автосервис</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
<div class="container">
    <h1>Управление услугами</h1>
    <?php if (!empty($_GET['updated'])): ?>
        <div class="alert alert-success py-2 mb-2">Услуга обновлена.</div>
    <?php endif; ?>
    <a href="admin_panel.php" class="btn btn-secondary mb-2">← В админку</a>
    <a href="add_item.php" class="btn btn-success mb-2">+ Добавить услугу</a>
    <a href="index.php" class="btn btn-outline-primary mb-2">На главную</a>

    <form method="GET" class="row g-2 mt-2 mb-2">
        <div class="col-md-9">
            <input type="text" name="q" class="form-control" value="<?= h($q) ?>" placeholder="Поиск услуги по названию...">
        </div>
        <div class="col-md-3 d-grid">
            <button type="submit" class="btn btn-primary">Найти</button>
        </div>
    </form>

    <table class="table table-bordered mt-3">
        <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Название</th>
                <th>Цена</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($services as $s): ?>
            <tr>
                <td><?= (int)$s['id'] ?></td>
                <td><?= h($s['title']) ?></td>
                <td><?= number_format((float)$s['price'], 0, '', ' ') ?> ₽</td>
                <td>
                    <a href="edit_service.php?id=<?= (int)$s['id'] ?>" class="btn btn-warning btn-sm">✏️ Редактировать</a>
                    <form action="delete_service.php" method="POST" class="d-inline" onsubmit="return confirm('Удалить услугу?');">
                        <input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
                        <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
                        <button type="submit" class="btn btn-danger btn-sm">🗑️ Удалить</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (count($services) === 0): ?>
            <tr><td colspan="4" class="text-muted">Услуг пока нет. <a href="add_item.php">Добавить</a></td></tr>
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
                $url = 'admin_services.php?' . http_build_query($query_params);
            ?>
            <li class="page-item <?= ($i === $page) ? 'active' : '' ?>">
                <a class="page-link" href="<?= h($url) ?>"><?= $i ?></a>
            </li>
            <?php endfor; ?>
        </ul>
    </nav>
    <?php endif; ?>
</div>
</body>
</html>
