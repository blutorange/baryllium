<?php

list($messageId, $messageType) = \mb_split(':', 'abcdef');
var_dump($messageType);
die();

//namespace Sandbox;

require_once './bootstrap.php';
Kint::enabled(true);

//Context::getInstance()->getCache()->deleteAll();


//Context::getInstance()->getCache()->deleteAll();
//#\Moose\Context\Context::getInstance()->getCache()->save('testing', ['hello', 'world']);
//DebugUtil::dump(Context::getInstance()->getCache()->fetch('testing'));

DebugUtil::sendDump();
