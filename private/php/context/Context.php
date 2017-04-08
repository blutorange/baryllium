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
use Doctrine\Common\Cache\ApcCache;
use Doctrine\Common\Cache\ApcuCache;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\CacheProvider;
use Doctrine\Common\Cache\MemcacheCache;
use Doctrine\Common\Cache\RedisCache;
use Doctrine\Common\Cache\XcacheCache;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use League\Plates\Engine;
use Memcache;
use Moose\Context\EntityManagerProviderInterface;
use Moose\Context\MailerProviderInterface;
use Moose\Context\PortalSessionHandler;
use Moose\Context\TemplateEngineProviderInterface;
use Nette\Mail\IMailer;
use Redis;
use function mb_strpos;

class Context extends Singleton implements EntityManagerProviderInterface, TemplateEngineProviderInterface, MailerProviderInterface {
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

    /** @var MooseConfig */
    private $config;

    /** @var PortalSessionHandler */
    private $sessionHandler;

    /** @var MooseEnvironment */
    private $environment;

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
        self::$emFactory = $emFactory ?? new AnnotationEntityManagerFactory();
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
        return $this->getConfiguration()->getPathContext() . '/' . ($relativePath !== null ? $relativePath : '');
    }
    
    public function getTaskServerPath(string $relativePath = ''): string {
        return $this->getConfiguration()->getPathTaskServer() . '/' . $relativePath;
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
            $this->engine = self::$engineFactory->makeEngine($this, $this->getConfiguration()->isNotEnvironment(MooseConfig::ENVIRONMENT_PRODUCTION));
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
                    $this->getConfiguration()->getCurrentEnvironment(),
                    $this->getFilePath("/private/php/entity"),
                    $this->getConfiguration()->isNotEnvironment(MooseConfig::ENVIRONMENT_PRODUCTION));
        }
        return $this->entityManagers[$i];
    }
    
    public function getMailer(): IMailer {
        if ($this->mailer === null) {
            $this->mailer = self::$mailerFactory->makeMailer(
                    $this->getConfiguration()->getCurrentEnvironment(),
                    $this->getConfiguration()->isNotEnvironment(MooseConfig::ENVIRONMENT_PRODUCTION));
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

    public function rollbackEm($i = null) {
        $this->withEm($i, function (EntityManager $em) {
            if ($em->isOpen() && $this->getEm()->getConnection()->isTransactionActive()) {
                $this->getEm()->rollback();
            }
        });
    }

    public function withEm(int $i = null, Closure $consumer = null) {
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
     * Loads the configuration file, either from the cache if available, or 
     * from the file system otherwise.
     * @return array
     */
    private function loadConfig(CacheProvider $cache) : MooseConfig {
        $cached = $cache != null ? $cache->fetch('moose.phinx') : false;
        if ($cached !== false) {
            try {
                $config = MooseConfig::createFromArray($cached);
            }
            catch (\Throwable $e) {
                \error_log("Invalid config in cache: " . $e);
                $config = MooseConfig::createFromFile();
                $cache->save('moose.phinx', $config->convertToArray());
            }
        }
        else {
            $config = MooseConfig::createFromFile();
            $cache->save('moose.phinx', $config->convertToArray());
        }
        return $config;
    }

    /** @return MooseConfig */
    public function getConfiguration() : MooseConfig {
        return $this->config;
    }

    /**
     * @return CacheProvider
     */
    private function makeCache() : CacheProvider {
        $cache = self::getActualCache();
        $config = $this->loadConfig($cache);
        // Do not cache anything in development/testing mode.
        if ($config->isNotEnvironment(MooseConfig::ENVIRONMENT_PRODUCTION)) {
            $cache = new ArrayCache();
            $config = MooseConfig::createFromFile();
            // But now we switched to production mode, so let's update the cache.
            if ($config->isEnvironment(MooseConfig::ENVIRONMENT_PRODUCTION)) {
                $cache = self::getActualCache();
                $cache->save('moose.phinx', $config->convertToArray());
            }
        }
        $this->config = $config;
        $this->cache = $cache;
        return $cache;
    }
    
    public static function getActualCache() : CacheProvider {
        if (\extension_loaded('apcu')) {
            $cache = new ApcuCache();
        }
        elseif (\extension_loaded('apc')) {
            $cache = new ApcCache();
        }
        elseif (\extension_loaded('xcache')) {
            $cache = new XcacheCache();
        }
        elseif (\extension_loaded('memcache')) {
            $memcache = new Memcache();
            $memcache->connect('127.0.0.1');
            $cache = new MemcacheCache();
            $cache->setMemcache($memcache);
        }
        elseif (\extension_loaded('redis')) {
            $redis = new Redis();
            $redis->connect('127.0.0.1');
            $cache = new RedisCache();
            $cache->setRedis($redis);
        }
        else {
            \error_log("Warning: Did not find any cache implementations, falling back to per-request array cache.");
            $cache = new ArrayCache();
        }
        return $cache;
    }
}