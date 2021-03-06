<?php

/* The 3-Clause BSD License
 * 
 * SPDX short identifier: BSD-3-Clause
 *
 * Note: This license has also been called the "New BSD License" or "Modified
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
use Defuse\Crypto\Key;
use Doctrine\DBAL\Types\ProtectedString;
use LogicException;
use Moose\Util\EncryptionUtil;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;
use Throwable;
use const MB_CASE_LOWER;
use function mb_convert_case;
use function mb_scrub;
use function mb_strlen;
use function mb_substr;

/**
 * Models the configuration required by this web application and takes care
 * of serializing and deserializing it.
 *
 * @author madgaksha
 */
class MooseConfig {
    const ENVIRONMENT_PRODUCTION = 'production';
    const ENVIRONMENT_DEVELOPMENT = 'development';
    const ENVIRONMENT_TESTING = 'testing';

    /** @var MooseEnvironment[] */
    private $environments;
    
    /** @var string */
    private $migrationTable;
    
    /** @var string */
    private $currentEnvironmentName;
    
    /** @var Key */
    private $privateKey;
    
    /** @var string */
    private $versionOrder;
    
    /** @var string */
    private $systemMailAddress;
    
    /** @var string */
    private $pathContext;
    
    /** @var string */
    private $pathLocalServer;
    
    /** @var string */
    private $pathPublicServer;
    
    /** @var MooseSecurity */
    private $security;
    
    /** @var string */
    private $pathMigrations;
    
    /** @var string */
    private $pathSeeds;
    
    /** @var string */
    private $pathDoctrineProxy;
    
    /** @var string */
    private $originalFile;
    
    /** @var MooseTasks */
    private $tasks;

    private function __construct(array & $yaml, PrivateKeyProviderInterface $keyProvider = null, $skipCheck = false, $originalFile = null) {
        // Decrypt.
        $privateKey = self::assertKeySource($yaml, $keyProvider, $skipCheck);
        // Assert santity.
        $top = self::assertTop($yaml);
        $paths = self::assertPaths($top['paths']);
        $environments = self::assertEnvironments($top['environments']);        
        $this->originalFile = $originalFile ?? $top['__self'] ?? null;
        // Read and configuration.
        $this->systemMailAddress = $top['system_mail_address'];
        $this->versionOrder = $top['version_order'];
        $this->pathContext = $this->sanitizeContextPath($paths['context']);
        $this->pathLocalServer = $this->sanitizeTaskServerPath($paths['local_server']);
        $this->pathPublicServer = $this->sanitizeTaskServerPath($paths['public_server']);
        $this->pathMigrations = $paths['migrations'];
        $this->pathSeeds = $paths['seeds'];
        $this->pathDoctrineProxy = $paths['doctrine_proxy'];
        $this->security= MooseSecurity::makeFromArray($top['security']);
        $this->tasks= MooseTasks::makeFromArray($top['tasks'] ?? []);
        $this->environments = [];
        foreach ($environments as $key => $value) {
            switch ($key) {
                case 'default_migration_table':
                    $this->migrationTable = $value;
                    break;
                case 'default_database':
                    $this->currentEnvironmentName = $value;
                    break;
                default:
                    $this->environments[$key] = MooseEnvironment::makeFromArray($value, $key);
                    break;
            }
        }
        $this->privateKey = $privateKey;
    }
    
    
    /**
     * @return string|null The path of the configuration file, or null when
     * it was never read from a file.
     */
    public function getOriginalFile() {
        return $this->originalFile;
    }

    public function isEnvironment(string $environment) : bool {
        return $this->getCurrentEnvironmentName() === $environment;
    }
    
    public function isNotEnvironment(string $environment) : bool {
        return $this->getCurrentEnvironmentName() !== $environment;
    }
    
    public function & convertToArray(bool $withKey = false, bool $encrypt = true, bool $withSelf = true) {
        $base = [
            'system_mail_address' => $this->systemMailAddress,
            'version_order' => $this->versionOrder,
            'security' => $this->security->convertToArray(),
            'tasks' => $this->tasks->convertToArray(),
            'paths' => [
                'doctrine_proxy' => $this->pathDoctrineProxy,
                'public_server' => $this->pathPublicServer,
                'local_server' => $this->pathLocalServer,
                'migrations' => $this->pathMigrations,
                'seeds' => $this->pathSeeds,
                'context' => $this->pathContext
            ],
            'environments' => [
                'default_migration_table' => $this->migrationTable,
                'default_database' => $this->currentEnvironmentName
            ],
        ];
        if ($withSelf) {
            $base['__self'] = $this->originalFile;
        }
        foreach ($this->environments as $name => $environment) {
            $base['environments'][$name] = $environment->convertToArray();
        }
        if ($encrypt) {
            EncryptionUtil::encryptArray($base, $this->privateKey);
        }
        if ($withKey) {
            $base['private_key'] = $this->privateKey->saveToAsciiSafeString();
        }
        $base['is_encrypted'] = $encrypt;
        return $base;
    }

