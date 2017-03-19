<?php

use Composer\Autoload\ClassLoader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\DBAL\Types\EncryptedStringType;
use Doctrine\DBAL\Types\Type;

/** @var ClassLoader $loader */
$loader = require(dirname(__FILE__, 2) . '/' . 'vendor/autoload.php');
AnnotationRegistry::registerLoader([$loader, 'loadClass']);

Type::addType(EncryptedStringType::TPYE_NAME, EncryptedStringType::class);

$context = new Context(dirname(__FILE__, 2));
$GLOBALS['context'] = $context;