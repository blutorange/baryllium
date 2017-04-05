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

namespace Moose\Context;

use Closure;
use Crunz\Singleton;
use Defuse\Crypto\Key;
use Doctrine\Common\Cache\ApcCache;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\CacheProvider;
use Doctrine\Common\Cache\MemcacheCache;
use Doctrine\Common\Cache\RedisCache;
use Doctrine\Common\Cache\XcacheCache;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use League\Plates\Engine;
use Memcache;
use Moose\Context\EntityManagerProviderInterface;
use Moose\Context\MailerProviderInterface;
use Moose\Context\PortalSessionHandler;
use Moose\Context\TemplateEngineProviderInterface;
use Moose\PlatesExtension\PlatesMooseExtension;
use Nette\Mail\IMailer;
use Odan\Asset\PlatesAssetExtension;
use Redis;
use Symfony\Component\Cache\Adapter\DoctrineAdapter;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;
use function mb_strpos;

class Context extends Singleton implements EntityManagerProviderInterface, TemplateEngineProviderInterface, MailerProviderInterface
{
    const MODE_PRODUCTION = 'production';
    const MODE_DEVELOPMENT = 'development';
    const MODE_TESTING = 'testing';

    /** @var bool */
    private static $configured;

    /** @var string */
    private static $fileRoot;
    
    /** @var EntityManagerFactoryInterface */
    private static $emFactory;

    /** @var MailerFactoryInterface */
    private static $mailerFactory;
    
    /** @var PlatesEngineFactoryInterface */
    private static $engineFactory;
    
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
    
    /** @var CacheProvider */
    private $cache;

    public function __construct() {
        if (!self::$configured) {
            \error_log('Context was not configured yet.');
            self::configureInstance();
        }
        $this->entityManagers = [];
        self::$instance = $this;
        $this->makeCache();
    }

    public static function configureInstance(string $fileRoot = null,
            EntityManagerFactoryInterface $emFactory = null,
            PlatesEngineFactoryInterface $engineFactory = null,
            MailerFactoryInterface $mailerFactory = null) {
        if (self::$configured) {
            \error_log('Context instance is already configured.');
            return;
        }
        $fr = $fileRoot ?? \dirname(__FILE__, 3);
        self::$fileRoot = self::assertFileRoot($fr);
        self::$configured = true;
        self::$emFactory = $emFactory ?? new RepositoryEntityManagerFactory();
        self::$mailerFactory = $mailerFactory ?? new NetteMailerFactory();
        self::$engineFactory = $engineFactory ?? new DefaultPlatesEngineFactory();
    }

    public function getSessionHandler(): PortalSessionHandler
    {
        if ($this->sessionHandler === null) {
            $this->sessionHandler = new PortalSessionHandler();
        }
        return $this->sessionHandler;
    }

    public function getServerPath(string $relativePath = ''): string {
        return $this->getServerRoot() . '/' . ($relativePath !== null ? $relativePath : '');
    }
    
    public function getTaskServerPath(string $relativePath = ''): string {
        return $this->getPath('task_server') . $this->getServerPath($relativePath);
    }

    /**
     * Checks whether the path resolves to a path within the project's root
     * directory, otherwise it return a dummy path.
     * @param string $relativePath Relative path to the project's root directory to resolve
     * @return string The resolved path.
     */
    public function getFilePath(string $relativePath): string {
        $path = self::$fileRoot . DIRECTORY_SEPARATOR . ($relativePath !== null ? $relativePath : '');
        if (($real = \realpath($path)) === false) {
            return __DIR__ . DIRECTORY_SEPARATOR . 'FORBIDDEN';
        }
        if (mb_strpos($real, self::$fileRoot) !== 0) {
            return __DIR__ . DIRECTORY_SEPARATOR . 'FORBIDDEN';
        }
        return $real;
    }

    /**
     * Does not check whether the path resolves to a path within the project's
     * root directory. Use with caution and never with external input.
     * @param string $relativePath Relative path to the project's root directory to resolve
     * @return string The resolved path.
     */
    public function getUnsafeFilePath(string $relativePath): string {
        return self::$fileRoot . DIRECTORY_SEPARATOR . ($relativePath !== null ? $relativePath : '');
    }

