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

namespace Moose\Extension\Opal;

use Doctrine\DBAL\Types\ProtectedString;
use Moose\Log\Logger;
use Moose\Util\MonoPredicate as M;
use Moose\Web\HttpBot;
use Moose\Web\HttpBotException;
use Moose\Web\HttpBotInterface;
use Requests_Cookie;
use Symfony\Component\DomCrawler\Crawler;
use Throwable;

/**
 * Implements an OPAL session.
 * @author madgaksha
 */
class OpalSession implements OpalSessionInterface {
    /** @var HttpBotInterface */
    private $bot;   
    
    /** @var OpalAuthorizationProviderInterface */
    private $authorizationProvider;
    
    /** @var OpalFiletreeReaderInterface */
    private $fileTreeReader;
    
    /** @var Logger */
    private $logger;

    const URL_OPAL = 'https://bildungsportal.sachsen.de';
    const PATH_OPAL_LOGIN = '/opal/login';
    const PATH_OPAL_HOME = '/opal/home';
    const URL_OPAL_LOGIN = self::URL_OPAL . self::PATH_OPAL_LOGIN;
    const URL_OPAL_HOME = self::URL_OPAL . self::PATH_OPAL_HOME;
    
    const COOKIE_JSESSIONID = [
        'name' => 'JSESSIONID',
        'domain' => 'bildungsportal.sachsen.de',
        'path' => '/opal/'
    ];
    
    const SELECTOR_LOGINFORM = '.login-form form';
    const SELECTOR_LOGINFORM_BUTTON = 'button';
    const SELECTOR_LOGINFORM_OPTION = '.login-form form select[name=wayfselection] option';

    private function __construct(OpalAuthorizationProviderInterface $authorizationProvider, Logger $logger = null) {
        $this->authorizationProvider = $authorizationProvider;
        $this->logger = $logger;
        $this->bot = (new HttpBot())
            ->setLogger($logger)
            ->enableLogBody()
            ->setRedirectLimit(15)
            ->setUserAgent('Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/59.0.3071.115 Safari/537.36')
            ->enableRewrite302ToGet()
            ->disableVerifySSL();
        $this->fileTreeReader = new OpalFiletreeReader($this);
    }
    
    public function getFiletreeReader() : OpalFiletreeReaderInterface {
        return $this->fileTreeReader;
    }

    public function store(): ProtectedString {
        return $this->tryAgainIfFailure(function() {
            return $this->bot
                    ->getCookieOne(self::COOKIE_JSESSIONID, function(Requests_Cookie $cookie) {
                        return new ProtectedString($cookie->value);
                    })
                    ->getReturn();
        });
    }

    public function restore(ProtectedString $serializedData): OpalSessionInterface {
        $this->logger->log('Restoring session', null, Logger::LEVEL_DEBUG);
        $this->bot
                ->clearCookies()
                ->clearReturn()
                ->addCookie(
                        self::COOKIE_JSESSIONID['name'],
                        $serializedData->getString(),
                        time() + 24*60*60*1000,
                        self::COOKIE_JSESSIONID['path'],
                        self::COOKIE_JSESSIONID['domain'],
                        true, // sslOnly
                        true, // httpOnly
                        true); // hostOnly
        return $this->assertLogin();
    }
    
    public function logout() {
        $this->bot->clearCookies();
        $this->bot->clearReturn();
        $this->bot
            ->head(self::URL_OPAL_LOGIN)
            ->ifResponsePath(M::startsWith(self::PATH_OPAL_HOME), function() {
                // TODO: We are still logged in, press logout button.
                $this->logger->log('Still logged in, attempting logout', null, Logger::LEVEL_INFO);
            });
    }
    
    /**
     * Try the given action, and if it fails, login and attempt the action again.
     * @param callable|array|string $callback
     * @return mixed The return value of the callback.
     * @throws OpalException
     */
    private function tryAgainIfFailure($callback) {
        $this->assertLogin();
        try {
            return \call_user_func($callback);
        } catch (OpalException $opalException) {
            $this->logger->log($opalException, "Action failed once, trying again", Logger::LEVEL_ERROR);
            $this->assertLogin();
            return \call_user_func($callback);
        }
        catch (HttpBotException $botException) {
            $this->logger->log($botException, "Action failed once due to a network error, trying again", Logger::LEVEL_ERROR);
            $this->assertLogin();
            return \call_user_func($callback);
        }
        catch (\Throwable $t) {
            $this->logger->log($t, "Action failed once due to an uncaught error, trying again", Logger::LEVEL_ERROR);
            throw new OpalException('Unhandled exception occured while performing action.', $t);
        }
    }
    
    private function assertLogin() : OpalSessionInterface {
        $this->bot
                ->head(self::URL_OPAL_LOGIN)
                ->ifResponsePath(M::startsWith(self::PATH_OPAL_LOGIN), function() {
                    $this->logger->log('Not logged in, attempting login', null, Logger::LEVEL_INFO);
                    $this->performLogin();
                })
                ->assertResponsePath(M::startsWith(self::PATH_OPAL_HOME));
        return $this;
    }
    
    private function performLogin() {
        $this->bot->clearCookies();
        $this->bot->clearReturn();
        $this->preAuth();
        $this->authorizationProvider->perform($this->bot, $this->logger);
        $this->postAuth();
    }

    private function preAuth() {
        $url = $this->bot->checkResponsePath(M::startsWith(self::PATH_OPAL_LOGIN)) ? $this->bot->getResponseUrl() : self::URL_OPAL_LOGIN;
        $this->bot
            ->get($url)
            // Find the the institution matching our authentication provider.
            ->selectMulti(self::SELECTOR_LOGINFORM_OPTION, function(Crawler $elements) {
                $elements = $elements->reduce(function(Crawler $element) {
                    return $this->authorizationProvider->matches($element->attr('value'), $element->text());
                });
                if ($elements->count() !== 1) {
                    $name = \get_class($this->authorizationProvider);
                    $this->logger->log("OPAL login failed, did not find an institution matching the authorization provider $name", null, Logger::LEVEL_ERROR);
                    throw new OpalAuthorizationException("OPAL login failed, did not find an institution matching the authorization provider $name");
                }
                $value = $elements->attr('value');
                $this->logger->log($value, 'Found institution value', Logger::LEVEL_INFO);
                return $value;
            })
            ->submitForm(self::SELECTOR_LOGINFORM, self::SELECTOR_LOGINFORM_BUTTON, [
                'wayfselection' => $this->bot->getReturn(),
            ]);
    }

    private function postAuth() {
        $this->bot->head(self::URL_OPAL_HOME);
    }
    
    public static function open(OpalAuthorizationProviderInterface $authorizationProvider, $callback, Logger $logger = null) {
        $session = new OpalSession($authorizationProvider, $logger);
        try {
            \call_user_func($callback, $session);
        }
        catch (OpalException $e) {
            throw $e;
        }
        catch (Throwable $t) {
            throw new OpalException('Unhandled exception occured during the OPAL session', $t);
        }
        finally {
            $session->logout();
        }
    }
}