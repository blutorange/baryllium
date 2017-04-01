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
use Moose\Context\MailerProviderInterface;
use Moose\Context\TemplateEngineProviderInterface;
use Nette\Mail\IMailer;
use Nette\Mail\SendmailMailer;
use Nette\Mail\SmtpMailer;
use PlatesExtension\MainExtension;
use Symfony\Component\Yaml\Yaml;

class Context extends Singleton implements EntityManagerProviderInterface, TemplateEngineProviderInterface, MailerProviderInterface {
    const MODE_PRODUCTION = 'production';
    const MODE_DEVELOPMENT = 'development';
    const MODE_TESTING = 'testing';

    /** @var bool */
    private static $configured;
    
    /** @var string */
    private static $fileRoot;

    /** @var Engine */
    private $engine;
    
    /** @var EntityManager[] */
    private $entityManagers;
    
    /** @var IMailer */
    private $mailer;

    /** @var string */
    private $contextPath;
       
    /** @var array */
    private $phinx;
    
    /** @var Key */
    private $secretKey;
    
    /** @var PortalSessionHandler */
    private $sessionHandler;
       
    /** @var string */
    private $mode;
    
    /** @var array */
    private $environment;
    
    /** @var string */
    private $logfile;

    public function __construct() {      
        if (!self::$configured) {
            \error_log('Context was not configured yet.');
            self::configureInstance();
        }
        $this->entityManagers = [];
        self::$instance = $this;
    }
    
    public static function configureInstance(string $fileRoot = null) {
        if (self::$configured) {
            \error_log('Context instance is already configured.');
            return;
        }
        $fr = $fileRoot ?? \dirname(__FILE__, 3);
        self::$fileRoot = self::assertFileRoot($fr);
        self::$configured = true;
    }

    public function getSessionHandler(): PortalSessionHandler {
        if ($this->sessionHandler === null) {
            $this->sessionHandler = new PortalSessionHandler();            
        }
        return $this->sessionHandler;
    }
    
    public function getServerPath(string $relativePath = ''): string {
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
        if ($this->engine == null) {
            self::makeEngine();
        }
        return $this->engine;
    }

    public function getEm(int $i = 0): EntityManagerInterface {
        if (!\array_key_exists($i, $this->entityManagers)) {
            $this->entityManagers[$i] = self::makeEm();
        }
        return $this->entityManagers[$i];
    }
    
    public function getMailer(): IMailer {
        if ($this->mailer === null) {
            $this->mailer = self::makeMailer();
        }
        return $this->mailer;
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
            foreach ($this->entityManagers as $em) {
                $consumer($em);
            }
        }
        else {
            if (\array_key_exists($i, $this->entityManagers)) {
                $consumer($this->entityManagers[$i]);
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
        $dbConf = self::getEnvironment();
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
                !self::isMode(self::MODE_PRODUCTION));

        // Obtaining the entity manager
        $entityManager = EntityManager::create($dbParams, $config);
        return $entityManager;
    }

    private function makeEngine() {
        // Create new Plates instance
        $this->engine = new Engine(self::getFilePath('private/php/view/templates/'));
        $this->engine->loadExtension(new MainExtension($this));
    }

    private function getPhinx() : array {
        if ($this->phinx === null) {
            $path = self::getFilePath('private/config/phinx.yml');
            $raw = \file_exists($path) ? \file_get_contents($path) : false;
            if ($raw === false) {
                $raw = '';
            }
            try {
                $phinx = Yaml::parse($raw);
            }
            catch (\Symfony\Component\Yaml\Exception\ParseException $ignored) {
                $phinx = [];
            }
            if (!is_array($phinx)) {
                $phinx = [];
            }
            
            if (\array_key_exists('private_key', $phinx)) {
                $secretKey = $phinx['private_key'];
                unset($phinx['private_key']);
                $this->secretKey = Key::loadFromAsciiSafeString($secretKey);
            }
          
            $this->phinx = &$phinx;
        }
        return $this->phinx ;
    }

