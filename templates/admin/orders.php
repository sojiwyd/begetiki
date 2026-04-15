<?php $title = 'Управление заказами'; require __DIR__ . '/../layouts/header.php'; ?>
<?php if (!empty($_GET['updated'])): ?><div class="alert alert-success py-2">Статус заказа обновлён.</div><?php endif; ?>
<?php if (!empty($_GET['deleted'])): ?><div class="alert alert-success py-2">Заказ удалён.</div><?php endif; ?>
<div class="d-flex flex-wrap gap-2 mb-3"><a href="<?= h(base_url('index.php?page=admin&action=dashboard')) ?>" class="btn btn-secondary">← В админку</a></div>
<form method="GET" class="row g-2 mb-3">
    <input type="hidden" name="page" value="admin"><input type="hidden" name="action" value="orders">
    <div class="col-md-6"><input type="text" name="q" class="form-control" placeholder="Поиск по email или услуге..." value="<?= h($query) ?>"></div>
    <div class="col-md-3"><select name="status" class="form-select"><option value="">Все статусы</option><?php foreach ($allowedStatuses as $status): ?><option value="<?= h($status) ?>" <?= $statusFilter === $status ? 'selected' : '' ?>><?= h($status) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-3 d-grid"><button type="submit" class="btn btn-primary">Фильтровать</button></div>
</form>
<table class="table table-bordered bg-white shadow-sm">
<thead><tr><th>ID</th><th>Создан</th><th>Дата визита</th><th>Фото</th><th>Статус</th><th>Клиент</th><th>Услуга</th><th>Цена</th><th>Действия</th></tr></thead>
<tbody>
<?php foreach ($orders as $order): ?>
<tr>
    <td><?= (int)$order['order_id'] ?></td>
    <td><?= h($order['created_at']) ?></td>
    <td><?= !empty($order['appointment_date']) ? h($order['appointment_date']) : '—' ?></td>
    <td><?php if (!empty($order['damage_photo_url'])): ?><a target="_blank" href="<?= h(base_url($order['damage_photo_url'])) ?>">Просмотр</a><?php else: ?>—<?php endif; ?></td>
    <td><span class="badge bg-<?= h(status_badge_class((string)$order['status'])) ?>"><?= h($order['status']) ?></span></td>
    <td><?= h($order['email']) ?></td>
    <td><?= h($order['title']) ?></td>
    <td><?= h(format_price($order['price'])) ?></td>
    <td>
        <form method="POST" class="d-flex gap-1 mb-1" action="<?= h(base_url('index.php?page=admin&action=updateOrder')) ?>">
            <input type="hidden" name="csrf_token" value="<?= h(Csrf::token()) ?>">
            <input type="hidden" name="order_id" value="<?= (int)$order['order_id'] ?>">
            <input type="hidden" name="action_type" value="update_status">
            <select name="status" class="form-select form-select-sm"><?php foreach ($allowedStatuses as $status): ?><option value="<?= h($status) ?>" <?= $order['status'] === $status ? 'selected' : '' ?>><?= h($status) ?></option><?php endforeach; ?></select>
            <button type="submit" class="btn btn-sm btn-warning">Сохранить</button>
        </form>
        <form method="POST" onsubmit="return confirm('Удалить заказ безвозвратно?');" action="<?= h(base_url('index.php?page=admin&action=updateOrder')) ?>">
            <input type="hidden" name="csrf_token" value="<?= h(Csrf::token()) ?>">
            <input type="hidden" name="order_id" value="<?= (int)$order['order_id'] ?>">
            <input type="hidden" name="action_type" value="delete_order">
            <button type="submit" class="btn btn-sm btn-danger w-100">Удалить</button>
        </form>
    </td>
</tr>
<?php endforeach; ?>
<?php if (count($orders) === 0): ?><tr><td colspan="9" class="text-muted">Заказов пока нет.</td></tr><?php endif; ?>
</tbody>
</table>
<?php if ($totalPages > 1): ?><nav><ul class="pagination"><?php for ($i = 1; $i <= $totalPages; $i++): ?><li class="page-item <?= $i === $page ? 'active' : '' ?>"><a class="page-link" href="<?= h(base_url('index.php?page=admin&action=orders&page_num=' . $i . '&q=' . urlencode($query) . '&status=' . urlencode($statusFilter))) ?>"><?= $i ?></a></li><?php endfor; ?></ul></nav><?php endif; ?>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
