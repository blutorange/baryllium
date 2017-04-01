<?php

use Composer\Autoload\ClassLoader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\DBAL\Types\EncryptedStringType;
use Doctrine\DBAL\Types\Type;
use Moose\Context\Context;

\call_user_func(function() {
    $errorPrinted = false;
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
    $loader = require(dirname(__FILE__, 2) . '/' . 'vendor/autoload.php');

    /* Disable KINT */
    Kint::enabled(false);
    
    /*
     * Override global error handling settings.
     * Do not send any error messages to the browser in production mode.
     */
    $getlog = function() {
        $logfile = \Moose\Context\Context::getInstance()->getLogFile();
        if (!\file_exists($logfile)) {
            $dir = \dirname($logfile);
            if (!\file_exists($dir)) {
                \mkdir($dir, 0600, true);
            }
            touch ($logfile);
        }
        return $logfile;
    };
    
    \set_error_handler(function($errno, $errstring, $errfile, $errline) use (&$errorPrinted, &$getlog) {
        try {
            $time = (new \DateTime())->format('[Y-m-d H:i:s e]');
            $main = "Unhandled error ($errno): $errstring in $errfile:$errline\n";
            \file_put_contents($getlog(), "$time $main", FILE_APPEND);
            if (!\Moose\Context\Context::getInstance()->isMode(\Moose\Context\Context::MODE_PRODUCTION)) {
                \Moose\Util\DebugUtil::dump($main, 'Unhandled error occured.');
            }
        } catch (\Throwable $ignored) {
        } finally {
            return true;
        }
    });
    
    \set_exception_handler(function($throwable) use (&$errorPrinted, &$getlog) {
        try {
            $time = (new \DateTime())->format('[Y-m-d H:i:s e]');
            \file_put_contents($getlog(), "$time $throwable". "\n", FILE_APPEND);
            if (\Moose\Context\Context::getInstance()->isMode(\Moose\Context\Context::MODE_PRODUCTION)) {
                if ($errorPrinted===true){return;}
                $errorPrinted = true;
                echo "UNHANDLED ERROR. THIS IS A PRODUCTION ENVIRONMENT. NO MORE DETAILS ARE AVAILABLE.<br>";
            }
            else {
                while ($throwable !== null) {
                    $message = $throwable->getMessage();
                    $message = empty($message) ? 'No message available' : $message;
                    $file = $throwable->getFile();
                    $line = $throwable->getLine();
                    $trace = $throwable->getTraceAsString();
                    echo "<details><summary>Unhandled exception: $message in $file:$line</summary>";
                    echo "<pre>$trace</pre>";
                    echo "</details>";
                    $throwable = $throwable->getPrevious();
                }
            }
        } catch (\Throwable $ignored) {
            if ($errorPrinted===true){return;}
            $errorPrinted = true;
            echo "UNHANDLED ERROR. THIS IS A PRODUCTION ENVIRONMENT. NO MORE DETAILS ARE AVAILABLE.<br>";
        }
    });
    
    /* Now build the context. */
    Context::configureInstance(dirname(__FILE__, 2));

    /* Write errors to the logfile. */
    \ini_set('log_errors ', 'on');
    \ini_set('error_log', $getlog());
    
    /* Register doctrine types */
    Type::addType(EncryptedStringType::TPYE_NAME, EncryptedStringType::class);   
    
    /* Setup doctrine annotation reader. */
    AnnotationRegistry::registerLoader([$loader, 'loadClass']);

});

return Context::getInstance();