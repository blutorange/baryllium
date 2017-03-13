<?php

// Set this to the path on the server where you put the application.
// For example, when autoload.php is located at
//   http://myserver.org/other/baryllium/vendor/autoload.php
// you would set this to
//   other/baryllium
$contextPath = '';

$GLOBALS['contextPath'] = $contextPath;
require_once(dirname(__FILE__, 2) . '/' . 'vendor/autoload.php');
$context = new Context($contextPath, dirname(__FILE__, 2));
$GLOBALS['context'] = $context;