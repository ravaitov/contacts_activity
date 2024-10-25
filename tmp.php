<?php
//if ($_GET) {
//    Header("HTTP 302 Found");
//    Header("Location: https://app.zemser.ru/reports/contacts_activity/tmp.php");
//    die();
//}
echo '<pre />';
//$ch = curl_init('https://bitrix.zemser.ru/local/b24_scripts/tmp.php?1');
//curl_exec($ch);
//$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
//echo $code;
print_r(getallheaders());
echo http_response_code();
print_r($_SERVER);
print_r($_REQUEST);
//https://bitrix.zemser.ru/local/b24_scripts/tmp.php
