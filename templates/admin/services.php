<?php $title = 'Управление услугами'; require __DIR__ . '/../layouts/header.php'; ?>
<?php if (!empty($_GET['updated'])): ?><div class="alert alert-success py-2">Услуга обновлена.</div><?php endif; ?>
<div class="d-flex flex-wrap gap-2 mb-3">
    <a href="<?= h(base_url('index.php?page=admin&action=dashboard')) ?>" class="btn btn-secondary">← В админку</a>
    <a href="<?= h(base_url('index.php?page=admin&action=createService')) ?>" class="btn btn-success">+ Добавить услугу</a>
</div>
<form method="GET" class="row g-2 mb-3">
    <input type="hidden" name="page" value="admin"><input type="hidden" name="action" value="services">
    <div class="col-md-9"><input type="text" name="q" class="form-control" value="<?= h($query) ?>" placeholder="Поиск услуги по названию..."></div>
    <div class="col-md-3 d-grid"><button type="submit" class="btn btn-primary">Найти</button></div>
</form>
<table class="table table-bordered bg-white shadow-sm">
    <thead class="table-light"><tr><th>ID</th><th>Название</th><th>Цена</th><th>Действия</th></tr></thead>
    <tbody>
    <?php foreach ($services as $service): ?>
        <tr>
            <td><?= (int)$service['id'] ?></td>
            <td><?= h($service['title']) ?></td>
            <td><?= h(format_price($service['price'])) ?></td>
            <td>
                <a href="<?= h(base_url('index.php?page=admin&action=editService&id=' . (int)$service['id'])) ?>" class="btn btn-warning btn-sm">Редактировать</a>
                <form action="<?= h(base_url('index.php?page=admin&action=deleteService')) ?>" method="POST" class="d-inline" onsubmit="return confirm('Удалить услугу?');">
                    <input type="hidden" name="csrf_token" value="<?= h(Csrf::token()) ?>">
                    <input type="hidden" name="id" value="<?= (int)$service['id'] ?>">
                    <button type="submit" class="btn btn-danger btn-sm">Удалить</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php if (count($services) === 0): ?><tr><td colspan="4" class="text-muted">Услуг пока нет.</td></tr><?php endif; ?>
    </tbody>
</table>
<?php if ($totalPages > 1): ?><nav><ul class="pagination"><?php for ($i = 1; $i <= $totalPages; $i++): ?><li class="page-item <?= $i === $page ? 'active' : '' ?>"><a class="page-link" href="<?= h(base_url('index.php?page=admin&action=services&page_num=' . $i . '&q=' . urlencode($query))) ?>"><?= $i ?></a></li><?php endfor; ?></ul></nav><?php endif; ?>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
