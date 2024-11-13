<?php
require_once __DIR__ . '/vendor/autoload.php';

use App\Logger\Logger;
use App\PivotTable;
use App\Presenters\XlsxPresenter;
use App\Presenters\WebPresenter;

try {
    Logger::instance()->log(print_r($_POST, 1));
    (new \App\Controller())->run();
    $pt = new PivotTable();
    $pt->run();

    $presenter = empty($pt::$params['xlsx']) ? new WebPresenter($pt->result) :  new XlsxPresenter($pt->result);
    $presenter->sendTable();
} catch (Throwable $t) {
    terminateError($t);
}

function terminateError(Throwable $t): void
{
    Logger::instance()->log("!!! Error: " . $t->getMessage());
    http_response_code(400);
    echo $t->getMessage();
    exit();
}