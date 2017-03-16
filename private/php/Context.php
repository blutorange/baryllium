<?php

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Yaml\Yaml;
use League\Plates\Engine;
use PlatesExtension\MainExtension;

class Context {

    public static $MODE_PRODUCTION = 'production';
    public static $MODE_DEVELOPMENT = 'development';
    public static $MODE_TESTING = 'testing';
    private $engine;
    private $entityManager;
    private $contextPath;
    private $phinx;

    public function __construct($fr) {
        $this->fileRoot = self::assertFileRoot($fr);
    }

    public function getServerPath(string $relativePath): string {
        return $this->getServerRoot() . '/' . ($relativePath !== null ? $relativePath : '');
    }
    
    public function getFilePath(string $relativePath): string {
        return $this->fileRoot . DIRECTORY_SEPARATOR . ($relativePath !== null ? $relativePath : '');
    }

    public function getEngine(): Engine {
        if ($this->engine == null) {
            $this->makeEngine();
        }
        return $this->engine;
    }

    public function getEm(): EntityManager {
        if ($this->entityManager == null) {
            $this->makeEm();
        }
        return $this->entityManager;
    }
    
    public function closeEm() {
        if ($this->entityManager !== null) {
            $this->entityManager->flush();
            $this->entityManager->close();
        }
    }

    private static function assertFileRoot($dir) : string{
        if ($dir == null) {
            error_log('Server root is null.');
            return '/';
        }
        if (empty($dir)) {
            return '/';
        }
        if (file_exists($dir)) {
            return $dir;
        } else {
            error_log('Server root path ' . $dir . 'does not exist on the file system.');
            return '/';
        }
    }

    private function makeEm() {
        // Get database configuration for the current mode.
        $dbConf = $this->getPhinx()['environments'][$this->getMode()];
        $dbParams = array(
            'dbname' => $dbConf['name'],
            'user' => $dbConf['user'],
            'password' => $dbConf['pass'],
            'host' => $dbConf['host'],
            'port' => $dbConf['port'],
            'driver' => $dbConf['driver'],
            'collation-server' => $dbConf['collation'],
            'character-set-server' => $dbConf['charset']
        );
        
        // Create a simple "default" Doctrine ORM configuration for Annotations
        $config = Setup::createAnnotationMetadataConfiguration(
                array($this->getFilePath("/private/php/entity")),
                $this->getMode() != self::$MODE_PRODUCTION);

        // Obtaining the entity manager
        $this->entityManager = EntityManager::create($dbParams, $config);
    }

    private function makeEngine() {
        // Create new Plates instance
        $this->engine = new League\Plates\Engine($this->getFilePath('private/php/view/templates/'));
        $this->engine->loadExtension(new MainExtension($this));
    }

    private function getPhinx() : array {
        if ($this->phinx === NULL) {
            $this->phinx = Yaml::parse(file_get_contents($this->getFilePath('private/config/phinx.yml')));
        }
        return $this->phinx ;
    }

    public function getMode(): string {
        return $this->getPhinx()['environments']['default_database'];
    }
    
    private function getServerRoot() : string {
        if ($this->contextPath !== null) {
            return $this->contextPath;
        }
        $this->contextPath = $this->getPhinx()['paths']['context'];
        if ($this->contextPath === null) {
            error_log('No context path specified, please see private/config/phinx.yml');
            $this->contextPath = '';
        }
        if ($this->contextPath == '/') {
            $this->contextPath = '';
        }
        else if (!empty($this->contextPath) && substr($this->contextPath, 0, 1) !== '/') {
            $this->contextPath = '/' . $this->contextPath;
        }
        return $this->contextPath;
    }
    
    public function getSystemMailAddress() : string {
        $mail = $this->getPhinx()['system_mail_address'];
        if ($mail !== null) {
            return $mail;
        }
        error_log('System mail address not specified, please see private/config/phinx.yml');
        return 'sender@example.com';
    }
}