    public function getLogFile() : string {
        if ($this->logfile === null) {
            $env = self::getEnvironment();
            if (\array_key_exists('logfile', $env)) {
                $this->logfile = $env['logfile'];
            }
            else {
                $this->logfile = __DIR__ . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'baryllium.error.log';
            }
        }
        return $this->logfile;
    }
    
    public function getMode(): string {
        if ($this->mode === null) {
            $phinx = self::getPhinx();
            if (!\array_key_exists('environments', $phinx) || !\array_key_exists('default_database', $phinx['environments'])) {
                $mode = self::MODE_PRODUCTION;
            }
            else {
                $mode = $phinx['environments']['default_database'];
            }
            if ($mode !== self::MODE_DEVELOPMENT && $mode !== self::MODE_PRODUCTION && $mode !== self::MODE_TESTING) {
                $mode = self::MODE_PRODUCTION;
            }
            $this->mode = $mode;
        }
        return $this->mode;
    }
    
    public function isMode(string $mode) : bool {
        return self::getMode() === $mode;
    }

    private function getServerRoot() : string {
        if ($this->contextPath === null) {
            $phinx = self::getPhinx();
            if (!\array_key_exists('paths', $phinx) || !\array_key_exists('context', $phinx['paths'])) {
                throw new Exception('No context path specified, please see private/config/phinx.yml');
            }
            $contextPath = self::getPhinx()['paths']['context'];
            if ($contextPath === null) {
                \error_log('No context path specified, please see private/config/phinx.yml');
                $contextPath = '';
            }
            if ($contextPath == '/') {
                $contextPath = '';
            }
            else if (!empty($contextPath) && \substr($contextPath, 0, 1) !== '/') {
                $contextPath = '/' . $contextPath;
            }
            $this->contextPath = $contextPath;
        }
        return $this->contextPath;
    }
    
    public function getPrivateKey() {
        if ($this->secretKey === null) {
            self::getPhinx();
        }
        return $this->secretKey;
    }
    
    public function getSystemMailAddress() : string {
        $phinx = self::getPhinx();
        if (!\array_key_exists('system_mail_address', $phinx)) {
            \error_log('System mail address not specified, please see private/config/phinx.yml');
            return 'sender@example.com';            
        }
        return $phinx['system_mail_address'];
    }
    
    private function getEnvironment() {
        if ($this->environment === null) {
            $phinx = self::getPhinx();
            $mode = self::getMode();
            if (!\array_key_exists('environments', $phinx) || !\array_key_exists($mode, $phinx['environments'])) {
                $this->environment = [];
            }
            else {
                $this->environment = $phinx['environments'][$mode];
            }
        }
        return $this->environment;
    }
    
    private function makeMailer() : IMailer {
        $mailConf = self::getPhinx()['environments'][self::getMode()];
        $type = mb_convert_case(\trim($mailConf['mail']), MB_CASE_LOWER);
        if ($type !== 'smtp') {
            return new SendmailMailer();
        }
        $smtp = $mailConf['smtp'];
        $bindto = \array_key_exists('bindto', $smtp) ? $smtp['bindto'] : '0';
        $secure = \array_key_exists('secure', $smtp) ? !!$smtp['secure'] : true;
        $secure = $secure ? 'ssl' : 'tls';
        $port = \array_key_exists('port', $smtp) ? \intval($smtp['port']) : 0;
        $timeout = \array_key_exists('timeout', $smtp) ? \intval($smtp['timeout']) : 0;
        $options = [
            'host' => $smtp['host'],
            'username' => $smtp['user'],
            'password' => $smtp['pass'],
            'secure' => $secure,
            'timeout' => $timeout > 0 ? $timeout : 20,
            'port' => $port > 0 ? $port : ($secure ? 465 : 25),
        ];
        if (\array_key_exists('persistent', $smtp) && $smtp['persistent']) {
            $options['persistent'] = true;
        }
        if (!empty($bindto) && $bindto !== '0') {
            $options['context'] = [
                'socket' => [
                    'bindto' => $smtp['bindto']
                ]
            ];
        } 
        return new SmtpMailer($options);
    }
}