    public function getEngine(): Engine {
        if ($this->engine == null) {
            $this->engine = self::$engineFactory->makeEngine($this, !$this->isMode(self::MODE_PRODUCTION));
        }
        return $this->engine;
    }
    
    /**
     * @return CacheProvider A cache object for caching data. Do not write
     * to the cache from user requests or risk cache jamming!
     */
    public function getCache() : CacheProvider {
        return $this->cache;
    }

    public function getEm(int $i = 0): EntityManagerInterface {
        if (!\array_key_exists($i, $this->entityManagers)) {
            $this->entityManagers[$i] = self::$emFactory->makeEm(
                    $this->getEnvironment(),
                    $this->getFilePath("/private/php/entity"),
                    !$this->isMode(self::MODE_PRODUCTION));
        }
        return $this->entityManagers[$i];
    }
    
    public function getMailer(): IMailer {
        if ($this->mailer === null) {
            $this->mailer = self::$mailerFactory->makeMailer(
                    $this->getEnvironment(),
                    !$this->isMode(self::MODE_PRODUCTION));
        }
        return $this->mailer;
    }

    public function closeEm($i = null)
    {
        $this->withEm($i, function (EntityManager $em) {
            if ($em->isOpen()) {
                $em->flush();
                $em->close();
            }
        });
    }

    public function rollbackEm($i = null)
    {
        $this->withEm($i, function (EntityManager $em) {
            if ($em->isOpen() && $this->getEm()->getConnection()->isTransactionActive()) {
                $this->getEm()->rollback();
            }
        });
    }

    public function withEm(int $i = null, Closure $consumer = null)
    {
        $consumer = $consumer ?? function ($em) {};
        if ($i === null) {
            foreach ($this->entityManagers as $em) {
                $consumer($em);
            }
        } else {
            if (\array_key_exists($i, $this->entityManagers)) {
                $consumer($this->entityManagers[$i]);
            }
        }
    }

