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

use Defuse\Crypto\Key;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use League\Plates\Engine;
use PlatesExtension\MainExtension;
use Symfony\Component\Yaml\Yaml;

class Context {
    public static $MODE_PRODUCTION = 'production';
    public static $MODE_DEVELOPMENT = 'development';
    public static $MODE_TESTING = 'testing';

    /** @var Engine */
    private $engine;
    /** @var EntityManager */
    private $entityManager;
    private $contextPath;
    private $fileRoot;
    private $phinx;
    private $secretKey;
    
    /** @var PortalSessionHandler */
    private $sessionHandler;

    public function __construct(string $fileRoot = null) {
        $fr = $fileRoot ?? dirname(__FILE__, 3);
        $this->fileRoot = self::assertFileRoot($fr);
    }

    public function getSessionHandler(): PortalSessionHandler {
        if ($this->sessionHandler === null) {
            $this->sessionHandler = new PortalSessionHandler($this);            
        }
        return $this->sessionHandler;
    }
    
    public function getServerPath(string $relativePath): string {
        return $this->getServerRoot() . '/' . ($relativePath !== null ? $relativePath : '');
    }
    
    public function getFilePath(string $relativePath): string {
        $path = $this->fileRoot . DIRECTORY_SEPARATOR . ($relativePath !== null ? $relativePath : '');
        
        if (($real = realpath($path)) === false) {
            return $this->fileRoot . DIRECTORY_SEPARATOR . 'FORBIDDEN';
        }
        if (mb_strpos($real, $this->fileRoot) !== 0) {
            return $this->fileRoot . DIRECTORY_SEPARATOR . 'FORBIDDEN';
        }
        return $real;
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
            'charset' => $dbConf['charset'],
            'collation-server' => $dbConf['collation'],
            'character-set-server' => $dbConf['charset']
        );
        
        // Create a simple "default" Doctrine ORM configuration for Annotations
        $config = Setup::createAnnotationMetadataConfiguration(
                array($this->getFilePath("/private/php/entity")),
                $this->getMode() != self::$MODE_PRODUCTION);

        // Obtaining the entity manager
        $this->entityManager = EntityManager::create($dbParams, $config);
        
        $config = new \Doctrine\ORM\Configuration();
    }

    private function makeEngine() {
        // Create new Plates instance
        $this->engine = new Engine($this->getFilePath('private/php/view/templates/'));
        $this->engine->loadExtension(new MainExtension($this));
    }

    private function getPhinx() : array {
        if ($this->phinx === NULL) {
            $phinx = Yaml::parse(file_get_contents($this->getFilePath('private/config/phinx.yml')));
            $secretKey = $phinx['private_key'];
            $phinx['private_key'] = null;
            $this->secretKey = Key::loadFromAsciiSafeString($secretKey);
            $this->phinx = $phinx;
        }
        return $this->phinx ;
    }

    public function getMode(): string {
        return $this->getPhinx()['environments']['default_database'];
    }
    
    public function isMode(string $mode) : bool {
        return $this->getMode() === $mode;
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
    
    public function getPrivateKey() {
        if ($this->secretKey === null) {
            $this->getPhinx();
        }
        return $this->secretKey;
    }
    
    public function getSystemMailAddress() : string {
        $mail = $this->getPhinx()['system_mail_address'];
        if ($mail !== null) {
            return $mail;
        }
        error_log('System mail address not specified, please see private/config/phinx.yml');
        return 'sender@example.com';
    }

    public function isEmInitialized() : bool {
        return $this->entityManager !== null;
    }
}