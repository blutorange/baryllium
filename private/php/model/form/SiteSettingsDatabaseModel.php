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

namespace Moose\FormModel;

use Moose\Context\MooseConfig;
use Moose\Controller\SiteSettingsEnvironmentController;
use Moose\Util\PlaceholderTranslator;
use Moose\Web\HttpRequestInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @see SiteSettingsEnvironmentController
 * @author madgaksha
 */
class SiteSettingsDatabaseModel extends AbstractFormModel {
    
    const MAP = [
        'configPath' => 'configpath',
        'host' => 'host',
        'port' => ['port', 0, 'Int'],
        'databaseType' => 'driver',
        'databaseName' => 'dbname',
        'username' => 'user',
        'password' => 'pass',
        'collation' => 'collation',
        'encoding' => 'encoding',
    ];
    
    /**
     * @var string
     * @Assert\NotBlank(message="settings.config.configpath.blank")
     */
    private $configPath;
    
    /**
     * @var string
     * @Assert\NotBlank(message="settings.db.host.blank")
     */
    private $host;

    /**
     * @var int
     * @Assert\NotBlank(message="settings.db.port.blank")
     * @Assert\Range(min=1, max=65535, minMessage="settings.db.port.min", maxMessage="settings.db.port.max")
     */
    private $port;

    /**
     * @var string
     * @Assert\NotBlank(message="settings.db.dbtype.blank")
     * @Assert\Choice(choices={"mysql", "oracle", "sqlite", "sqlserver", "postgres"}, message="settings.db.dbtype.invalid", strict=true)
     */
    private $databaseType;
    
    
    /**
     * @var string
     * @Assert\NotBlank(message="settings.db..dbname.blank")
     */
    private $databaseName;
    
    /**
     * @var string
     * @Assert\NotBlank(message="settings.db.username.blank")
     */
    private $username;
    
    /**
     * @Assert\NotBlank(message="settings.db.password.blank")
     * @var string
     */
    private $password;

    /**
     * @Assert\NotBlank(message="settings.db.collation.blank")
     * @var string
     */
    private $collation;

    /**
     * @Assert\NotBlank(message="settings.db.encoding.blank")
     * @var string
     */
    private $encoding;
    
    protected function __construct(HttpRequestInterface $request, PlaceholderTranslator $translator, array $fields) {
        parent::__construct($request, $translator, $fields);
    }
    
    public static function fromRequest(HttpRequestInterface $request, PlaceholderTranslator $translator) : SiteSettingsDatabaseModel {
        return new SiteSettingsDatabaseModel($request, $translator, self::MAP);
    }
    
    public static function fromConfig(HttpRequestInterface $request,
            PlaceholderTranslator $translator, MooseConfig $config) : SiteSettingsDatabaseModel {
        $model = new SiteSettingsDatabaseModel($request, $translator, self::MAP);
        $env = $config->getCurrentEnvironment();
        $db = $env->getDatabaseOptions();
        $model
                ->setConfigPath($config->getOriginalFile())
                ->setCollation($db->getCollation())
                ->setDatabaseName($db->getDatabaseName())
                ->setDatabaseType($db->getDatabaseType())
                ->setEncoding($db->getEncoding())
                ->setHost($db->getHost())
                ->setPassword($db->getPassword())
                ->setPort($db->getPort())
                ->setUsername($db->getUsername());
        return $model;
    }

    public function getConfigPath() : string {
        return $this->configPath;
    }

    public function getHost() : string {
        return $this->host;
    }

    public function getPort() : int {
        return $this->port;
    }

    public function getDatabaseType() : string {
        return $this->databaseType;
    }

    public function getDatabaseName() : string {
        return $this->databaseName;
    }

    public function getUsername() : string {
        return $this->username;
    }

    public function getPassword() : string {
        return $this->password;
    }

    public function getCollation() : string {
        return $this->collation;
    }

    public function getEncoding() : string {
        return $this->encoding;
    }

    public function setConfigPath(string $configPath = null) : SiteSettingsDatabaseModel {
        $this->configPath = $configPath ?? '';
        return $this;
    }

    public function setHost(string $host = null) : SiteSettingsDatabaseModel {
        $this->host = $host;
        return $this;
    }

    public function setPort(int $port = null) : SiteSettingsDatabaseModel {
        $this->port = $port ?? -1;
        return $this;
    }

    public function setDatabaseType(string $databaseType = null) : SiteSettingsDatabaseModel {
        $this->databaseType = $databaseType ?? '';
        return $this;
    }

    public function setDatabaseName(string $databaseName = null) : SiteSettingsDatabaseModel {
        $this->databaseName = $databaseName ?? '';
        return $this;
    }

    public function setUsername(string $username = null) : SiteSettingsDatabaseModel {
        $this->username = $username ?? '';
        return $this;
    }

    public function setPassword(string $password = null) : SiteSettingsDatabaseModel {
        $this->password = $password ?? '';
        return $this;
    }

    public function setCollation(string $collation = null) : SiteSettingsDatabaseModel {
        $this->collation = $collation ?? '';
        return $this;
    }

    public function setEncoding(string $encoding = null) : SiteSettingsDatabaseModel {
        $this->encoding = $encoding ?? '';
        return $this;
    }    
    
    protected function getGroups(): array {
        return [];
    }
}
