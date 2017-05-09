<?php

use Moose\Extension\CampusDual\CampusDualException;
use Moose\Util\DebugUtil;


require_once './bootstrap.php';
Kint::enabled(true);

$e = new CampusDualException('Authorization denied.', CampusDualException::FLAG_ACCESS_DENIED);
var_dump($e);
var_dump($e->is(CampusDualException::FLAG_ACCESS_DENIED) ? 'yes' : 'no');

//echo Moose\Context\Context::getInstance()->getEngine()->render('t_sandbox');
die();

DebugUtil::sendDump();