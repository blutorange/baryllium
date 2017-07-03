<?php
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Moose\Context\Context;

require_once './private/bootstrap.php';
return ConsoleRunner::createHelperSet(Context::getInstance()->getEm());