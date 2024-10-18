<?php
require_once __DIR__ . '/vendor/autoload.php';

use App\Logger\Logger;
use App\PivotTable;
use App\Presenters\WebPresenter;

try {
    Logger::instance()->log(print_r($_POST, 1));
//    (new \App\InputsData())->run();
    $pt = new PivotTable();
    $pt->run();
    $wr = new WebPresenter($pt->result);
    echo $wr->sendTable();
} catch (Throwable $t) {
    terminateError($t);
}

function terminateError(Throwable $t): void
{
    Logger::instance()->log("!!! Error: " . $t->getMessage());
    http_response_code(400);
    echo '{"error": "'. $t->getMessage().'"}';
    exit();
}