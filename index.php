<?php
session_start();
require 'db.php';

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$sql = "SELECT * FROM services";
$params = [];
$where_clauses = [];

if (!empty($_GET['q'])) {
    $where_clauses[] = "title LIKE ?";
    $params[] = "%" . trim($_GET['q']) . "%";
}

$where_sql = count($where_clauses) > 0 ? " WHERE " . implode(" AND ", $where_clauses) : "";

$count_sql = "SELECT COUNT(*) FROM services" . $where_sql;
if ($params) {
    $stmt = $pdo->prepare($count_sql);
    $stmt->execute($params);
} else {
    $stmt = $pdo->query($count_sql);
}
$total_rows = (int)$stmt->fetchColumn();
$total_pages = max(1, (int)ceil($total_rows / $limit));

$sql .= $where_sql . " ORDER BY id DESC LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
if ($params) {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
} else {
    $stmt = $pdo->query($sql);
}
$products = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Главная — Автосервис</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-light bg-light px-4 mb-4">
    <span class="navbar-brand">Автосервис</span>
    <div>
        <?php if (isset($_SESSION['user_id'])): ?>
            <?php if ($_SESSION['user_role'] === 'admin'): ?>
                <a href="admin_panel.php" class="btn btn-danger btn-sm">Админка</a>
            <?php endif; ?>
            <a href="profile.php" class="btn btn-outline-primary btn-sm">Мои заказы</a>
            <a href="calendar.php" class="btn btn-outline-success btn-sm">Календарь</a>
            <a href="logout.php" class="btn btn-dark btn-sm">Выйти</a>
        <?php else: ?>
            <a href="login.php" class="btn btn-primary btn-sm">Войти</a>
            <a href="register.php" class="btn btn-outline-primary btn-sm">Регистрация</a>
        <?php endif; ?>
    </div>
</nav>

<div class="container">
    <div class="card mb-4 p-3 bg-light">
        <form action="index.php" method="GET" class="row g-3">
            <div class="col-md-8">
                <input type="text" name="q" class="form-control" 
                       placeholder="Поиск по названию услуги..." 
                       value="<?= h($_GET['q'] ?? '') ?>">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100">Найти</button>
            </div>
            <?php if (!empty($_GET['q'])): ?>
            <div class="col-12 text-end">
                <a href="index.php" class="text-muted text-decoration-none small">Сбросить фильтры</a>
            </div>
            <?php endif; ?>
        </form>
    </div>
    <div class="row">
        <?php foreach ($products as $p): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <img src="<?= h($p['image_url'] ?: 'https://via.placeholder.com/300') ?>" class="card-img-top" alt="" style="height: 200px; object-fit: cover;">
                    <div class="card-body">
                        <h5 class="card-title"><?= h($p['title']) ?></h5>
                        <p class="card-text"><?= h($p['description']) ?></p>
                        <p class="text-primary fw-bold"><?= h($p['price']) ?> ₽</p>
                        <a href="make_order.php?id=<?= (int)$p['id'] ?>" class="btn btn-primary mt-2">Записаться</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if (count($products) === 0): ?>
            <p class="text-muted">Услуг пока нет. Зайдите под админом и добавьте их.</p>
        <?php endif; ?>

        <?php if ($total_pages > 1): ?>
        <nav class="mt-4">
            <ul class="pagination justify-content-center">
                <?php
                $query_params = $_GET;
                for ($i = 1; $i <= $total_pages; $i++):
                    $query_params['page'] = $i;
                    $url = 'index.php?' . http_build_query($query_params);
                ?>
                <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                    <a class="page-link" href="<?= h($url) ?>"><?= $i ?></a>
                </li>
                <?php endfor; ?>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
