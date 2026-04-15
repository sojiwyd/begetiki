<?php $title = 'Детали заказа'; require __DIR__ . '/../layouts/header.php'; ?>
<div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Заказ #<?= (int)$order['id'] ?></h5>
        <a href="<?= h(base_url('index.php?page=profile&action=index')) ?>" class="btn btn-outline-secondary btn-sm">← Назад</a>
    </div>
    <div class="card-body">
        <p><strong>Создан:</strong> <?= h(date('d.m.Y H:i', strtotime((string)$order['created_at']))) ?></p>
        <?php if (!empty($order['appointment_date'])): ?><p><strong>Дата визита:</strong> <?= h(date('d.m.Y H:i', strtotime((string)$order['appointment_date']))) ?></p><?php endif; ?>
        <p><strong>Услуга:</strong> <?= h($order['title']) ?></p>
        <p><strong>Цена:</strong> <?= h(format_price($order['price'])) ?></p>
        <p><strong>Статус:</strong> <span class="badge bg-<?= h(status_badge_class((string)$order['status'])) ?>"><?= h($order['status']) ?></span></p>
        <?php if (!empty($order['description'])): ?><p><strong>Описание:</strong> <?= h($order['description']) ?></p><?php endif; ?>
        <?php if (!empty($order['damage_photo_url'])): ?><p><strong>Фото повреждения:</strong></p><img src="<?= h(base_url($order['damage_photo_url'])) ?>" alt="Фото повреждения" class="img-fluid rounded" style="max-height: 300px;"><?php endif; ?>
    </div>
</div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
