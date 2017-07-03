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
use Doctrine\DBAL\Types\ProtectedString;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use League\Plates\Engine;
use LogicException;
use Memcache;
use Moose\Context\EntityManagerProviderInterface;
use Moose\Context\MailerProviderInterface;
use Moose\Context\PortalSessionHandler;
use Moose\Context\TemplateEngineProviderInterface;
use Moose\Dao\Dao;
use Moose\Entity\User;
use Moose\Util\CmnCnst;
use Moose\Web\HttpRequest;
use Moose\Web\HttpRequestInterface;
use Moose\Web\HttpResponseInterface;
use Nette\Mail\IMailer;
use RandomLib\Factory;
use Redis;
use Symfony\Component\HttpFoundation\Cookie;
use Throwable;
use function mb_strpos;

class Context extends Singleton implements EntityManagerProviderInterface, TemplateEngineProviderInterface, MailerProviderInterface {
    const PATH_TYPE_RELATIVE = 0;
    const PATH_TYPE_LOCAL = 1;
    const PATH_TYPE_PUBLIC = 2;

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
    
    /** @var PrivateKeyProviderInterface */
    private static $keyProvider;
    
    /** @var MooseConfig */
    private static $mooseConfig;

    /** @var Engine */
    private $engine;
    
    /** HttpRequestInterface */
    private $request;

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
    
    /** @var Factory */
    private $randomLibFactory;
    
    /** @var User */
    private $user;
    
    /** @var User */
    private $requestUser;

    public function __construct() {
        if (!self::$configured) {
            echo('No context configured.');
            die();
        }
        $this->entityManagers = [];
        try {
            $this->request = HttpRequest::createFromGlobals();
        }
        catch (\Throwable $e) {
            echo('Failed to create request.');
            die();
        }
        self::$instance = $this;
        try {
            $this->makeCache(self::$keyProvider ?? RequestKeyProvider::fromRequest($this->request), self::$mooseConfig);
        }
        catch (\Throwable $e) {
            echo("Failed to load configuration, please check whether the application was unlocked after startup.<pre>");
            if ($this->request->isLocalhost()) {
                \print_r(\get_class($e));
                echo(': ');
                \print_r($e->getMessage());
                echo("\n");
                \print_r($e->getTraceAsString());
            }
            echo("</pre>");
            die();
        }
        $this->sessionHandler = new PortalSessionHandler();
        self::$mooseConfig = null;
    }   
    
    public static function reconfigureInstance(string $fileRoot = null,
            PrivateKeyProviderInterface $keyProvider = null,
            MooseConfig $config = null,
            EntityManagerFactoryInterface $emFactory = null,
            PlatesEngineFactoryInterface $engineFactory = null,
            MailerFactoryInterface $mailerFactory = null) {
        if (self::$instance !== null) {
            self::$instance->closeEm();
        }
        self::$instance = null;
        self::$configured = null;
        self::configureInstance($fileRoot, $keyProvider, $config, $emFactory,
                $engineFactory, $mailerFactory);
    }

    public static function configureInstance(string $fileRoot = null,
            PrivateKeyProviderInterface $keyProvider = null,
            MooseConfig $config = null,
            EntityManagerFactoryInterface $emFactory = null,
            PlatesEngineFactoryInterface $engineFactory = null,
            MailerFactoryInterface $mailerFactory = null) {
        if (self::$configured) {
            \error_log('Context instance is already configured.');
            return;
        }
        $fr = $fileRoot ?? \dirname(__FILE__, 4);
        self::$keyProvider = $keyProvider ?? null;
        self::$fileRoot = self::assertFileRoot($fr);
        self::$configured = true;
        self::$mooseConfig = $config;
        self::$emFactory = $emFactory ?? new TreeEntityManagerFactory();
        self::$mailerFactory = $mailerFactory ?? new NetteMailerFactory();
        self::$engineFactory = $engineFactory ?? new DefaultPlatesEngineFactory();
    }

    public function getSessionHandler(): PortalSessionHandler {
        return $this->sessionHandler;
    }
   
