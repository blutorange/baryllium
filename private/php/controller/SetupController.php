<?php

/* Note: This license has also been called the "New BSD License" or "Modified
 * BSD License". See also the 2-clause BSD License.
 * 
 * Copyright 2015 The Moose Team
 * 
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 * 
 * 1. Redistributions of source code must retain the above copyright notice,
 * this list of conditions and the following disclaimer.
 * 
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 * this list of conditions and the following disclaimer in the documentation
 * and/or other materials provided with the distribution.
 * 
 * 3. Neither the name of the copyright holder nor the names of its contributors
 * may be used to endorse or promote products derived from this software without
 * specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

namespace Controller;

use Controller\AbstractController;
use Dao\AbstractDao;
use Defuse\Crypto\Key;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\Setup;
use Entity\ScheduledEvent;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Yaml\Yaml;
use Throwable;
use Ui\PlaceholderTranslator;

class SetupController extends AbstractController {

    public function doGet(HttpResponseInterface $response, HttpRequestInterface $request) {
        if (file_exists($this->getPhinxPath())) {
            $this->renderTemplate('t_setup_redirect_user');
            return;
        }

        echo $this->renderTemplate('t_setup', ['action' => $_SERVER['PHP_SELF']]);
    }

    public function doPost(HttpResponseInterface $response, HttpRequestInterface $request) {
        if (file_exists($this->getPhinxPath())) {
            $this->renderTemplate('t_setup_redirect_user');
            return;
        }

        $port = $request->getParamInt('port', 3306);
        $host = $request->getParam('host');
        $dbname = $request->getParam('dbname');
        $dbnameTest = $request->getParam('dbnameTest');
        $dbnameDev = $request->getParam('dbnameDev');
        $user = $request->getParam('user');
        $pass = $request->getParam('pass');
        $collation = $request->getParam('collation');
        $encoding = $request->getParam('encoding');
        $driver = $this->getDriver($request);
        $systemMail = $request->getParam('sysmail') ?? 'admin@example.com';

        try {
            $em = $this->initDb($dbname, $user, $pass, $host, $port, $driver,
                    $collation, $encoding);
        }
        catch (Throwable $e) {
            \error_log("Failed to init db: $e");
            $this->renderTemplate('t_setup',
                    ['message' => "Failed to initialize DB schema: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine(),
                'detail' => $e->getTraceAsString(), 'action' => $_SERVER['PHP_SELF']]);
            return;
        }

        try {
            $this->writeConfigFile($systemMail, $dbname, $user, $pass, $host, $port, $driver,
                    $collation, $encoding, $dbnameDev, $dbnameTest);
        }
        catch (Throwable $e) {
            \error_log("Failed to write config file: $e");
            $this->renderTemplate('t_setup',
                    ['message' => "Failed to write config file: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine(),
                'detail' => $e->getTraceAsString(), 'action' => $_SERVER['PHP_SELF']]);
            return;
        }
        
        try {
            $this->prepareDb($em);
        }
        catch (Throwable $e) {
            \error_log("Failed to prepare db: $e");
            $this->renderTemplate('t_setup',
                    ['message' => "Failed to prepare database: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine(),
                'detail' => $e->getTraceAsString(), 'action' => $_SERVER['PHP_SELF']]);
            return;
        }

        $this->renderTemplate('t_setup_redirect_user');
    }

    // TODO
    private function getDriver(HttpRequestInterface $request): array {
        $driver = $request->getParam('driver');
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
            $collation, $encoding) : EntityManager {
        $dbParams = array(
            'dbname'               => $dbname,
            'user'                 => $user,
            'password'             => $pass,
            'host'                 => $host,
            'port'                 => $port,
            'driver'               => $driver[1],
            'collation-server'     => $collation,
            'character-set-server' => $encoding,
            'charset'              => $encoding
        );

        $pathToEntities = dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'entity';
        $config = Setup::createAnnotationMetadataConfiguration(array($pathToEntities),
                        false);

        $em = EntityManager::create($dbParams, $config);
        $tool = new SchemaTool($em);
        $tool->dropDatabase();
        $metas = $em->getMetadataFactory()->getAllMetadata();
        $tool->updateSchema($metas);
        
        return $em;
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
        // Dirname may add backslashes, especially when going to the top-level path.
        $contextPath = preg_replace('/\\\\/u', '/', $contextPath);
        $secretKey = Key::createNewRandomKey()->saveToAsciiSafeString();
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
            'private_key' => $secretKey ,
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
    
    protected function getRequiresLogin() : int {
        return self::REQUIRE_LOGIN_NEVER;
    }

    protected function prepareDb(EntityManager $em) {
        $translator = new PlaceholderTranslator('en');
        
        $expireTokenClean = new ScheduledEvent();
        $expireTokenClean->setCategory(ScheduledEvent::CATEGORY_CLEANUP);
        $expireTokenClean->setSubCategory(ScheduledEvent::SUBCATEGORY_CLEANUP_EXPIRETOKEN);
        $expireTokenClean->setName('Clean up - purge expire token');
        $expireTokenClean->setIsActive(true);
        $errors = AbstractDao::expireToken($em)->persist($expireTokenClean, $translator);
        if (sizeof($errors) > 0) {
            throw new \Exception("Failed to persist expire token clean task: $errors[0]");
        }
        
        $em->flush();
    }
}