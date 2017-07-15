<?php
declare(strict_types=1);

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
use Moose\Context\MooseEnvironment;
use Moose\Context\MooseSmtpOptions;
use Moose\Util\PlaceholderTranslator;
use Moose\Web\HttpRequestInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Description of SiteSettingsMailFormModel
 *
 * @author madgaksha
 */
class SiteSettingsMailFormModel extends AbstractFormModel {

    const MAP = [
        'systemMailAddress' => 'sysmail',
        'configPath' => 'configpath',
        'mailType' => 'mailtype',
        'smtpHost' => 'smtphost',
        'smtpUser' => 'smtpuser',
        'smtpPass' => 'smtppass',
        'smtpPort' => ['smtpport', 465, 'Int'],
        'smtpSecurity' => ['smtpsec', 'ssl'],
        'smtpPersistentConnection' => ['smtppers', false, 'Bool'],
        'smtpConnectionTimeout' => ['smtptime', 20, 'Int'],
        'smtpBindTo' => ['smtpbind', 0, 'Int']
    ];
    
    /**
     * @var string
     * @Assert\NotBlank(message="settings.config.configpath.blank")
     */
    private $configPath;
    
    /**
     * @var string
     * @Assert\NotBlank(message="settings.mail.sysmail.blank")
     * @Assert\Email(message="settings.mail.sysmail.invalid")
     */
    private $systemMailAddress;
    
    /**
     * @var string
     * @Assert\NotBlank(message="settings.mail.type.blank")
     * @Assert\Choice(choices={"php", "smtp"}, message="settings.mail.type.invalid", strict=true)
     */
    private $mailType;
    
    /**
     * @var string
     * @Assert\NotBlank(message="settings.mail.smtphost.blank", groups = {"smtp"})
     */
    private $smtpHost;

    /**
     * @var string
     * @Assert\NotBlank(message="settings.mail.smtpuser.blank", groups = {"smtp"})
     */
    private $smtpUser;
    
    /**
     * @var string
     * @Assert\NotBlank(message="settings.mail.smtppass.blank", groups = {"smtp"})
     */
    private $smtpPass;
    
    /**
     * @var int
     * @Assert\NotNull(message="settings.mail.smtpport.null", groups = {"smtp"})
     * @Assert\Range(min=1, max=65535, minMessage="settings.mail.smtpport.min", maxMessage="settings.mail.smtpport.max", groups = {"smtp"})
     */
    private $smtpPort;

    /**
     * @var string
     * @Assert\NotBlank(message="settings.mail.security.blank")
     * @Assert\Choice(choices={"ssl", "tls"}, message="settings.mail.security.invalid", strict=true)
     */
    private $smtpSecurity;
    
    /**
     * @var int
     * @Assert\NotNull(message="settings.mail.smtptimeout.null", groups = {"smtp"})
     * @Assert\Range(min=0, minMessage="settings.mail.smtptimeout.min", groups = {"smtp"})
     */
    private $smtpConnectionTimeout;

    /**
     * @var int
     * @Assert\NotNull(message="settings.mail.smtpbind.null", groups = {"smtp"})
     * @Assert\Range(min=0, minMessage="settings.mail.smtpbind.min", groups = {"smtp"})
     */
    private $smtpBindTo;
    
    /**
     * @var bool
     */
    private $smtpPersistentConnection;

    protected function __construct(HttpRequestInterface $request,
            PlaceholderTranslator $translator, array $fields) {
        parent::__construct($request, $translator, $fields);
    }
    
    public static function fromRequest(HttpRequestInterface $request, PlaceholderTranslator $translator) {
        return new SiteSettingsMailFormModel($request, $translator, self::MAP);
    }
    
