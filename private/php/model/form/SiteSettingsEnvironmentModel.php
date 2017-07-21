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
use Moose\Log\Logger;
use Moose\Util\PlaceholderTranslator;
use Moose\Web\HttpRequestInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @see SiteSettingsEnvironmentController
 * @author madgaksha
 */
class SiteSettingsEnvironmentModel extends AbstractFormModel {

    private static $MAP;

    /**
     * @var string
     * @Assert\NotBlank(message="settings.config.configpath.blank")
     */
    private $configPath;

    /**
     * @var bool
     */
    private $httpOnly;

    /**
     * @var bool
     */
    private $httpsOnly;

    /**
     * @Assert\NotBlank(message="settings.security.samesitepolicy.blank")
     * @Assert\Choice(choices={"lax", "strict"}, message="settings.security.samesitepolicy.invalid", strict=true)
     * @var string
     */
    private $sameSitePolicy;

    /**
     * @Assert\NotBlank(message="settings.logging.loglevel.blank")
     * @Assert\Choice(callback="getLogLevels", message="settings.logging.loglevel.invalid", strict=true)
     * @var string
     */
    private $logLevel;

    /**
     * @Assert\NotBlank(message="settings.security.remembertimeout.blank")
     * @Assert\Range(min=0, minMessage="settings.security.remembertimeout.min")
     * @var int
     */
    private $rememberTimeout;

    /**
     * @Assert\NotBlank(message="settings.paths.doctrineproxypath.blank")
     * @var string
     */
    private $doctrineProxyPath;

    /**
     * @Assert\NotBlank(message="settings.paths.serverpublic.blank")
     * @var string
     */
    private $publicServerAddress;

    /**
     * @Assert\NotBlank(message="settings.paths.serverlocal.blank")
     * @var string
     */
    private $localServerAddress;

    /**
     * @Assert\NotBlank(message="settings.paths.logfile.blank")
     * @var string
     */
    private $logfilePath;

    protected function __construct(HttpRequestInterface $request,
            PlaceholderTranslator $translator, array $fields) {
        parent::__construct($request, $translator, $fields);
    }

    public static function fromRequest(HttpRequestInterface $request,
            PlaceholderTranslator $translator): SiteSettingsEnvironmentModel {
        return new SiteSettingsEnvironmentModel($request, $translator, self::getMap());
    }

    public static function fromConfig(HttpRequestInterface $request,
            PlaceholderTranslator $translator, MooseConfig $config): SiteSettingsEnvironmentModel {
        $model = new SiteSettingsEnvironmentModel($request, $translator,
                self::getMap());
        $env = $config->getCurrentEnvironment();
        $sec = $config->getSecurity();
        $model
                ->setConfigPath($config->getOriginalFile())
                ->setDoctrineProxyPath($config->getPathDoctrineProxy())
                ->setHttpOnly($sec->getHttpOnly())
                ->setHttpsOnly($sec->getSessionSecure())
                ->setLocalServerAddress($config->getPathLocalServer())
                ->setLogfilePath($env->getLogFile())
                ->setPublicServerAddress($config->getPathPublicServer())
                ->setRememberTimeout($sec->getRememberMeTimeout())
                ->setLogLevel($env->getLogLevelName())
                ->setSameSitePolicy($sec->getSameSite());
        return $model;
    }

    public function getHttpOnly(): bool {
        return $this->httpOnly;
    }

    public function getHttpsOnly(): bool {
        return $this->httpsOnly;
    }

    public function getLogLevel() : string {
        return $this->logLevel;
    }

    public function setLogLevel(string $logLevel = null) {
        $this->logLevel = $logLevel;
        return $this;
    }

    public function getSameSitePolicy(): string {
        return $this->sameSitePolicy;
    }

    public function getRememberTimeout(): int {
        return $this->rememberTimeout;
    }

    public function getDoctrineProxyPath(): string {
        return $this->doctrineProxyPath;
    }

    public function getPublicServerAddress(): string {
        return $this->publicServerAddress;
    }

    public function getLocalServerAddress(): string {
        return $this->localServerAddress;
    }

    public function getLogfilePath(): string {
        return $this->logfilePath;
    }

    public function getConfigPath(): string {
        return $this->configPath;
    }

    public function setConfigPath(string $configPath = null) {
        $this->configPath = $configPath ?? '';
        return $this;
    }

    public function setHttpOnly(bool $httpOnly): SiteSettingsEnvironmentModel {
        $this->httpOnly = $httpOnly;
        return $this;
    }

    public function setHttpsOnly(bool $httpsOnly): SiteSettingsEnvironmentModel {
        $this->httpsOnly = $httpsOnly;
        return $this;
    }

    public function setSameSitePolicy(string $sameSitePolicy): SiteSettingsEnvironmentModel {
        $this->sameSitePolicy = $sameSitePolicy;
        return $this;
    }

    public function setRememberTimeout(int $rememberTimeout): SiteSettingsEnvironmentModel {
        $this->rememberTimeout = $rememberTimeout;
        return $this;
    }

    public function setDoctrineProxyPath(string $doctrineProxyPath = null): SiteSettingsEnvironmentModel {
        $this->doctrineProxyPath = $doctrineProxyPath ?? '';
        return $this;
    }

    public function setPublicServerAddress(string $publicServerAddress = null): SiteSettingsEnvironmentModel {
        $address = $publicServerAddress ?? '';
        $this->publicServerAddress = \trim($address, " \t\n\r\0\x0B/");
        return $this;
    }

    public function setLocalServerAddress(string $localServerAddress = null): SiteSettingsEnvironmentModel {
        $address = $localServerAddress ?? '';
        $this->localServerAddress = \trim($address, " \t\n\r\0\x0B/");
        return $this;
    }

    public function setLogfilePath(string $logfilePath = null): SiteSettingsEnvironmentModel {
        $this->logfilePath = $logfilePath ?? '';
        return $this;
    }

    protected function getGroups(): array {
        return [];
    }

    private static function getMap() {
        if (self::$MAP === null) {
            self::$MAP = [
                'configPath'          => 'configpath',
                'httpOnly'            => ['httponly', true, 'bool'],
                'httpsOnly'           => ['httpsonly', true, 'bool'],
                'sameSitePolicy'      => ['samesite', 'strict'],
                'rememberTimeout'     => ['remembertime', 86400, 'int'],
                'doctrineProxyPath'   => 'docproxy',
                'publicServerAddress' => 'serverpublic',
                'localServerAddress'  => 'serverlocal',
                'logfilePath'         => 'logfile',
                'logLevel'            => 'loglevel'
            ];
        }
        return self::$MAP;
    }
    
    public function getLogLevels() {
        return Logger::LEVEL_NAMES;
    }
}
