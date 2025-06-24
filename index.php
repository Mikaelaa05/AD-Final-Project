<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


require_once __DIR__ . '/bootstrap.php';

require_once BASE_PATH . '/handlers/mongodbChecker.handler.php';
require_once BASE_PATH . '/handlers/postgreChecker.handler.php';

echo ("Hello World");

?>