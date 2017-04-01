<?php


namespace Sandbox;

/*
 * Override global error handling settings.
 * Do not send any error messages to the browser in production mode.
 */
//\call_user_func(function() {
//    $errorPrinted = false;
//    \ini_set('display_errors', 'off');
//    \ini_set('display_startup_errors', 'off');
//    \ini_set('html_errors', 'off');
//    \ini_set('docref_ext', 0);
//    \ini_set('docref_root', 0);
//    \error_reporting(0);
//    require_once './bootstrap.php';
//    $getlog = function() {
//        $logfile = \Context::getInstance()->getLogFile();
//        if (!\file_exists($logfile)) {
//            $dir = \dirname($logfile);
//            if (!\file_exists($base)) {
//                \mkdir($dir, 0600, true);
//            }
//            touch ($logfile);
//        }
//        return $logfile;
//    };
//    \set_error_handler(function($errno, $errstring, $errfile, $errline) use (&$errorPrinted, &$getlog) {
//        try {
//            $time = (new \DateTime())->format('[Y-m-d H:i:s e]');
//            $main = "Unhandled error ($errno): $errstring in $errfile:$errline\n";
//            \file_put_contents($getlog(), "$time $main", FILE_APPEND);
//            if (\Context::getInstance()->isMode(\Context::MODE_PRODUCTION)) {
//                if ($errorPrinted===true){return;}
//                $errorPrinted = true;
//                echo "UNHANDLED ERROR. THIS IS A PRODUCTION ENVIRONMENT. NO MORE DETAILS ARE AVAILABLE.<br>";
//            }
//            else {
//                echo "<section><h1>$main</h1></section>";
//            }
//        } catch (\Throwable $ignored) {
//            if ($errorPrinted===true){return;}
//            $errorPrinted = true;
//            echo "UNHANDLED ERROR. THIS IS A PRODUCTION ENVIRONMENT. NO MORE DETAILS ARE AVAILABLE.<br>";
//        } finally {
//            return true;
//        }
//    });
//    \set_exception_handler(function($throwable) use (&$errorPrinted, &$getlog) {
//        try {
//            $time = (new \DateTime())->format('[Y-m-d H:i:s e]');
//            \file_put_contents($getlog(), "$time $throwable". "\n", FILE_APPEND);
//            if (\Context::getInstance()->isMode(\Context::MODE_PRODUCTION)) {
//                if ($errorPrinted===true){return;}
//                $errorPrinted = true;
//                echo "UNHANDLED ERROR. THIS IS A PRODUCTION ENVIRONMENT. NO MORE DETAILS ARE AVAILABLE.<br>";
//            }
//            else {
//                while ($throwable !== null) {
//                    $message = $throwable->getMessage();
//                    $message = empty($message) ? 'No message available' : $message;
//                    $file = $throwable->getFile();
//                    $line = $throwable->getLine();
//                    $trace = $throwable->getTraceAsString();
//                    echo "<details><summary>Unhandled exception: $message in $file:$line</summary>";
//                    echo "<pre>$trace</pre>";
//                    echo "</details>";
//                    $throwable = $throwable->getPrevious();
//                }
//            }
//        } catch (\Throwable $ignored) {
//            if ($errorPrinted===true){return;}
//            $errorPrinted = true;
//            echo "UNHANDLED ERROR. THIS IS A PRODUCTION ENVIRONMENT. NO MORE DETAILS ARE AVAILABLE.<br>";
//        }
//    });
//    \ini_set('log_errors ', 'on');
//    \ini_set('error_log', $getlog());
//});

use Kint;
use Throwable;
use Exception;

 require_once './bootstrap.php';

/* Use this for quickly testing some php code... */

\error_log('asd');
echo $undefinedVariable;

function a(){
    throw new \Exception();
};
a();



die();


Kint::enabled(true);
Kint::dump($_SERVER);
echo __FILE__;