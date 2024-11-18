<?php
require_once __DIR__ . '/vendor/autoload.php';

use App\Logger\Logger;
use App\Controller;

try {
//    Logger::instance()->log(print_r($_POST, 1));
    $del = new Controller();
    $del->run();
    $del->clearCache();
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