    public function getServerPath(string $relativePath = '', int $type = self::PATH_TYPE_RELATIVE): string {
        $path = '';
        switch ($type) {
            case self::PATH_TYPE_LOCAL:
                $path .= $this->getConfiguration()->getPathLocalServer();
                break;
            case self::PATH_TYPE_PUBLIC:
                $path .= $this->getConfiguration()->getPathPublicServer();
                break;
            default:
                // Nothing to be added.
        }
        $path .= $this->getConfiguration()->getPathContext() . '/' . ($relativePath !== null ? $relativePath : '');
        return $path;
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
                    $this->getUnsafeFilePath("/private/php/entity"),
                    $this->getCache(),
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

    public function closeEm($i = null) {
        $this->withEm($i, function (EntityManager $em) {
            if ($em->isOpen()) {
                $em->flush();
                $em->close();
            }
        });
        if ($i === null) {
            $this->entityManagers = [];
        }
        else {
            $this->entityManagers[$i] = null;
        }
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
    private function loadConfig(CacheProvider $cache, PrivateKeyProviderInterface $keyProvider) : MooseConfig {
        $cached = $cache != null ? $cache->fetch(CmnCnst::CACHE_MOOSE_CONFIGURATION) : false;
        if ($cached !== false) {
            try {
                $config = MooseConfig::createFromArray($cached);
            }
            catch (Throwable $e) {
                $config = MooseConfig::createFromFile(null, $keyProvider);
                $cache->save(CmnCnst::CACHE_MOOSE_CONFIGURATION, $config->convertToArray(true, false));
            }
        }
        else {
            $config = MooseConfig::createFromFile(null, $keyProvider);
            $cache->save(CmnCnst::CACHE_MOOSE_CONFIGURATION, $config->convertToArray(true, false));
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
    private function makeCache(PrivateKeyProviderInterface $keyProvider,
            MooseConfig $config = null) : CacheProvider {
        $cache = self::getActualCache();
        if ($config === null) {
            $config = $this->loadConfig($cache, $keyProvider);
            // Do not cache anything in development/testing mode.
            if ($config->isNotEnvironment(MooseConfig::ENVIRONMENT_PRODUCTION)) {
                $cache = new ArrayCache();
                $config = MooseConfig::createFromFile(null, $keyProvider);
                // But now we switched to production mode, so let's update the cache.
                if ($config->isEnvironment(MooseConfig::ENVIRONMENT_PRODUCTION)) {
                    $cache = self::getActualCache();
                    $cache->save(CmnCnst::CACHE_MOOSE_CONFIGURATION, $config->convertToArray(true, false));
                }
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
            throw new LogicException('No supported cache found.');
        }
        return $cache;
    }

    public function getRequest() : HttpRequestInterface {
        return $this->request;
    }

    /**
     * @return Factory
     */
    public function getRandomLibFactory() : Factory {
        if ($this->randomLibFactory === null) {
            $this->randomLibFactory  = new Factory();
        }
        return $this->randomLibFactory ;
    }
    
    public function getUser(): User {
        if ($this->user === null) {
            $this->user = $this->determineUser();
        }
        return $this->user;
    }

    private function determineUser() : User {
        $sessionUser = $this->getSessionHandler()->getSessionUser();
        if ($sessionUser !== null && $sessionUser->isValid() && !$sessionUser->isAnonymous()) {
            return $sessionUser;
        }
        $requestUser = $this->getRequestUser();
        if ($requestUser !== null && $requestUser->isValid() && !$requestUser->isAnonymous()) {
            return $requestUser;
        }
        return User::getAnonymousUser();
    }

    /**
     * @return User
     */
    public function getRequestUser() : User {
        $requestUser = $this->requestUser;
        if ($requestUser === null) {
            $cookie = $this->getRequest()->getParam(CmnCnst::COOKIE_REMEMBERME, null, HttpRequestInterface::PARAM_COOKIE);
            if (empty($cookie)) {
                $requestUser = User::getAnonymousUser();
            }
            else {
                try {
                    $requestUser = $this->fetchUserFromDatabase($cookie);
                }
                catch (\Throwable $e) {
                    $requestUser = User::getAnonymousUser();
                }
            }
            if (!$requestUser->isAnonymous()) {
                $requestUser->markCookieAuthed();
            }
            $this->requestUser = $requestUser;
        }
        return $requestUser;
    }

    private function fetchUserFromDatabase(string $cookie) : User {
        /* @var $uuid string */
        /* @var $challenge string */
        list($uuid, $challenge) = explode('.', $cookie);
        if (empty($uuid) || empty($challenge)) {
            return User::getAnonymousUser();
        }
        $challenge = new ProtectedString($challenge);
        $token = Dao::expireToken($this->getEm())->findOneByToken($uuid);
        if ($token === null) {
            return User::getAnonymousUser();
        }
        if (!$token->isLegal($challenge)) {
            return User::getAnonymousUser();
        }
        $user = $token->getDataEntity($this->getEm(), User::class);
        if ($user === null) {
            return User::getAnonymousUser();
        }
        return $user;
    }
    
    public function expireRememberCookie(HttpResponseInterface $response) {
        $security = $this->getConfiguration()->getSecurity();
        $response->addCookie(new Cookie(
                CmnCnst::COOKIE_REMEMBERME,
                '',
                -1,
                '/',
                null,
                $security->getSessionSecure(),
                $security->getHttpOnly(),
                false,
                $security->getSameSite()
        ));        
    }
}