    private static function assertFileRoot($dir): string {
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

    /**
     * Reloads the configuration file from the file system and adds it to the
     * cache. This is slow, so do not use this unless you know what you are
     * doing. Normally, you are looking for getPhinx().
     */
    private function reloadPhinx() : array {
        $path = $this->getFilePath('private/config/phinx.yml');
        $raw = \file_exists($path) ? \file_get_contents($path) : false;
        if ($raw === false) {
            $raw = '';
        }
        try {
            $phinx = Yaml::parse($raw);
        } catch (ParseException $ignored) {
            $phinx = [];
        }
        if (!is_array($phinx)) {
            $phinx = [];
        }
        return $phinx;
    }
    
    public function getPhinx() : array {
        return $this->phinx;
    }
    
    private function extractPrivateKey(array & $phinx) {
        if (\array_key_exists('private_key', $phinx)) {
            $secretKey = $phinx['private_key'];
            unset($phinx['private_key']);
            $this->secretKey = Key::loadFromAsciiSafeString($secretKey);
        }
    }
    
    /**
     * Loads the configuration file, either from the cache if available, or 
     * from the file system otherwise.
     * @return array
     */
    private function loadPhinx(CacheProvider $cache) : array {
        $cached = $cache != null ? $cache->fetch('moose.phinx') : false;
        if ($cached !== false) {
            $phinx = $cached;
        }
        else {
            $phinx = $this->reloadPhinx();
            $cache->save('moose.phinx', $phinx);
        }
        $this->extractPrivateKey($phinx);
        return $phinx;
    }

    public function getLogFile(): string {
        if ($this->logfile === null) {
            $env = $this->getEnvironment();
            $path = \array_key_exists('logfile', $env) ? $env['logfile'] : null;
            if (empty($path)) {
                $this->logfile = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'baryllium.error.log';
            } else {
                $this->logfile = $path;
            }
        }
        return $this->logfile;
    }

    public function getMode(): string {
        return $this->mode;
    }

    private function extractMode(array $phinx): string {
        if (!\array_key_exists('environments', $phinx) || !\array_key_exists('default_database', $phinx['environments'])) {
            $mode = self::MODE_PRODUCTION;
        } else {
            $mode = $phinx['environments']['default_database'];
        }
        if ($mode !== self::MODE_DEVELOPMENT && $mode !== self::MODE_PRODUCTION && $mode !== self::MODE_TESTING) {
            $mode = self::MODE_PRODUCTION;
        }
        return $this->mode = $mode;
    }

    public function isMode(string $mode): bool
    {
        return $this->getMode() === $mode;
    }

    private function getServerRoot(): string {
        if ($this->contextPath === null) {
            $phinx = $this->getPhinx();
            if (!\array_key_exists('paths', $phinx) || !\array_key_exists('context', $phinx['paths'])) {
                throw new Exception('No context path specified, please see private/config/phinx.yml');
            }
            $contextPath = $phinx['paths']['context'];
            if ($contextPath === null) {
                \error_log('No context path specified, please see private/config/phinx.yml');
                $contextPath = '';
            }
            if ($contextPath == '/') {
                $contextPath = '';
            } else if (!empty($contextPath) && \substr($contextPath, 0, 1) !== '/') {
                $contextPath = '/' . $contextPath;
            }
            $this->contextPath = $contextPath;
        }
        return $this->contextPath;
    }

    /**
     * @return Key
     */
    public function getPrivateKey() {
        return $this->secretKey;
    }

    public function getSystemMailAddress(): string {
        $phinx = $this->getPhinx();
        if (!\array_key_exists('system_mail_address', $phinx)) {
            \error_log('System mail address not specified, please see private/config/phinx.yml');
            return 'sender@example.com';
        }
        return $phinx['system_mail_address'];
    }

    private function getEnvironment() {
        if ($this->environment === null) {
            $phinx = $this->getPhinx();
            $mode = $this->getMode();
            if (!\array_key_exists('environments', $phinx) || !\array_key_exists($mode, $phinx['environments'])) {
                $this->environment = [];
            } else {
                $this->environment = $phinx['environments'][$mode];
            }
        }
        return $this->environment;
    }

    /**
     * @return CacheProvider
     */
    private function makeCache() : CacheProvider {
        $cache = $this->getActualCache();
        $phinx = $this->loadPhinx($cache);
        $this->extractMode($phinx);
        // Do not cache anything in development/testing mode.
        if (!$this->isMode(self::MODE_PRODUCTION)) {
            $cache = new ArrayCache();
            $phinx = $this->reloadPhinx();
            $this->extractMode($phinx);
            // But now we switched to production mode, so let's update the cache.
            if ($this->isMode(self::MODE_PRODUCTION)) {
                $cache = $this->getActualCache();
                $phinx = $this->reloadPhinx();
                $this->extractMode($phinx);
                $cache->save('moose.phinx', $phinx);
            }
        }
        $this->phinx = $phinx;
        $this->cache = $cache;
        return $cache;
    }
    
    public function getActualCache() : CacheProvider {
        if (extension_loaded('apc')) {
            $cache = new ApcCache();
        } elseif (extension_loaded('xcache')) {
            $cache = new XcacheCache();
        } elseif (extension_loaded('memcache')) {
            $memcache = new Memcache();
            $memcache->connect('127.0.0.1');
            $cache = new MemcacheCache();
            $cache->setMemcache($memcache);
        } elseif (extension_loaded('redis')) {
            $redis = new Redis();
            $redis->connect('127.0.0.1');
            $cache = new RedisCache();
            $cache->setRedis($redis);
        } else {
            \error_log("Warning: Did not find any cache implementations, falling back to per-request array cache.");
            $cache = new ArrayCache();
        }
        return $cache;
    }
    
    public function getPath(string $name) {
        $paths = $this->getPhinx()['paths'] ?? [];
        $path = $paths[$name];
        return $path ?? '';
    }
}