    public function saveAs(string $path = null, bool $withKey = false, bool $encrypt = true) {
        if ($path === null) {
            // Take the default path at private/config/phinx.yml
            $path = $this->originalFile ?? $this->getDefaultPath();
        }
        if (!\file_exists(\dirname($path))) {
            \mkdir(\dirname($path), 0660, true);
        }
        $yaml = $this->convertToArray($withKey, $encrypt, false);
        $raw = Yaml::dump($yaml, 4, 4);
        if (\file_put_contents($path, $raw) === false) {
            throw new IOException("Failed to write config file at $path.");
        }
    }

    public function getMigrationTable() {
        return $this->migrationTable;
    }

    public function getSecurity(): MooseSecurity {
        return $this->security;
    }
    
    public function getCurrentEnvironmentName() {
        return $this->currentEnvironmentName;
    }

    public function getPrivateKey(): Key {
        return $this->privateKey;
    }

    public function getVersionOrder() {
        return $this->versionOrder;
    }

    public function getSystemMailAddress() {
        return $this->systemMailAddress;
    }
    
    public function getPathContext() {
        return $this->pathContext;
    }

    public function getPathLocalServer() {
        return $this->pathLocalServer;
    }
    
    public function getPathPublicServer() : string {
        return $this->pathPublicServer;
    }

    public function getPathMigrations() {
        return $this->pathMigrations;
    }

    public function getPathSeeds() {
        return $this->pathSeeds;
    }
    
    public function setPathLocalServer(string $pathLocalServer) : MooseConfig {
        $this->pathLocalServer = $pathLocalServer;
        return $this;
    }

    public function setPathPublicServer(string $pathPublicServer) : MooseConfig {
        $this->pathPublicServer = $pathPublicServer;
        return $this;
    }

    public function setPathMigrations(string $pathMigrations) : MooseConfig {
        $this->pathMigrations = $pathMigrations;
        return $this;
    }

    public function setPathSeeds(string $pathSeeds) : MooseConfig {
        $this->pathSeeds = $pathSeeds;
        return $this;
    }

    public function setPathDoctrineProxy(string $pathDoctrineProxy) : MooseConfig {
        $this->pathDoctrineProxy = $pathDoctrineProxy;
        return $this;
    }
    
    /**
     * @return MooseEnvironment The environment currently set as the default..
     */
    public function getCurrentEnvironment() : MooseEnvironment {
        return $this->environments[$this->currentEnvironmentName];
    }
    
    public function setCurrentEnvironment(string $environmentName) : MooseConfig {
        if (!isset($this->environments[$environmentName]))
            throw new LogicException("Cannot set current environment to $environmentName, no such environment defined.");
        $this->currentEnvironmentName = $environmentName;
        return $this;
    }
    
    /**
     * 
     * @param string $environmentName
     * @param array|MooseEnvironment $environment
     * @return MooseConfig
     * @throws LogicException
     */
    public function addEnvironment(string $environmentName, $environment) : MooseConfig {
        if (isset($this->environments[$currentName]))
            throw new LogicException("Cannot add environment $environmentName, it exists already. Use updateEnvironment instead.");
        $this->environments[$environmentName] = \is_array($environment) ? MooseEnvironment::makeFromArray($environment, $environmentName) : $environment;
        return $this;
    }
    
    public function updateEnvironment(string $environmentName, Closure $function) : MooseConfig {
        if (!isset($this->environments[$environmentName]))
            throw new LogicException("Cannot update environment $environmentName, no such environment defined.");
        $oldEnvironment = $this->environments[$environmentName];
        $newEnvironment = $function($oldEnvironment) ?? $oldEnvironment;
        if ($newEnvironment instanceof MooseConfig)
            $this->environments[$environmentName] = $newEnvironment;
        else if (\is_array($newEnvironment))
            $this->environments[$environmentName] = MooseEnvironment::makeFromArray($newEnvironment, $environmentName);
        else
            throw new LogicException("Cannot update environment $environmentName, function did not return array.");
        return $this;
    }

    public function setSystemMailAddress($systemMailAddress) : MooseConfig {
        $this->systemMailAddress = $systemMailAddress;
        return $this;
    }
            
    private static function getDefaultPath() : string {
        return \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'config' . \DIRECTORY_SEPARATOR . 'phinx.yml';
    }

