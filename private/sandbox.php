<?php

use Moose\Util\DebugUtil;


require_once './bootstrap.php';
Kint::enabled(true);

var_dump(is_object(json_decode('{}')));

//echo Moose\Context\Context::getInstance()->getEngine()->render('t_sandbox');
die();

DebugUtil::sendDump();