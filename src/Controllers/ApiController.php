<?php
declare(strict_types=1);

class ApiController
{
    public function slots(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $date = (string)($_GET['date'] ?? date('Y-m-d'));
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            http_response_code(400);
            echo json_encode(['error' => 'Некорректная дата'], JSON_UNESCAPED_UNICODE);
            return;
        }
        $orderModel = new Order();
        echo json_encode([
            'date' => $date,
            'slots' => $orderModel->getBusySlots($date),
        ], JSON_UNESCAPED_UNICODE);
    }
}
