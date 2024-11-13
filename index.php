<?php
$userId = (include '/home/worker/secure/auth.php')('reports/contacts_activity/');

require_once __DIR__ . '/vendor/autoload.php';

require_once 'header.html';
require_once 'body.php';
require_once 'footer.html';
