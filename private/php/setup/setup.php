<?php

use Moose\Context\Context;
use Moose\Context\MooseConfig;
use Moose\Util\CmnCnst;
use Moose\Util\DebugUtil;

function redirectWithQuery(string $url) {
    $query = $_SERVER['QUERY_STRING'] ?? '';
    if (!empty($query)) {
        $url .= '?' . $query;
    }
    \header("Location: $url");
}

// As this is an unsafe operation, check whether the admin really intents to do
// this.
$file = \dirname(__FILE__, 4) . DIRECTORY_SEPARATOR . 'FIRST_INSTALL';
if (!\file_exists($file)) {
    echo "Create file $file to run the setup guide.";
    die();
}

// Load required classes.
require_once \dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . '/' . 'autoload.php';
// Get location of the configuration file. When it exists already, this
// script is not needed and we may proceed to system setup.
$phinxPath = \dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'config';
$phinxFile = $phinxPath . DIRECTORY_SEPARATOR  . 'phinx.yml';
if (\file_exists($phinxFile)) {
    redirectWithQuery('./setup_system.php');
    die();
}

// Intelligently guess some settings and create an initial configuration file.
$contextPath = \dirname($_SERVER['PHP_SELF'], 4);
$isHttps = isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
$taskServer = ($isHttps ? 'https://' : 'http://') . $_SERVER['SERVER_NAME'];
if (isset($_SERVER['SERVER_PORT']) && !empty($_SERVER['SERVER_PORT'])) {
    $taskServer .= ':' . $_SERVER['SERVER_PORT'];
}

// Create the initial configuration data.
$environment = $_GET[CmnCnst::URL_PARAM_DEBUG_ENVIRONMENT] ?? MooseConfig::ENVIRONMENT_PRODUCTION;
try {
    $mooseConfig = MooseConfig::createDefault($contextPath, $taskServer, $environment);
    $mooseConfig->saveAs();
    Context::configureInstance();
    Context::getInstance()->getCache()->deleteAll();
    Context::getActualCache()->deleteAll();
    // Just redirect the user to this page.
    // This checks whether the configuration file exists now.
    redirectWithQuery('./setup.php');
}
catch (Throwable $e) {
    DebugUtil::dump($e, 'Could not create initial configuration file.');
    DebugUtil::sendDump();
}