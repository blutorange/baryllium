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

use Crunz\Singleton;
use Defuse\Crypto\Key;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Setup;
use League\Plates\Engine;
use Moose\Context\EntityManagerProviderInterface;
use Moose\Context\TemplateEngineProviderInterface;
use PlatesExtension\MainExtension;
use Symfony\Component\Yaml\Yaml;

class Context extends Singleton implements EntityManagerProviderInterface, TemplateEngineProviderInterface {
    public static $MODE_PRODUCTION = 'production';
    public static $MODE_DEVELOPMENT = 'development';
    public static $MODE_TESTING = 'testing';

    /** @var Engine */
    private static $engine;
    
    /** @var EntityManager[] */
    private static $entityManagers;
    
    /** @var string */
    private static $contextPath;
    
    /** @var string */
    private static $fileRoot;
    
    /** @var array */
    private static $phinx;
    
    /** @var Key */
    private static $secretKey;
    
    /** @var PortalSessionHandler */
    private static $sessionHandler;
    
    /** @var bool */
    private static $configured;

    public function __construct() {
        if (!self::$configured) {
            \error_log('Context was not configured yet.');
            self::configureInstance();
        }
    }
    
    public static function configureInstance(string $fileRoot = null) {
        if (self::$configured) {
            \error_log('Context instance is already configured.');
            return;
        }
        $fr = $fileRoot ?? \dirname(__FILE__, 3);
        self::$fileRoot = self::assertFileRoot($fr);
        self::$entityManagers = [];
        self::$configured = true;
    }

    public function getSessionHandler(): PortalSessionHandler {
        if (self::$sessionHandler === null) {
            self::$sessionHandler = new PortalSessionHandler();            
        }
        return self::$sessionHandler;
    }
    
    public function getServerPath(string $relativePath): string {
        return self::getServerRoot() . '/' . ($relativePath !== null ? $relativePath : '');
    }
    
    public function getFilePath(string $relativePath): string {
        $path = self::$fileRoot . DIRECTORY_SEPARATOR . ($relativePath !== null ? $relativePath : '');
        
        if (($real = \realpath($path)) === false) {
            return self::$fileRoot . DIRECTORY_SEPARATOR . 'FORBIDDEN';
        }
        if (mb_strpos($real, self::$fileRoot) !== 0) {
            return self::$fileRoot . DIRECTORY_SEPARATOR . 'FORBIDDEN';
        }
        return $real;
    }

    public function getEngine(): Engine {
        if (self::$engine == null) {
            self::makeEngine();
        }
        return self::$engine;
    }

    public function getEm(int $i = 0): EntityManagerInterface {
        if (!\array_key_exists($i, self::$entityManagers)) {
            self::$entityManagers[$i] = self::makeEm();
        }
        return self::$entityManagers[$i];
    }
    
    public function closeEm($i = null) {
        self::withEm($i, function(EntityManager $em){
            if ($em->isOpen()) {
                $em->flush();
                $em->close();
            }
        });
    }
    
    public function rollbackEm($i = null) {
        self::withEm($i, function(EntityManager $em) {
            if ($em->isOpen() && self::getEm()->getConnection()->isTransactionActive()) {
                self::getEm()->rollback();
            }
        });
    }
    
    public function withEm(int $i = null, Closure $consumer = null) {
        $consumer = $consumer ?? function($em){};
        if ($i === null) {
            foreach (self::$entityManagers as $em) {
                $consumer($em);
            }
        }
        else {
            if (\array_key_exists($i, self::$entityManagers)) {
                $consumer(self::$entityManagers[$i]);
            }
        }
    }

    private static function assertFileRoot($dir) : string{
        if ($dir == null) {
            \error_log('Server root is null.');
            return '/';
        }
        if (empty($dir)) {
            return '/';
        }
        if (\file_exists($dir)) {
            return $dir;
        } else {
            \error_log('Server root path ' . $dir . 'does not exist on the file system.');
            return '/';
        }
    }

    private function makeEm() : EntityManager {
        // Get database configuration for the current mode.
        $dbConf = self::getPhinx()['environments'][self::getMode()];
        $dbParams = array(
            'dbname' => $dbConf['name'],
            'user' => $dbConf['user'],
            'password' => $dbConf['pass'],
            'host' => $dbConf['host'],
            'port' => $dbConf['port'],
            'driver' => $dbConf['driver'],
            'charset' => $dbConf['charset'],
            'collation-server' => $dbConf['collation'],
            'character-set-server' => $dbConf['charset']
        );        
        // Create a simple "default" Doctrine ORM configuration for Annotations
        $config = Setup::createAnnotationMetadataConfiguration(
                array(self::getFilePath("/private/php/entity")),
                !self::isMode(self::$MODE_PRODUCTION));

        // Obtaining the entity manager
        $entityManager = EntityManager::create($dbParams, $config);
        return $entityManager;
    }

    private function makeEngine() {
        // Create new Plates instance
        self::$engine = new Engine(self::getFilePath('private/php/view/templates/'));
        self::$engine->loadExtension(new MainExtension($this));
    }

    private function getPhinx() : array {
        if (self::$phinx === null) {
            $phinx = Yaml::parse(\file_get_contents(self::getFilePath('private/config/phinx.yml')));
            $secretKey = $phinx['private_key'];
            $phinx['private_key'] = null;
            self::$secretKey = Key::loadFromAsciiSafeString($secretKey);
            self::$phinx = $phinx;
        }
        return self::$phinx ;
    }

    public function getMode(): string {
        return self::getPhinx()['environments']['default_database'];
    }
    
    public function isMode(string $mode) : bool {
        return self::getMode() === $mode;
    }

    private function getServerRoot() : string {
        if (self::$contextPath !== null) {
            return self::$contextPath;
        }
        self::$contextPath = self::getPhinx()['paths']['context'];
        if (self::$contextPath === null) {
            \error_log('No context path specified, please see private/config/phinx.yml');
            self::$contextPath = '';
        }
        if (self::$contextPath == '/') {
            self::$contextPath = '';
        }
        else if (!empty(self::$contextPath) && \substr(self::$contextPath, 0, 1) !== '/') {
            self::$contextPath = '/' . self::$contextPath;
        }
        return self::$contextPath;
    }
    
    public function getPrivateKey() {
        if (self::$secretKey === null) {
            self::getPhinx();
        }
        return self::$secretKey;
    }
    
    public function getSystemMailAddress() : string {
        $mail = self::getPhinx()['system_mail_address'];
        if ($mail !== null) {
            return $mail;
        }
        \error_log('System mail address not specified, please see private/config/phinx.yml');
        return 'sender@example.com';
    }
}