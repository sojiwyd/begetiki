<?php
declare(strict_types=1);

final class CalendarController
{
    public function index(): void
    {
        $date = (string)($_GET['date'] ?? date('Y-m-d'));
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $date = date('Y-m-d');
        }
        $busy = (new Order())->getBusySlots($date);
        $slots = [];
        foreach (get_slots() as $slot) {
            $slots[] = ['time' => $slot, 'is_busy' => !empty($busy[$slot])];
        }

        View::render('calendar/index', ['date' => $date, 'slots' => $slots, 'flash' => get_flash()]);
    }
}
