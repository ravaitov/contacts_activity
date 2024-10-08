<?php
require_once __DIR__ . '/vendor/autoload.php';

use App\Logger\Logger;

try {
    $app = new App\CompanyList();
} catch (Throwable $t) {
    terminateError($t);
}

try {
    Logger::instance()->log(print_r($_POST, 1));
    $app->run();
    $i = 0;
    $tBody = '';// "<tbody>\n";
    foreach ($app->result as $name => $id) {
        $i++;
        $tBody .= <<<EOT
        <tr>\n
        <td>$i</td>
        <td><a onClick=\"jump('$id'); return false;\" >$name</a></td>
        <td>$id</td>
        <td>$id</td>
        <td>$id</td>
        <td>$id</td>
        <td>$id</td>
        <td>$id</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        </tr>\n
       EOT;
    }
    $str =<<<EOT
        <tr> 
        <th>#</th> 
        <th>Компания</th> 
        <th>Активность</th> 
        <th>Контакт</th> 
        <th>Ключевой?</th> 
        <th>36</th> 
        <th>37</th> 
        <th>38</th> 
        <th>39</th> 
        <th>40</th> 
        <th>50</th> 
        <th>555</th> 
        </tr>
        EOT;

//    $tBody .= "\n</tbody>";
    echo json_encode(['thead' => $str, 'body' => $tBody], JSON_UNESCAPED_UNICODE);
//    Logger::instance()->log($tBody);
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