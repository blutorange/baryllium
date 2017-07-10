<?php
declare(strict_types = 1);
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

namespace Moose\Extension\Opal;

use Doctrine\DBAL\Types\ProtectedString;
use Moose\Log\Logger;
use Moose\Util\MonoPredicate as M;
use Moose\Util\PlaceholderTranslator;
use Moose\Web\HttpBotInterface as Bot;
use Requests_Cookie;

/**
 * Authorization via https://idp.ba-dresden.de/idp/profile/Shibboleth/SSO
 *
 * @author madgaksha
 */
class OpalBaDresden implements OpalAuthorizationProviderInterface {
    /** @var string */
    private $username;
    /** @var ProtectedString */
    private $password;
    
    const PATH_SSO = '/idp/profile/Shibboleth/SSO';
    const PATH_USER_PASSWORD = '/idp/Authn/UserPassword';
    const SELECTOR_LOGIN_FORM = '.loginbox form';
    const SELECTOR_SAML_FORM = 'form';
    
    const COOKIE_IDP_AUTHN_LC_KEY = [
        'name'   => '_idp_authn_lc_key',
        'domain' => 'idp.ba-dresden.de',
        'path'   => '/idp'
    ];
    
    public function __construct(string $username, ProtectedString $password) {
        $this->username = $username;
        $this->password = $password;
    }
    
    public function perform(Bot $bot, Logger $logger) {
        try {
            $bot
                ->ifResponsePath(M::startsWith(self::PATH_USER_PASSWORD), function(Bot $bot) {
                    $bot->submitForm(self::SELECTOR_LOGIN_FORM, null, [
                        'j_username' => $this->username,
                        'j_password' => $this->password->getString()
                    ]);
                })
                ->assertResponsePath(M::startsWith(self::PATH_SSO), OpalAuthorizationException::class)
                ->submitForm(self::SELECTOR_SAML_FORM);
        }
        catch (\Throwable $e) {
            // Hide password from stacktrace.
            $class = \get_class($e);
            $logger->error($e->getMessage(), "Failed to authorize with BADresden ($class)");
            throw new $class($e->getMessage());
        }
    }

    public function getName(PlaceholderTranslator $translator): string {
        return $translator->gettext('university.name.badresden');
    }

    public function matches(string $value, string $text): bool {
        return \preg_match('/BA Dresden/i', $text) === 1;
    }

    public function getNativeName(): string {
        return "Berufsakademie Sachsen, Staatliche Studienakademie Dresden";
    }
    
    public function restore(Bot $bot, ProtectedString $storedSession, Logger $logger) {
        if (!$storedSession->_isEmpty()) {
            $logger->log('Restoring previous IDP BA Dresden session...', null, Logger::LEVEL_DEBUG);
            $bot->addCookie(
                    self::COOKIE_IDP_AUTHN_LC_KEY['name'],
                    $storedSession->getString(),
                    time() + 24*60*60*1000,
                    self::COOKIE_IDP_AUTHN_LC_KEY['path'],
                    self::COOKIE_IDP_AUTHN_LC_KEY['domain'],
                    true,
                    false,
                    true);
        }
    }
    
    public function store(Bot $bot, Logger $logger) : ProtectedString  {
        return $bot
            ->getCookieOne(self::COOKIE_IDP_AUTHN_LC_KEY, function(Requests_Cookie $cookie) use ($logger) {
                $logger->log('Storing current IDP BA Dresden session...', null, Logger::LEVEL_DEBUG);
                return new ProtectedString($cookie->value);
            })
            ->getReturn();
    }
}