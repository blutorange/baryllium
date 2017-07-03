<?php

use Moose\Context\Context;
use Moose\Context\MooseConfig;
use Moose\Util\CmnCnst;
use Moose\Util\DebugUtil;

// Refuse to work in non-sane environments.
function assertSanity() {
    if (\get_magic_quotes_gpc() || \get_magic_quotes_runtime()) {
        echo 'Please disable magic quotes, then reload this page.';
        die();
    }
    if (\ini_get('expose_php')) {
        echo 'Please disable expose_php, then reload this page.';
        die();
    }
    if (\ini_get('short_open_tag')) {
        echo 'Please disable short_open_tag, then reload this page.';
        die();
    }
    
    $disabledFunctions = \array_map(function($name) {
        return \trim($name);
    },\explode(',', \ini_get('disable_functions') ?? ''));
    foreach (['exec','shell_exec','system', 'popen','curl_exec','curl_multi_exec','parse_ini_file','show_source'] as $name) {
        if (!\in_array($name, $disabledFunctions)) {
            echo "Please disable the functions exec,shell_exec,system,popen,curl_exec,curl_multi_exec,parse_ini_file,show_source via the disable_functions directive";
            die();
        }
    }
    if (\in_array('passthru', $disabledFunctions)) {
        echo "Please enable the function passthru via the disable_function directive, this is required for mime type detection.";
        die();
    }
}

function redirectWithQuery(string $url) {
    $query = $_SERVER['QUERY_STRING'] ?? '';
    if (!empty($query)) {
        $url .= '?' . $query;
    }
    \header("Location: $url");
}

assertSanity();

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
$server = ($isHttps ? 'https://' : 'http://') . $_SERVER['SERVER_NAME'];
if (isset($_SERVER['SERVER_PORT']) && !empty($_SERVER['SERVER_PORT'])) {
    $server .= ':' . $_SERVER['SERVER_PORT'];
}

// Create the initial configuration data.
$environment = $_GET[CmnCnst::URL_PARAM_DEBUG_ENVIRONMENT] ?? MooseConfig::ENVIRONMENT_DEVELOPMENT;
try {
    $mooseConfig = MooseConfig::createDefault($contextPath, $server, $server, $environment);
    $mooseConfig->saveAs(null, true, false);
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