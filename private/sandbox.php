<?php

use Moose\Util\DebugUtil;


require_once './bootstrap.php';
Kint::enabled(true);

echo Moose\Context\Context::getInstance()->getEngine()->render('t_sandbox');
die();

DebugUtil::sendDump();