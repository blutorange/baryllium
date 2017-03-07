<?php
// bootstrap.php
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

require_once "vendor/autoload.php";

// Create a simple "default" Doctrine ORM configuration for Annotations
$isDevMode = false;
$config = Setup::createAnnotationMetadataConfiguration(array(__DIR__."/src"), $isDevMode);
// or if you prefer yaml or XML
//$config = Setup::createXMLMetadataConfiguration(array(__DIR__."/config/xml"), $isDevMode);
//$config = Setup::createYAMLMetadataConfiguration(array(__DIR__."/config/yaml"), $isDevMode);

// database configuration parameters
$dbParams = array(
    'dbname' => 'baryllium',
    'user' => 'baryllium',
    'password' => 'baryllium',
    'host' => 'localhost',
    'driver' => 'pdo_mysql'
    //'path' => __DIR__ . '/db.sqlite',
);


// obtaining the entity manager
$entityManager = EntityManager::create($dbParams, $config);
