<?php

echo get_current_user();
die();

echo "<html><head><title>sandbox</title></head><body>HELLO!</body></html>";
die();
class Foo {
    public $x = 0;
    public function __toString() {
        return "bar";
    }
}

echo strval(false);
die();

//namespace Sandbox;

require_once './bootstrap.php';
Kint::enabled(true);

//Context::getInstance()->getCache()->deleteAll();


//Context::getInstance()->getCache()->deleteAll();
//#\Moose\Context\Context::getInstance()->getCache()->save('testing', ['hello', 'world']);
//DebugUtil::dump(Context::getInstance()->getCache()->fetch('testing'));

DebugUtil::sendDump();
