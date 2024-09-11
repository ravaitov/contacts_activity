<?php
require_once __DIR__ . '/vendor/autoload.php';

use App\Logger\Logger;

try {
    $app = new App\CompanyList();
} catch (Throwable $t) {
    terminateError($t);
}

try {
    $app->run();
    $i = 0;
    foreach ($app->result as $name => $id) {
        $i++;
        echo "<tr>\n";
        echo "<td>$i</td>";
        echo "<td><a onClick=\"jump('$id'); return false;\" >$name</a></td>";
        echo "<td>$id</td>";
        echo "</tr>\n";
    }
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