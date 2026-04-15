<?php $title = 'Админка'; require __DIR__ . '/../layouts/header.php'; ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Панель администратора</h1>
        <p class="text-muted mb-0">Управление услугами и заказами автосервиса.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= h(base_url('index.php?page=admin&action=createService')) ?>" class="btn btn-success">+ Добавить услугу</a>
        <a href="<?= h(base_url('index.php?page=admin&action=services')) ?>" class="btn btn-outline-primary">Все услуги</a>
        <a href="<?= h(base_url('index.php?page=admin&action=orders')) ?>" class="btn btn-outline-dark">Все заказы</a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="card shadow-sm h-100"><div class="card-body"><div class="text-muted small">Всего услуг</div><div class="display-6"><?= (int)$serviceCount ?></div></div></div></div>
    <div class="col-md-3"><div class="card shadow-sm h-100"><div class="card-body"><div class="text-muted small">Всего заказов</div><div class="display-6"><?= (int)$orderCount ?></div></div></div></div>
    <div class="col-md-2"><div class="card shadow-sm h-100"><div class="card-body"><div class="text-muted small">Новые</div><div class="display-6 text-primary"><?= (int)$statusCounts['new'] ?></div></div></div></div>
    <div class="col-md-2"><div class="card shadow-sm h-100"><div class="card-body"><div class="text-muted small">В работе</div><div class="display-6 text-warning"><?= (int)$statusCounts['processing'] ?></div></div></div></div>
    <div class="col-md-2"><div class="card shadow-sm h-100"><div class="card-body"><div class="text-muted small">Завершены</div><div class="display-6 text-success"><?= (int)$statusCounts['done'] ?></div></div></div></div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white"><h2 class="h5 mb-0">Последние заказы</h2></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light"><tr><th>ID</th><th>Клиент</th><th>Услуга</th><th>Дата визита</th><th>Статус</th></tr></thead>
                <tbody>
                <?php foreach ($recentOrders as $order): ?>
                    <tr>
                        <td>#<?= (int)$order['order_id'] ?></td>
                        <td><?= h($order['email']) ?></td>
                        <td><?= h($order['title']) ?></td>
                        <td><?= !empty($order['appointment_date']) ? h(date('d.m.Y H:i', strtotime((string)$order['appointment_date']))) : '—' ?></td>
                        <td><span class="badge bg-<?= h(status_badge_class((string)$order['status'])) ?>"><?= h($order['status']) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (count($recentOrders) === 0): ?>
                    <tr><td colspan="5" class="text-muted text-center py-4">Заказов пока нет.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
