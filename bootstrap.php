<?php
use Context;

require_once "vendor/autoload.php";

$context = new Context($_SERVER['DOCUMENT_ROOT']);
$GLOBALS['context'] = $context;