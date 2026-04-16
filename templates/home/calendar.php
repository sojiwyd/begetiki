<?php $title = 'Календарь занятости'; require __DIR__ . '/../layouts/header.php'; ?>
<div class="card shadow-sm">
    <div class="card-header bg-white">
        <h4 class="mb-0">Календарь занятости</h4>
        <small class="text-muted">Красный — занято, зелёный — свободно</small>
    </div>
    <div class="card-body">
        <form method="GET" class="mb-4 row g-2" id="calendarFilterForm">
            <input type="hidden" name="page" value="home">
            <input type="hidden" name="action" value="calendar">
            <div class="col-auto">
                <input type="date" id="calendarDateInput" name="date" class="form-control" value="<?= h($date) ?>">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">Показать</button>
            </div>
        </form>
        <div class="row g-2" id="slotsGrid">
            <?php foreach ($slots as $slot): ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="p-2 rounded text-center text-white <?= $slot['is_busy'] ? 'bg-danger' : 'bg-success' ?>">
                        <?= h($slot['time']) ?> — <?= $slot['is_busy'] ? 'Занято' : 'Свободно' ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<script>
(function () {
    const form = document.getElementById('calendarFilterForm');
    const dateInput = document.getElementById('calendarDateInput');
    const grid = document.getElementById('slotsGrid');
    if (!form || !dateInput || !grid) return;
    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
    function renderSlots(slots) {
        grid.innerHTML = slots.map((slot) => {
            const busy = Boolean(slot.is_busy);
            return `<div class="col-6 col-md-4 col-lg-3"><div class="p-2 rounded text-center text-white ${busy ? 'bg-danger' : 'bg-success'}">${escapeHtml(slot.time)} — ${busy ? 'Занято' : 'Свободно'}</div></div>`;
        }).join('');
    }
    async function loadSlots() {
        const response = await fetch('<?= h(base_url('index.php?page=api&action=slots')) ?>&date=' + encodeURIComponent(dateInput.value));
        if (!response.ok) return;
        const data = await response.json();
        if (data && Array.isArray(data.slots)) renderSlots(data.slots);
    }
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        loadSlots();
    });
})();
</script>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
