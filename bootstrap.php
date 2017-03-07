<?php
// bootstrap.php
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Yaml\Yaml;

require_once "vendor/autoload.php";

// Create a simple "default" Doctrine ORM configuration for Annotations
$isDevMode = false;
$config = Setup::createAnnotationMetadataConfiguration(array(__DIR__."/src"), $isDevMode);


$phinx = Yaml::parse(file_get_contents('config/phinx.yml'));
$defaultDB = $phinx['environments']['default_database'];
$dbConf= $phinx[environments][$defaultDB];

// Database configuration parameters
//$dbParams = array(
//    'dbname' => 'baryllium',
//    'user' => 'baryllium',
//    'password' => 'baryllium',
//    'host' => 'localhost',
//    'driver' => 'pdo_mysql'
//);
$dbParams = array(
    'dbname' => $dbConf['name'],
    'user' => $dbConf['user'],
    'password' => $dbConf['pass'],
    'host' => $dbConf['host'],
    'port' => $dbConf['port'],
    'driver' => 'pdo_' . $dbConf['adapter'],
    'collation-server' => 'utf8_general_ci',
    'character-set-server' => 'utf8'
);

// obtaining the entity manager
$entityManager = EntityManager::create($dbParams, $config);
