<?php
require_once "vendor/autoload.php";

$context = new Context($_SERVER['PHP_SELF'], __FILE__, false);
$GLOBALS['context'] = $context;