<?php
//$currentSessionName = 'usersActivity';
//if (session_name() !== $currentSessionName) session_name($currentSessionName);
//session_start();
//require_once 'constants.php';
//require_once $_SERVER['DOCUMENT_ROOT'] . "/library/technical_service.php";
//require_once $_SERVER['DOCUMENT_ROOT'] . "/luna/php_scripts/luna_pdo_connection.php";
//$luna = new \library\dataBase\dataBase($lunaPDO);
//$obToken = new \library\bitrixRestApi\SetToken(...TOKEN_PARAMETERS_ARRAY);

/** используй чтобы обновить токен в БД */
//$obToken->insertTokenDataToDatabase($luna);

require_once 'header.html';
require_once 'body.php';
require_once 'footer.html';
