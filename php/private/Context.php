<?php

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Yaml\Yaml;
use League\Plates\Engine;

class Context {

    public static $MODE_DEFAULT = 0;
    public static $MODE_PRODUCTION = 1;
    public static $MODE_DEVELOP = 2;
    public static $MODE_TEST = 3;
    private $engine;
    private $entityManager;
    private $serverRoot;
    private $mode;

    public function __construct($sr, $fr, int $mode) {
        $this->mode = $mode;
        $this->retrieveServerRoot($sr);
        $this->retrieveFileRoot($fr);
    }

    public function setMode(int $mode) {
        if ($this->entityManager == null) {
            $this->mode = $mode;
        }
        else {
            error_log("Cannot switch mode anymore, entity manager created already.");
        }
    }


    public function getServerPath(string $relativePath): string {
        return $this->serverRoot . '/' . ($relativePath !== null ? $relativePath : '');
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

    private function retrieveServerRoot($sr) {
        if ($sr == null) {
            $this->serverRoot = '/';
            return;
        }
        if (empty($sr)) {
            $this->serverRoot = '/';
        } else {
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
        } else {
            // Log an error.
            $this->serverRoot = '/';
        }
    }

    private function makeEm() {
        // Create a simple "default" Doctrine ORM configuration for Annotations
        $config = Setup::createAnnotationMetadataConfiguration(array($this->getFilePath("/php/private/entity")), $this->mode != self::$MODE_PRODUCTION);

        $phinx = Yaml::parse(file_get_contents($this->getFilePath('config/phinx.yml')));
        $databaseMode = $this->getDatabaseMode($phinx);
        $dbConf = $phinx['environments'][$databaseMode];

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

    private function makeEngine() {
        // Create new Plates instance
        $this->engine = new League\Plates\Engine($this->getFilePath('view/templates/'));
    }

    private function getDatabaseMode(array $phinx): string {
        switch ($this->mode) {
            case self::$MODE_PRODUCTION:
                return "production";
            case self::$MODE_TEST:
                return "testing";
            case self::$MODE_DEFAULT:    
                return $phinx['environments']['default_database'];
            default:
                return "develop";
        }
    }
}