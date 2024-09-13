<?php
require_once __DIR__ . '/vendor/autoload.php';

use App\Logger\Logger;

Logger::instance()->echoLog = false;


try {
    $app = new App\ServisesApp;
} catch (Throwable $t) {
    terminateError($t);
}

try {
    $app->prepare([$_GET['id'], $_GET['start'], $_GET['end']]);
    $app->run();
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