    protected static function setFromConfig($model,
            PlaceholderTranslator $translator, MooseConfig $config) {
        $env = $config->getCurrentEnvironment();
        $model->setConfigPath($config->getOriginalFile());
        $model->setSystemMailAddress($config->getSystemMailAddress());
        $env->ifMail([
            MooseEnvironment::MAIL_TYPE_PHP => function() use ($model) {
                $model->setMailType('php');
            },
            MooseEnvironment::MAIL_TYPE_SMTP => function(MooseSmtpOptions $smtp) use ($model) {
                /* @var $smtp MooseSmtpOptions */                
                $model->setMailType('smtp');
                $model->setSmtpBindTo($smtp->getBindTo());
                $model->setSmtpConnectionTimeout($smtp->getConnectionTimeout());
                $model->setSmtpHost($smtp->getHost());
                $model->setSmtpPass($smtp->getPassword());
                $model->setSmtpPersistentConnection($smtp->getIsPersistent());
                $model->setSmtpPort($smtp->getPort());
                $model->setSmtpSecurity($smtp->getIsSecure() ? 'ssl' : 'tls');
                $model->setSmtpUser($smtp->getUsername());
            }
        ]);                
    }

    /**
     * @param HttpRequestInterface $request
     * @param PlaceholderTranslator $translator
     * @param MooseConfig $config
     * @return SiteSettingsMailFormModel
     */
    public static function fromConfig(HttpRequestInterface $request,
            PlaceholderTranslator $translator, MooseConfig $config) {
        $model = new SiteSettingsMailFormModel($request, $translator, self::MAP);
        self::setFromConfig($model, $translator, $config);
        return $model;
    }
            
    public function getSystemMailAddress() : string {
        return $this->systemMailAddress;
    }
       
    public function getMailType() : string {
        return $this->mailType;
    }

    public function getSmtpHost() : string {
        return $this->smtpHost;
    }

    public function getSmtpUser() : string {
        return $this->smtpUser;
    }

    public function getSmtpPass() : string {
        return $this->smtpPass;
    }

    public function getSmtpPort() : int {
        return $this->smtpPort;
    }

    public function getSmtpSecurity() : string {
        return $this->smtpSecurity;
    }
    
    public function getSmtpBindTo() : int {
        return $this->smtpBindTo;
    }

    public function getSmtpConnectionTimeout() : int {
        return $this->smtpConnectionTimeout;
    }

    public function getSmtpPersistentConnection() : bool {
        return $this->smtpPersistentConnection;
    }
    
    public function isMailTypePhp() : bool {
        return $this->mailType === 'php';
    }
    
    public function isMailTypeSmtp() : bool {
        return $this->mailType === 'smtp';
    }

    public function isSecuritySsl() : bool {
        return $this->smtpSecurity === 'ssl';
    }
    
    public function isSecurityTls() : bool {
        return $this->smtpSecurity === 'tls';
    }

    public function getConfigPath() : string {
        return $this->configPath;
    }
    
    public function setConfigPath(string $configPath = null) {
        $this->configPath = $configPath ?? '';
        return $this;
    }

    public function setSystemMailAddress(string $systemMailAddress = null) {
        $this->systemMailAddress = $systemMailAddress ?? '';
        return $this;
    }

    public function setMailType(string $mailType = null) {
        $this->mailType = $mailType ?? '';
        return $this;
    }

    public function setSmtpHost(string $smtpHost = null) {
        $this->smtpHost = $smtpHost ?? '';
        return $this;
    }

    public function setSmtpUser(string $smtpUser = null) {
        $this->smtpUser = $smtpUser ?? '';
        return $this;
    }

    public function setSmtpPass(string $smtpPass = null) {
        $this->smtpPass = $smtpPass ?? '';
        return $this;
    }

    public function setSmtpPort(int $smtpPort) {
        $this->smtpPort = $smtpPort;
        return $this;
    }

    public function setSmtpSecurity(string $smtpSecurity = null) {
        $this->smtpSecurity = $smtpSecurity ?? '';
        return $this;
    }

    public function setSmtpConnectionTimeout(int $smtpConnectionTimeout) {
        $this->smtpConnectionTimeout = $smtpConnectionTimeout;
        return $this;
    }

    public function setSmtpBindTo(int $smtpBindTo) {
        $this->smtpBindTo = $smtpBindTo;
        return $this;
    }

    public function setSmtpPersistentConnection(bool $smtpPersistentConnection) {
        $this->smtpPersistentConnection = $smtpPersistentConnection;
        return $this;
    }
    
    protected function getGroups() : array {
        return [$this->mailType];
    }
}
