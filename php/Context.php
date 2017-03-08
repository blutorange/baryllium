<?php
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Yaml\Yaml;
use League\Plates\Engine;

class Context {
    private $engine;
    private $entityManager;
    public function __construct(string $root) {
        $this->makeEm($root);
        $this->makeEngine($root);
    }
    public function getEngine() : Engine {
        return $this->engine;
    }
    public function getEm() : EntityManager {
        return $this->entityManager;
    }
    private function makeEm(string $root) {
       // Create a simple "default" Doctrine ORM configuration for Annotations
       $isDevMode = false;
       $config = Setup::createAnnotationMetadataConfiguration(array($root . "/php/entity"), $isDevMode);

       $phinx = Yaml::parse(file_get_contents($root . '/config/phinx.yml'));
       $defaultDB = $phinx['environments']['default_database'];
       $dbConf= $phinx['environments'][$defaultDB];

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

       // Obtaining the entity manager
       $this->entityManager = EntityManager::create($dbParams, $config);       
    }
    private function  makeEngine(string $root) {
        // Create new Plates instance
        $this->engine = new League\Plates\Engine($root . '/view/templates/');
    }
}