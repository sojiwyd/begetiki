<?php $title = 'Главная — Автосервис'; require __DIR__ . '/../layouts/header.php'; ?>
<div class="card mb-4 p-3 bg-white shadow-sm">
    <form action="<?= h(base_url('index.php')) ?>" method="GET" class="row g-3">
        <input type="hidden" name="page" value="home">
        <input type="hidden" name="action" value="index">
        <div class="col-md-8">
            <input type="text" name="q" class="form-control" placeholder="Поиск по названию услуги..." value="<?= h($query) ?>">
        </div>
        <div class="col-md-4">
            <button type="submit" class="btn btn-primary w-100">Найти</button>
        </div>
    </form>
</div>
<div class="row">
    <?php foreach ($services as $service): ?>
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm">
                <?php if (!empty($service['image_url'])): ?>
                    <img src="<?= h(base_url($service['image_url'])) ?>" class="card-img-top" alt="<?= h($service['title']) ?>" style="height: 200px; object-fit: cover;">
                <?php else: ?>
                    <div class="card-img-top d-flex align-items-center justify-content-center text-muted bg-light border-bottom" style="height: 200px;">Изображение не загружено</div>
                <?php endif; ?>
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title"><?= h($service['title']) ?></h5>
                    <p class="card-text flex-grow-1"><?= h($service['description']) ?></p>
                    <p class="text-primary fw-bold mb-3"><?= h(format_price($service['price'])) ?></p>
                    <?php if (Auth::check()): ?>
                        <a href="<?= h(base_url('index.php?page=orders&action=create&service_id=' . (int)$service['id'])) ?>" class="btn btn-primary mt-auto">Записаться</a>
                    <?php else: ?>
                        <a href="<?= h(base_url('index.php?page=auth&action=login')) ?>" class="btn btn-outline-primary mt-auto">Войдите для записи</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    <?php if (count($services) === 0): ?>
        <p class="text-muted">Услуг пока нет.</p>
    <?php endif; ?>
</div>
<?php if ($totalPages > 1): ?>
<nav>
    <ul class="pagination justify-content-center">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                <a class="page-link" href="<?= h(base_url('index.php?page=home&action=index&page_num=' . $i . '&q=' . urlencode($query))) ?>"><?= $i ?></a>
            </li>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif; ?>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
