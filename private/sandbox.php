<?php

use Moose\Util\DebugUtil;


require_once './bootstrap.php';
Kint::enabled(true);

$a = ['ad' => 1];
$b = ['ad' => 2];
$c=array_merge($a,$b);
var_dump($c);

//echo Moose\Context\Context::getInstance()->getEngine()->render('t_sandbox');
die();

DebugUtil::sendDump();