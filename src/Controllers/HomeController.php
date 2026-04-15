<?php
declare(strict_types=1);

class HomeController
{
    public function index(): void
    {
        $serviceModel = new Service();
        $page = max(1, (int)($_GET['page_num'] ?? 1));
        $limit = 9;
        $query = trim((string)($_GET['q'] ?? ''));

        $result = $serviceModel->paginate($query, $page, $limit);
        $totalPages = max(1, (int)ceil($result['total'] / $limit));
        if ($page > $totalPages) {
            $page = $totalPages;
            $result = $serviceModel->paginate($query, $page, $limit);
        }

        View::render('home/index', [
            'services' => $result['items'],
            'page' => $page,
            'totalPages' => $totalPages,
            'query' => $query,
        ]);
    }

    public function calendar(): void
    {
        $date = (string)($_GET['date'] ?? date('Y-m-d'));
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $date = date('Y-m-d');
        }
        $orderModel = new Order();
        View::render('home/calendar', [
            'date' => $date,
            'slots' => $orderModel->getBusySlots($date),
        ]);
    }
}
