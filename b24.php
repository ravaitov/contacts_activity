<?php
//header('Access-Control-Allow-Origin: *');
define("NO_KEEP_STATISTIC", true);
require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php";

global $USER;
$userId = $USER->GetID();

$file = __DIR__ . '/tt';
if (!is_file($file))
    file_put_contents($file, '');
else {
    if (filemtime($file) > time() - 3) { // чтобы не чаще раз в 3 с
        file_put_contents(__DIR__ . '/repeat.txt', date("Y-m-d H:i:s ") . $_GET['url'] . "\n",FILE_APPEND);
        sleep(3);
    };
    touch($file);
}

$url = 'https://app.zemser.ru/' . $_GET['url'];
$cod = hash("sha256", (int)(time() / 5) . 'hjfguyd' . $userId);
Header("HTTP 302 Found");
Header("Location: $url?id=$userId&cod=$cod");
die();
