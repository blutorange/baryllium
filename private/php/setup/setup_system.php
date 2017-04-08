<?php

use Moose\Context\Context;
use Moose\Controller\SetupController;

require_once '../../bootstrap.php';

$firstInstall = \dirname(__FILE__, 4) . DIRECTORY_SEPARATOR . 'FIRST_INSTALL';
$phinxPath = \dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'config';
$phinxFile = $phinxPath . DIRECTORY_SEPARATOR  . 'phinx.yml';

if (!\file_exists($firstInstall) || !\file_exists($phinxFile)) {
    \header('Location: ./setup.php');
    die();
}
Context::getActualCache()->deleteAll();
Context::getInstance()->getCache()->deleteAll();
(new SetupController())->process();