    private function sanitizeContextPath($contextPath) {
        if ($contextPath === null) {
            \error_log('No context path specified, please see private/config/phinx.yml');
            return '';
        }
        if ($contextPath == '/') {
            return '';
        }
        // Dirname may add backslashes, especially when going to the top-level path.
        $contextPath = \preg_replace('/\\\\/u', '/', $contextPath);
        if (!empty($contextPath) && \substr($contextPath, 0, 1) !== '/') {
            return '/' . $contextPath;
        }
        return $contextPath;
    }

    private function sanitizeTaskServerPath($taskServerPath) {
        if (mb_substr($taskServerPath, 0, 4) !== 'http') {
            $taskServerPath = "http://$taskServerPath";
        }
        if (mb_substr($taskServerPath, mb_strlen($taskServerPath) - 1) === '/') {
            $taskServerPath = mb_scrub($taskServerPath, 0, mb_strlen($taskServerPath) - 1);
        }
        return $taskServerPath;
    }
    
    private static function assertKeySource(array & $yaml, PrivateKeyProviderInterface $keyProvider = null, bool $skipCheck = false) {
        $keystring = $yaml['private_key'] ?? null;
        $isEncrypted = $yaml['is_encrypted'] ?? false;
        if (isset($yaml['private_key'])) {
            unset($yaml['private_key']);
        }
        if (isset($yaml['is_encrypted'])) {
            unset($yaml['is_encrypted']);
        }
        $key = null;
        if (!empty($keystring)) {
            try {
                $key = Key::loadFromAsciiSafeString($keystring);
            }
            catch (Throwable $e) {
                $keystring = null;
                $class = \get_class($e);
                $yaml = null;
                throw new LogicException($class . " - " . $e->getMessage());
            }
        }
        else if ($keyProvider !== null) {           
            try {
                $protectedString = $keyProvider->fetch();
            }
            catch (\Throwable $e) {
                $keyProvider = null;
                $protectedString = null;
                $yaml = null;
                $class = \get_class($e);
                throw new LogicException($class . " - " . $e->getMessage());
            }
            if ($protectedString === null) {
                $yaml = null;
                $keyProvider = null;
                throw new LogicException('Cannot create config, no key provided.');
            }            
            $key = Key::loadFromAsciiSafeString($protectedString->getString());
        }
        if ($key === null) {
            $yaml = null;
            throw new LogicException('Cannot create config, missing private key.');
        }
        if ($isEncrypted === true) {
            EncryptionUtil::decryptArray($yaml, $key);
        }
        $env = $yaml['environments']['default_database'];
        if ($env !== MooseConfig::ENVIRONMENT_DEVELOPMENT
                && $env !== MooseConfig::ENVIRONMENT_TESTING) {
            if (!empty($keystring)) {
                $keystring = null;
                $keyProvider = null;
                $env = null;
                throw new LogicException('Cannot create config, unsafe stored key in production mode.');
            }
            if (!$skipCheck && $isEncrypted !== true) {
                $keystring = null;
                $keyProvider = null;
                $env = null;
                throw new LogicException('Cannot create config, encryption must be enabled in production mode.');
            }
        }
        return $key;
    }

    private static function & assertPaths(array & $paths) : array {
        if (!isset($paths['context']))
            throw new LogicException('Cannot create config, missing path/context entry.');
        if (!isset($paths['public_server']))
            throw new LogicException('Cannot create config, missing paths/public_server entry.');
        if (!isset($paths['local_server']))
            throw new LogicException('Cannot create config, missing paths/local_server entry.');
        if (!isset($paths['seeds']))
            throw new LogicException('Cannot create config, missing paths/seeds entry.');
        if (!isset($paths['migrations']))
            throw new LogicException('Cannot create config, missing paths/migrations entry.');
        if (!isset($paths['doctrine_proxy']) || empty($paths['doctrine_proxy']))
            $paths['doctrine_proxy'] = dirname(__DIR__,1);
        return $paths;
    }

    private static function & assertEnvironments(array & $environments) : array{
        if (!isset($environments['default_migration_table']))
            throw new LogicException('Cannot create config, missing environments/default_migration_table entry.');
        if (!isset($environments['default_database']))
            throw new LogicException('Cannot create config, missing environments/default_database entry.');
        $defaultEnvironment = $environments['default_database'];
        if (!isset($environments[$defaultEnvironment]))
            throw new LogicException("Cannot create config, missing environments/$defaultEnvironment entry.");
        return $environments;
    }

