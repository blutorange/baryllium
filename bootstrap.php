<?php
require_once "vendor/autoload.php";

$context = new Context($_SERVER['PHP_SELF'], __FILE__, $GLOBALS['deployMode'] ?? Context::$MODE_DEFAULT);
$GLOBALS['context'] = $context;