<?php

use Composer\Autoload\ClassLoader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\DBAL\Types\EncryptedStringType;
use Doctrine\DBAL\Types\Type;
use Moose\Context\Context;
use Moose\Context\EnvironmentKeyProvider;
use Moose\Context\MooseConfig;
use Moose\Context\RequestKeyProvider;
use Moose\Util\DebugUtil;

return \call_user_func(function() use (& $argv) {
    $errorPrinted = false;
    \ini_set('session.name', 'MOOSE');
    \ini_set('display_errors', 'off');
    \ini_set('display_startup_errors', 'off');
    \ini_set('html_errors', 'off');
    \ini_set('docref_ext', 0);
    \ini_set('docref_root', 0);
    \error_reporting(0);

    /* Set internal character encoding to UTF-8 */
    mb_internal_encoding('UTF-8');
    mb_http_output('UTF-8');
    
    /** @var ClassLoader $loader */
    $loader = require(\dirname(__FILE__, 2) . '/' . 'vendor/autoload.php');

    /* Disable KINT, enable it later in development mode. */
    Kint::enabled(false);
    
    /*
     * Override global error handling settings.
     * Do not send any error messages to the browser in production mode.
     */
    $getlog = function() {
        $logfile = Context::getInstance()->getConfiguration()->getCurrentEnvironment()->getLogFile();
        if (!\file_exists($logfile)) {
            $dir = \dirname($logfile);
            if (!\file_exists($dir)) {
                \mkdir($dir, 0600, true);
            }
            \touch($logfile);
        }
        return $logfile;
    };
    
    /*
     * Log the error to the logfile. In development mode, output the error to
     * the browser as well for convenience.
     */
    \set_error_handler(function($errno, $errstring, $errfile, $errline) use (&$errorPrinted, &$getlog) {
        try {
            $time = (new DateTime())->format('[Y-m-d H:i:s e]');
            $main = "Unhandled error ($errno): $errstring in $errfile:$errline\n";
            \file_put_contents(\call_user_func($getlog), "$time $main", \FILE_APPEND);
            $isLocalhost = Context::getInstance()->getRequest()->isLocalhost();
            $isProduction = Context::getInstance()->getConfiguration()->isEnvironment(MooseConfig::ENVIRONMENT_PRODUCTION);
            if ($isLocalhost || !$isProduction) {
                DebugUtil::dump($main, 'Unhandled error occured.');
            }
        } catch (Throwable $ignored) {
        } finally {
            return true;
        }
    });
    
    // Log the exception to the logfile. In dev mode, send details about the
    // exception to the browser.
    \set_exception_handler(function($throwable) use (&$errorPrinted, &$getlog) {
        try {
            $time = (new DateTime())->format('[Y-m-d H:i:s e]');
            $isLocalhost = Context::getInstance()->getRequest()->isLocalhost();
            $isProduction = Context::getInstance()->getConfiguration()->isEnvironment(MooseConfig::ENVIRONMENT_PRODUCTION);
            \file_put_contents(\call_user_func($getlog), "$time $throwable". "\n", \FILE_APPEND);
            if (!$isLocalhost && $isProduction) {
                if ($errorPrinted===true){return;}
                $errorPrinted = true;
                echo "UNHANDLED ERROR. THIS IS A PRODUCTION ENVIRONMENT. NO MORE DETAILS ARE AVAILABLE.<br>";
            }
            else {
                while ($throwable !== null) {
                    $message = $throwable->getMessage();
                    $message = empty($message) ? 'No message available' : $message;
                    $class = \get_class($throwable);
                    $file = $throwable->getFile();
                    $line = $throwable->getLine();
                    $trace = $throwable->getTraceAsString();
                    echo "<details><summary>Unhandled exception $class: $message in $file:$line</summary>";
                    echo "<pre>$trace</pre>";
                    echo "</details>";
                    $throwable = $throwable->getPrevious();
                }
            }
        } catch (Throwable $ignored) {
            if ($errorPrinted===true){return;}
            $errorPrinted = true;
            echo "UNHANDLED ERROR. THIS IS A PRODUCTION ENVIRONMENT. NO MORE DETAILS ARE AVAILABLE.<br>";
        }
    });
    
    /* Now configure the context. Load key from either CLI or request params.*/
    if (php_sapi_name() === 'cli') {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $keyProvider = EnvironmentKeyProvider::fromCliEnvironment();
    }
    else {
        $keyProvider = RequestKeyProvider::fromGlobals();
    }
    Context::configureInstance(\dirname(__FILE__, 2), $keyProvider);

    /* Write errors to the logfile. */
    \ini_set('log_errors ', 'on');
    \ini_set('error_log', \call_user_func($getlog));
    
    /* Register doctrine types */
    Type::addType(EncryptedStringType::TPYE_NAME, EncryptedStringType::class);
    
    /* Setup doctrine annotation reader. */
    AnnotationRegistry::registerLoader([$loader, 'loadClass']);
    
    /* Now we are ready to create the context instance. */
    $context = Context::getInstance();
    
    /* Apply some security settings. */
    \ini_set('session.cookie_httponly', $context->getConfiguration()->getSecurity()->getHttpOnly() ? '1' : '0');
    \ini_set('session.cookie_secure', $context->getConfiguration()->getSecurity()->getSessionSecure() ? '1' : '0');
    \ini_set('session.cookie_lifetime', 0);    
});