    private static function & assertTop(array & $top) : array {
        if (!isset($top['paths']))
            throw new LogicException('Cannot create config, missing paths entry.');
        if (!isset($top['environments']))
            throw new LogicException('Cannot create config, missing environemnts entry.');
        if (!isset($top['version_order']))
            throw new LogicException('Cannot create config, missing version_order entry.');
        if (!isset($top['system_mail_address']))
            throw new LogicException('Cannot create config, missing system_mail_address entry.');
        if (!isset($top['security']))
            throw new LogicException('Cannot create config, missing security entry.');
        return $top;
    }
    
    /**
     * 
     * @param string $path Path to the configuration file. Defaults to the
     * default path when not specified.
     * @return MooseConfig A new configuration.
     * @throws IOException When the file could not be read.
     * @throws ParseException When the file contains invalid YAML or illegal data.
     */
    public static function createFromFile(string $path = null,
            PrivateKeyProviderInterface $keyProvider = null) : MooseConfig {
        if ($path === null) {
            // Take the default path at private/config/phinx.yml
            $path = self::getDefaultPath();
        }
        if (($raw = \file_get_contents($path)) === false) {
            throw new IOException("Failed to read config file at $path.");
        }
        // Allow this to throw a parse exception. Nothing we can do in this
        // case.
        $yaml = Yaml::parse($raw);
        if (empty($yaml['delegate'] ?? null)) {
            return new MooseConfig($yaml, $keyProvider, false , $path);
        }
        return self::createFromFile($yaml['delegate'], $keyProvider);
    }
    
    /**
     * @param $config array The configuration in the format as returned by MooseConfig::convertToArray.
     * @return The configuration.
     */
    public static function createFromArray(array $config, Key $pk = null) : MooseConfig {
        if (!isset($config['private_key'])) {
            if ($pk === null) {
                throw new \LogicException('Security violation: Missing private key.');
            }
            $config['private_key'] = $pk->saveToAsciiSafeString();
        }
        $keyProvider = new StaticKeyProvider(new ProtectedString($config['private_key']));
        unset($config['private_key']);
        try {
            return new MooseConfig($config, $keyProvider, true);
        }
        catch (\Throwable $e) {
            $config = null;
            $keyProvider = null;
            $class = \get_class($e);
            throw new $class($e->getMessage());
        }
    }
    
    /**
     * Creates a new configuration with some default settings. Will not work
     * as-is.
     * @return MooseConfig A configuration with some default settings.
     */
    public static function createDefault(string $contextPath,
            string $publicServer, string $localServer,
            string $environment = self::ENVIRONMENT_DEVELOPMENT) : MooseConfig {
        // Try and get some default mail.
        $mailAddress = \ini_get('sendmail_from');
        if (empty($mailAddress)) {
            $mailAddress = 'yourmail@provider.net';
        }

        $currentUser = \get_current_user();
        $privateKey = Key::createNewRandomKey()->saveToAsciiSafeString();

        $environment = \trim(mb_convert_case($environment, MB_CASE_LOWER));
        if ($environment !== self::ENVIRONMENT_DEVELOPMENT && $environment !== self::ENVIRONMENT_PRODUCTION && $environment !== self::ENVIRONMENT_TESTING) {
            $environment = self::ENVIRONMENT_DEVELOPMENT;
        }
        
        $guess = [
            'paths' => [
                'context' => $contextPath,
                'migrations' => '%%PHINX_CONFIG_DIR%%/private/db/migrations',
                'seeds' => '%%PHINX_CONFIG_DIR%%/db/seeds',
                'local_server' => $localServer,
                'public_server' => $publicServer,
                'doctrine_proxy' => \dirname(__DIR__, 1)
            ],
            'environments' => [
                'default_migration_table' => 'phinxlog',
                'default_database' => $environment,
                $environment => [
                    'mail' => 'php',
                    'database' => [
                        'name' => '',
                        'user' => '',
                        'pass' => '',
                        'host' => '',
                        'port' => '',
                        'driver' => '',
                        'charset' => '',
                        'collation' => '',
                    ]
                ]
            ],
            'security' => [
                'remember_me_timeout' => '604800',
                'session_timeout' => '86400',
                'http_only' => 'true',
                'session_secure' => 'false',
                'same_site' => 'strict'
            ],
            'system_mail_address' => empty($mailAddress) ? "$currentUser@127.0.0.1.net" : $mailAddress,
            'private_key' => $privateKey,
            'is_encrypted' => false,
            'version_order' => 'creation'
        ];
        
        return new MooseConfig($guess);
    }

    public function getPathDoctrineProxy() {
        return $this->pathDoctrineProxy;
    }

    public function getTasks() : MooseTasks {
        return $this->tasks;
    }
}