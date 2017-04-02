<?php


namespace Sandbox;

echo dirname(__DIR__);
echo __DIR__;
die();


 require_once './bootstrap.php';

Kint::enabled(true);
Kint::dump($_SERVER);
echo __FILE__;