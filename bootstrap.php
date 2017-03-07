<?php
// bootstrap.php
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

require_once "vendor/autoload.php";

// Create a simple "default" Doctrine ORM configuration for Annotations
$isDevMode = false;
$config = Setup::createAnnotationMetadataConfiguration(array(__DIR__."/src"), $isDevMode);

// database configuration parameters
$dbParams = array(
    'dbname' => 'baryllium',
    'user' => 'baryllium',
    'password' => 'baryllium',
    'host' => 'localhost',
    'driver' => 'pdo_mysql'
);


// obtaining the entity manager
$entityManager = EntityManager::create($dbParams, $config);
