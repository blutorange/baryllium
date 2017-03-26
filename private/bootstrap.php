<?php

use Composer\Autoload\ClassLoader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\DBAL\Types\EncryptedStringType;
use Doctrine\DBAL\Types\Type;

/* Set internal character encoding to UTF-8 */
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

/** @var ClassLoader $loader */
$loader = require(dirname(__FILE__, 2) . '/' . 'vendor/autoload.php');
AnnotationRegistry::registerLoader([$loader, 'loadClass']);

Type::addType(EncryptedStringType::TPYE_NAME, EncryptedStringType::class);

/** Disable KINT */
Kint::enabled(false);

$context = new Context(dirname(__FILE__, 2));
$GLOBALS['context'] = $context;

return $context;