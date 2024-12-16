<?php
return function ($url) {
    $location = "Location: https://bitrix.zemser.team/local/b24_scripts/b24.php?url=$url";
    if (!$_GET) {
        Header("HTTP 302 Found");
        Header($location);
        die();
    }
    $cod = $_GET['cod'];
    $id = $_GET['id'];
    $t = (int)(time() / 5);
    for ($i = $t-2; $i <= $t + 2; $i++) {
        if ($cod == hash("sha256", $i . 'hjfguyd' . $id))
            return $_GET['id'];
    }
    Header("HTTP 302 Found");
    Header($location);
    die();
};

