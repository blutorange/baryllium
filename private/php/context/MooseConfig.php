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

use Defuse\Crypto\Key;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * Models the configuration required by this web application and takes care
 * of serializing and deserializing it.
 *
 * @author madgaksha
 */
class MooseConfig {
    /** @var array */
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
    private $pathTaskServer;
    
    /** @var string */
    private $pathMigrations;
    
    /** @var string */
    private $pathSeeds;

    private function __construct(array & $yaml) {
        $top = $this->assertTop($yaml);
        $paths = $this->assertPaths($top['paths']);
        $environments = $this->assertEnvironments($top['environments']);
        $this->systemMailAddress = $top['system_mail_address'];
        $this->privateKey = Key::loadFromAsciiSafeString($top['private_key']);
        $this->versionOrder = $top['version_order'];
        $this->pathContext = $this->sanitizeContextPath($paths['context']);
        $this->pathTaskServer = $this->sanitizeTaskServerPath($paths['task_server']);
        $this->pathMigrations = $paths['migrations'];
        $this->pathSeeds = $paths['seeds'];
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
                    $this->environments[$key] = $value;
                    break;
            }
        }
    }
    
    public function & convertToArray() {
        $base = [
            'system_mail_address' => $this->systemMailAddress,
            'private_key' => $this->privateKey->saveToAsciiSafeString(),
            'version_order' => $this->versionOrder,
            'paths' => [
                'task_server' => $this->pathTaskServer,
                'migrations' => $this->pathMigrations,
                'seeds' => $this->pathSeeds,
                'context' => $this->pathContext
            ],
            'environments' => [
                'default_migration_table' => $this->migrationTable,
                'default_database' => $this->currentEnvironmentName
            ]
        ];
        foreach ($this->environments as $name => $environment) {
            $base['environments'][$name] = $environment;
        }
        return $base;
    }

    public function saveAs(string $path = null) {
        if ($path === null) {
            // Take the default path at private/config/phinx.yml
            $path = $this->getDefaultPath();
        }
        \mkdir(\dirname($path), 0660, true);
        $yaml = $this->convertToArray();
        $raw = Yaml::dump($yaml, 4, 4);
        if (\file_put_contents($path, $raw) === false) {
            throw new IOException("Failed to read config file at $path.");
        }
    }

    public function getMigrationTable() {
        return $this->migrationTable;
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

    public function getPathTaskServer() {
        return $this->pathTaskServer;
    }

    public function getPathMigrations() {
        return $this->pathMigrations;
    }

    public function getPathSeeds() {
        return $this->pathSeeds;
    }

    public function getCurrentEnvironment() {
        return $this->environments[$this->currentEnvironmentName];
    }
    
    public function setCurrentEnvironment(string $environmentName) : MooseConfig {
        if (!isset($this->environments[$environmentName]))
            throw new LogicException("Cannot set current environment to $environmentName, no such environment defined.");
        $this->currentEnvironmentName = $environmentName;
        return $this;
    }
    
    public function addEnvironment(string $environmentName, array & $environment) : MooseConfig {
        if (isset($this->environments[$currentName]))
            throw new LogicException("Cannot add environment $environmentName, it exists already. Use updateEnvironment instead.");
        $this->environments[$environmentName] = $environments;
        return $this;
    }
    
    public function updateEnvironment(string $environmentName, \Closure $function) : MooseConfig {
        if (!isset($this->environments[$environmentName]))
            throw new LogicException("Cannot update environment $environmentName, no such environment defined.");
        $oldEnvironment = $this->environments[$environmentName];
        $newEnvironment = $function($oldEnvironment);
        if (!is_array($newEnvironment))
            throw new LogicException("Cannot update environment $environmentName, function did not return array.");
        $this->environments[$environmentName] = $newEnvironment;
        return $this;
    }

    public function setSystemMailAddress($systemMailAddress) : MooseConfig {
        $this->systemMailAddress = $systemMailAddress;
        return $this;
    }
            
    private function getDefaultPath() {
        return \dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'phinx.yml';
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
        $contextPath = preg_replace('/\\\\/u', '/', $contextPath);
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

    private function & assertPaths(array & $paths) : array {
        if (!isset($paths['context']))
            throw new \LogicException('Cannot create config, missing path/context entry.');
        if (!isset($paths['context']))
            throw new \LogicException('Cannot create config, missing paths/taskServer entry.');
        if (!isset($paths['seeds']))
            throw new \LogicException('Cannot create config, missing paths/seeds entry.');
        if (!isset($paths['migrations']))
            throw new \LogicException('Cannot create config, missing paths/migrations entry.');
        return $paths;
    }

    private function & assertEnvironments(array & $environments) : array{
        if (!isset($environments['default_migration_table']))
            throw new \LogicException('Cannot create config, missing environments/default_migration_table entry.');
        if (!isset($environments['default_database']))
            throw new \LogicException('Cannot create config, missing environments/default_database entry.');
        $defaultEnvironment = $environments['default_database'];
        if (!isset($environments[$defaultEnvironment]))
            throw new \LogicException("Cannot create config, missing environments/$defaultEnvironment entry.");
        return $environments;
    }

    private function & assertTop(array & $top) : array {
        if (!isset($top['paths']))
            throw new \LogicException('Cannot create config, missing paths entry.');
        if (!isset($top['environments']))
            throw new \LogicException('Cannot create config, missing environemnts entry.');
        if (!isset($top['version_order']))
            throw new \LogicException('Cannot create config, missing version_order entry.');
        if (!isset($top['private_key']))
            throw new \LogicException('Cannot create config, missing private_key entry.');
        if (!isset($top['system_mail_address']))
            throw new \LogicException('Cannot create config, missing system_mail_address entry.');
        return $top;
    }
    
        /**
     * 
     * @param string $path Path to the configuration file. Default to the
     * default path when not specified.
     * @return MooseConfig A new configuration.
     * @throws IOException When the file could not be read.
     * @throws ParseException When the file contains invalid YAML or illegal data.
     */
    public static function createFromFile(string $path = null) : MooseConfig {
        if ($path === null) {
            // Take the default path at private/config/phinx.yml
            $path = $this->getDefaultPath();
        }
        if (($raw = \file_get_contents($path)) === false) {
            throw new IOException("Failed to read config file at $path.");
        }
        // Allow this to throw a parse exception. Nothing we can do in this
        // case.
        $yaml = Yaml::parse($raw);
        return new MooseConfig($yaml);
    }
    
    /**
     * Creates a new configuration with some default settings. Will not work
     * as-is.
     * @return MooseConfig A configuration with some default settings.
     */
    public static function createDefault(string $contextPath, string $taskServer) : MooseConfig {
        $mailAddress = ini_get('sendmail_from');
        $currentUser = get_current_user();
        $privateKey = Key::createNewRandomKey()->saveToAsciiSafeString();

        $guess = [
            'paths' => [
                'context' => $contextPath,
                'migrations' => '%%PHINX_CONFIG_DIR%%/private/db/migrations',
                'seeds' => '%%PHINX_CONFIG_DIR%%/db/seeds',
                'task_server' => $taskServer
            ],
            'environments' => [
                'default_migration_table' => 'phinxlog',
                'default_database' => Context::MODE_PRODUCTION,
                Context::MODE_PRODUCTION => [
                    'dbname' => '',
                    'user' => '',
                    'password' => '',
                    'host' => '',
                    'port' => '',
                    'driver' => '',
                    'charset' => '',
                    'collation-server' => '',
                    'character-set-server' => ''
                ]
            ],
            'system_mail_address' => empty($mailAddress) ? "$currentUser@localhost" : $mailAddress,
            'private_key' => $privateKey,
            'version_order' => 'creation'
        ];
        
        return new MooseConfig($guess);
    }
}