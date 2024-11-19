<?php

namespace App;

class Controller extends AbstractApp
{
    public function run(): void
    {
        $weekCnt = $_REQUEST['week'] ?? 0;
        $date = $_REQUEST['date'] ?? 0;
        $this->log("Количество недель: $weekCnt, дата: $date");
        if (!$weekCnt || !$date || $weekCnt > 10 || !preg_match('/\d\d\d\d-\d\d-\d\d/', $date)) {
            throw new \Exception("Не корректные данные!\n" . print_r($_REQUEST, 1));
        }
        static::$params = $_REQUEST;
//        $this->log('static = ' . print_r(static::$params, 1));
//        $this->log('SERVER = ' . print_r($_SERVER, 1));
    }
}