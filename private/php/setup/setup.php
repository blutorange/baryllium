<?php

namespace Controller;

use Controller\AbstractController;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\Setup;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Yaml\Yaml;
use Throwable;

require_once '../../bootstrap.php';

class SetupController extends AbstractController {

    public function doGet() {
        if (file_exists($this->getPhinxPath())) {
            $this->renderTemplate('t_setup_redirect_user');
            return;
        }

        echo $this->renderTemplate('t_setup', ['action' => $_SERVER['PHP_SELF']]);
    }

    public function doPost() {
        if (file_exists($this->getPhinxPath())) {
            $this->renderTemplate('t_setup_redirect_user');
            return;
        }

        $port = $this->getParamInteger('port') ?? 3306;
        $host = $this->getParam('host');
        $dbname = $this->getParam('dbname');
        $dbnameTest = $this->getParam('dbnameTest');
        $dbnameDev = $this->getParam('dbnameDev');
        $user = $this->getParam('user');
        $pass = $this->getParam('pass');
        $collation = $this->getParam('collation');
        $encoding = $this->getParam('encoding');
        $driver = $this->getDriver();
        $systemMail = $this->getParam('sysmail') ?? 'admin@example.com';

        try {
            $this->initDb($dbname, $user, $pass, $host, $port, $driver,
                    $collation, $encoding);
        }
        catch (Throwable $e) {
            echo $this->renderTemplate('t_setup',
                    ['message' => "Failed to initialize DB schema: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine(),
                'detail' => $e->getTraceAsString(), 'action' => $_SERVER['PHP_SELF']]);
            return;
        }

        try {
            $this->writeConfigFile($systemMail, $dbname, $user, $pass, $host, $port, $driver,
                    $collation, $encoding, $dbnameDev, $dbnameTest);
        }
        catch (Throwable $e) {
            echo $this->renderTemplate('t_setup',
                    ['message' => "Failed to write config file: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine(),
                'detail' => $e->getTraceAsString(), 'action' => $_SERVER['PHP_SELF']]);
            return;
        }

        echo $this->renderTemplate('t_setup_redirect_user');
    }

    // TODO
    private function getDriver(): array {
        $driver = $this->getParam('driver');
        switch ($driver) {
            case 'mysql':
                return ['mysql', 'pdo_mysql'];
            case 'oracle':
                return ['oracle', 'pdo_oci8'];
            case 'sqlite':
                return ['sqlite', 'pdo_sqlite'];
            case 'sqlserver':
                return ['sqlsrv', 'sqlsrv'];
            default:
                return ['', ''];
        }
    }

    private function initDb($dbname, $user, $pass, $host, $port, $driver,
            $collation, $encoding) {
        $dbParams = array(
            'dbname'               => $dbname,
            'user'                 => $user,
            'password'             => $pass,
            'host'                 => $host,
            'port'                 => $port,
            'driver'               => $driver[1],
            'collation-server'     => $collation,
            'character-set-server' => $encoding
        );

        $pathToEntities = dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'entity';
        $config = Setup::createAnnotationMetadataConfiguration(array($pathToEntities),
                        false);

        $em = EntityManager::create($dbParams, $config);
        $tool = new SchemaTool($em);
        $tool->dropDatabase();
        $metas = $em->getMetadataFactory()->getAllMetadata();
        $tool->updateSchema($metas);
    }

    private function writeConfigFile($systemMail, $dbname, $user, $pass, $host, $port,
            $driver, $collation, $encoding, $dbnameDev, $dbnameTest) {
        $path = $this->getPhinxPath();
        $dir = dirname($path);
        if (!file_exists($dir)) {
            if (!mkdir($dir, 0660, true)) {
                throw new IOException("Could not create directory for config file $path");
            }
        }
        $yaml = $this->makeConfig($systemMail, $dbname, $user, $pass, $host, $port, $driver,
                $collation, $encoding, $dbnameDev, $dbnameTest);
        if (file_put_contents($path, Yaml::dump($yaml, 4, 4)) === false) {
            throw new IOException("Could not create directory for config file $path: ");
        }
    }

    private function getPhinxPath() {
        return dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'config/phinx.yml';
    }

    private function makeConfig($systemMail, $dbname, $user, $pass, $host, $port, $driver,
            $collation, $encoding, $dbNameDev, $dbNameTest) {
        $contextPath = dirname($_SERVER['PHP_SELF'], 4);
        $yaml = array(
            'paths'        => array(
                'migrations' => '%%PHINX_CONFIG_DIR%%/private/db/migrations',
                'seeds'      => '%%PHINX_CONFIG_DIR%%/db/seeds',
                'context'    => $contextPath
            ),
            'environments' => array(
                'default_migration_table' => 'phinxlog',
                'default_database'        => 'production',
                'production'              => array(
                    'adapter'   => $driver[0],
                    'driver'    => $driver[1],
                    'host'      => $host,
                    'name'      => $dbname,
                    'user'      => $user,
                    'pass'      => $pass,
                    'port'      => $port,
                    'charset'   => $encoding,
                    'collation' => $collation
                ),
            ),
            'system_mail_address' => $systemMail,
            'version_order' => 'creation'
        );
        if (!empty($dbNameTest)) {
            $yaml['environments']['testing'] = array(
                'adapter'   => $driver[0],
                'driver'    => $driver[1],
                'host'      => $host,
                'name'      => $dbNameTest,
                'user'      => $user,
                'pass'      => $pass,
                'port'      => $port,
                'charset'   => $encoding,
                'collation' => $collation
            );
        }
        if (!empty($dbNameDev)) {
            $yaml['environments']['development'] = array(
                'adapter'   => $driver[0],
                'driver'    => $driver[1],
                'host'      => $host,
                'name'      => $dbNameDev,
                'user'      => $user,
                'pass'      => $pass,
                'port'      => $port,
                'charset'   => $encoding,
                'collation' => $collation
            );
        }
        return $yaml;
    }

}

$c = new SetupController();
$file = dirname(__FILE__, 4) . DIRECTORY_SEPARATOR . 'FIRST_INSTALL';

if (!file_exists($file)) {
    echo "Create file $file to run the setup guide.";
}
else {
    $c->process(false, false);
}