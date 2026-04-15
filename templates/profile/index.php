<?php $title = 'Личный кабинет'; require __DIR__ . '/../layouts/header.php'; ?>
<div class="card shadow-sm">
    <div class="card-header bg-white"><h2 class="mb-0">Мои заказы / записи</h2></div>
    <div class="card-body">
        <?php if ($updatedMessage): ?><div class="alert alert-success py-2 small"><?= h($updatedMessage) ?></div><?php endif; ?>
        <?php if (count($orders) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                    <tr>
                        <th>№ заказа</th>
                        <th>Создан</th>
                        <th>Дата визита</th>
                        <th>Услуга</th>
                        <th>Цена</th>
                        <th>Фото</th>
                        <th>Статус</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>#<?= (int)$order['order_id'] ?></td>
                            <td><?= h(date('d.m.Y H:i', strtotime((string)$order['created_at']))) ?></td>
                            <td><?= !empty($order['appointment_date']) ? h(date('d.m.Y H:i', strtotime((string)$order['appointment_date']))) : '—' ?></td>
                            <td><strong><?= h($order['title']) ?></strong></td>
                            <td><?= h(format_price($order['price'])) ?></td>
                            <td><?php if (!empty($order['damage_photo_url'])): ?><a href="<?= h(base_url($order['damage_photo_url'])) ?>" target="_blank">Просмотр</a><?php else: ?>—<?php endif; ?></td>
                            <td><span class="badge bg-<?= h(status_badge_class((string)$order['status'])) ?>"><?= h($order['status']) ?></span></td>
                            <td>
                                <a href="<?= h(base_url('index.php?page=orders&action=show&id=' . (int)$order['order_id'])) ?>" class="btn btn-sm btn-outline-primary">Подробнее</a>
                                <?php $canEdit = ($order['status'] === 'new') && !empty($order['appointment_date']) && strtotime((string)$order['appointment_date']) > time(); ?>
                                <?php if ($canEdit): ?>
                                    <a href="<?= h(base_url('index.php?page=orders&action=edit&id=' . (int)$order['order_id'])) ?>" class="btn btn-sm btn-outline-warning">Перенести</a>
                                <?php endif; ?>
                                <?php $hoursLeft = !empty($order['appointment_date']) ? (strtotime((string)$order['appointment_date']) - time()) / 3600 : 0; ?>
                                <?php if (($order['status'] === 'new') && !empty($order['appointment_date']) && $hoursLeft >= 24): ?>
                                    <form action="<?= h(base_url('index.php?page=orders&action=cancel')) ?>" method="POST" class="d-inline" onsubmit="return confirm('Отменить запись?');">
                                        <input type="hidden" name="csrf_token" value="<?= h(Csrf::token()) ?>">
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
                <a href="<?= h(base_url('index.php')) ?>" class="btn btn-primary mt-3">Перейти в каталог</a>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
