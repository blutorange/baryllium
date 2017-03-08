<?php
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Yaml\Yaml;
use League\Plates\Engine;

class Context {
    private $engine;
    private $entityManager;
    private $serverRoot;
    private $isDevMode;

    public function __construct($sr, $fr, bool $isDevMode) {
        $this->isDevMode = $isDevMode;
        $this->retrieveServerRoot($sr);
        $this->retrieveFileRoot($fr);
    }
 
    public function getServerPath(string $relativePath) : string {
        return $this->serverRoot . '/' . ($relativePath !== null ? $relativePath : '');
    }
    
    public function getFilePath(string $relativePath) : string {
        return $this->fileRoot . DIRECTORY_SEPARATOR . ($relativePath !== null ? $relativePath : '');
    }
    
    public function getEngine() : Engine {
        if ($this->engine == null)
            $this->makeEngine();
        return $this->engine;
    }
    
    public function getEm() : EntityManager {
        if ($this->entityManager == null)
            $this->makeEm();
        return $this->entityManager;
    }
    
    private function retrieveServerRoot($sr) {
        if ($sr == null) {
            $this->serverRoot = '/';
            return;
        }
        if (empty($sr)) {
            $this->serverRoot = '/';
        }
        else {
            $this->serverRoot = filter_input(INPUT_SERVER, dirname($sr), FILTER_VALIDATE_URL);
        }
    }
    
    private function retrieveFileRoot($fr) {
        if ($fr == null) {
            $this->serverRoot = '/';
            return;
        }
        if (empty($fr)) {
            $this->fileRoot = '/';
            return;
        }
        $dir = dirname($fr);
        if (file_exists($dir)) {
            $this->fileRoot = $dir;
        }
        else {
            // Log an error.
            $this->serverRoot = '/';
        }
    }

    private function makeEm() {
       // Create a simple "default" Doctrine ORM configuration for Annotations
       $isDevMode = false;
       $config = Setup::createAnnotationMetadataConfiguration(array($this->getFilePath("/php/entity")), $this->isDevMode);

       $phinx = Yaml::parse(file_get_contents($this->getFilePath('config/phinx.yml')));
       $defaultDB = $phinx['environments']['default_database'];
       $dbConf= $phinx['environments'][$defaultDB];

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
    
    private function  makeEngine() {
        // Create new Plates instance
        $this->engine = new League\Plates\Engine($this->getFilePath('view/templates/'));
    }
}