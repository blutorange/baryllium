<?php
use Doctrine\ORM\Tools\Console\ConsoleRunner;

// replace with file to your own project bootstrap
require_once './private/bootstrap.php';

return ConsoleRunner::createHelperSet(Context::getInstance()->getEm());