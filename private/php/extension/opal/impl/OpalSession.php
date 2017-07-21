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
    
    private $server;

    const URL_OPAL = 'https://bildungsportal.sachsen.de';
    const PATH_OPAL_LOGIN = '/opal/login';
    const PATH_OPAL_HOME = '/opal/home';
    const URL_OPAL_LOGIN = self::URL_OPAL . self::PATH_OPAL_LOGIN;
    const URL_OPAL_HOME = self::URL_OPAL . self::PATH_OPAL_HOME;
    const REGEX_LOGOUT_LINK = '/\/opal\/home\/?(?:\?\d+-)?\d+[\.\w-]+-headerFunction-logoutLink/i';
    
    const COOKIE_JSESSIONID = [
        'name' => 'JSESSIONID',
        'domain' => 'bildungsportal.sachsen.de',
        'path' => '/opal/'
    ];
    
    const SELECTOR_LOGINFORM = '.login-form form';
    const SELECTOR_LOGINFORM_BUTTON = 'button';
    const SELECTOR_LOGINFORM_OPTION = '.login-form form select[name=wayfselection] option';

    private function __construct(OpalAuthorizationProviderInterface $authorizationProvider, Logger $logger = null) {
        $this->server = '';
        $this->authorizationProvider = $authorizationProvider;
        $this->logger = $logger;
        $this->bot = (new HttpBot())
            ->setLogger($logger)
            ->setRedirectLimit(15)
            ->setUserAgent('Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/59.0.3071.115 Safari/537.36')
            ->enableRewrite302ToGet()
            ->disableVerifySSL();
        $this->fileTreeReader = new OpalFiletreeReader($this, $this->bot, $logger);
    }
    
    public function getFiletreeReader() : OpalFiletreeReaderInterface {
        return $this->fileTreeReader;
    }

    public function store(): ProtectedString {
        return $this->tryAgainIfFailure(function() {
            $opal = $this->bot
                    ->getCookieOne(self::COOKIE_JSESSIONID, function(Requests_Cookie $cookie) {
                        return new ProtectedString($cookie->value);
                    })
                    ->getReturn();
            $auth = $this->authorizationProvider->store($this->bot, $this->logger);
            return new ProtectedString(\json_encode([
                'opal' => $opal->getString(),
                'auth' => $auth->getString(),
                'provider' => \get_class($this->authorizationProvider)
            ]));
        });
    }

    public function restore(ProtectedString $serializedData): ProtectedString {
        if (ProtectedString::isEmpty($serializedData)) {
            return new ProtectedString(null);
        }
        $this->logger->debug('Attempting to restore session', null);
        $sessionData = \json_decode($serializedData->getString());
        if (!$sessionData instanceof \stdClass) {
            $this->logger->error('Invalid session data, must be as returned by store.');
            throw new OpalException('Cannot restore session, invalid session data');
        }
        $opal = new ProtectedString($sessionData->opal ?? '');
        $auth = new ProtectedString($sessionData->auth ?? '');
        $provider = $sessionData->provider ?? '';
        $expectedProvider = \get_class($this->authorizationProvider);
        if ($expectedProvider !== $provider) {
            $this->logger->error($provider, "Cannot restore session, expected authorization provider $expectedProvider");
            throw new OpalException("Cannot restore session, expected authorization provider $expectedProvider, but found $provider");
        }
        $this->clear();
        // Restore our OPAL session by adding the JSESSIONID cookie.
        if (!ProtectedString::isEmpty($opal)) {
            $this->restoreSession($opal);
        }
        // Restore authorization provider session.
        if (!ProtectedString::isEmpty($auth)) {
            $this->authorizationProvider->restore($this->bot, $auth, $this->logger);
        }
        $session = $this->store();
        $this->logger->debug('Session restored successfully');
        return $session;
    }
    
    public function logout() {
        $this->bot
            ->head(self::URL_OPAL_LOGIN)
            ->always([$this, 'getCurrentId'])
            ->ifResponsePath(M::startsWith(self::PATH_OPAL_HOME), function() {
                $this->logger->log('Still logged in to OPAL, attempting logout', null, Logger::LEVEL_INFO);
                $this->performLogout();
            });
        $this->clear();
    }
    
    private function performLogout() {
        $this->bot
                ->get($this->url(self::URL_OPAL_HOME))
                ->always([$this, 'getCurrentId'])
                ->selectMulti('script', function(Crawler $crawler){
                    $link = null;
                    $crawler = $crawler->reduce(function(Crawler $script) use (& $link) {
                        $matches = [];
                        if (1 === \preg_match(self::REGEX_LOGOUT_LINK, $script->text(), $matches)) {
                            $link = $matches[0];
                            return true;
                        }
                        return false;
                    });
                    return $crawler->count() === 1 ? $link : null;
                })
                ->ifNonNullReturn(function(string $logoutLink, HttpBotInterface $bot) {
                    $this->logger->log($logoutLink, 'Found logout link', Logger::LEVEL_DEBUG);
                    $bot
                        ->get(self::URL_OPAL . $logoutLink)
                        ->always([$this, 'getCurrentId']);
                }, function() {
                    $this->logger->log('Failed to logout, did not find one logout link.', null, Logger::LEVEL_WARNING);                    
                });
    }
    
    /**
     * Try the given action, and if it fails, login and attempt the action again.
     * @param callable|array|string $callback
     * @return mixed The return value of the callback.
     * @throws OpalException
     */
    public function tryAgainIfFailure($callback) {
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
        catch (Throwable $t) {
            $this->logger->log($t, "Action failed once due to an uncaught error, trying again", Logger::LEVEL_ERROR);
            throw new OpalException('Unhandled exception occured while performing action.', $t);
        }
    }
    
    public function getBot() : HttpBotInterface {
        return $this->bot;
    }
    
    public function getLogger() : Logger {
        return $this->logger;
    }

    private function assertLogin() : OpalSessionInterface {
        $this->bot
                ->head(self::URL_OPAL_LOGIN)
                ->always([$this, 'getCurrentId'])
                ->ifResponsePath(M::startsWith(self::PATH_OPAL_LOGIN), function() {
                    $this->logger->log('Not logged in, attempting login', null, Logger::LEVEL_INFO);
                    $this->performLogin();
                })
                ->assertResponsePath(M::startsWith(self::PATH_OPAL_HOME));
        return $this;
    }
    
    private function performLogin() {
        $this->clear();
        $this->preAuth();
        $this->authorizationProvider->perform($this->bot, $this->logger);
        $this->postAuth();
    }

    private function preAuth() {
        $this->bot
            ->get($this->url(self::URL_OPAL_LOGIN))
            ->always([$this, 'getCurrentId'])
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
        $this->bot
                ->head(self::URL_OPAL_LOGIN)
                ->always([$this, 'getCurrentId']);
    }
    
    public static function open(OpalAuthorizationProviderInterface $authorizationProvider, $callback, Logger $logger = null, bool $leaveOpen = false) {
        $session = new OpalSession($authorizationProvider, $logger);
        try {
            return \call_user_func($callback, $session);
        }
        catch (OpalException $e) {
            $logger->error($t, "Opal exception occured during the OPAL session");
            throw $e;
        }
        catch (Throwable $t) {
            $class = \get_class($t);
            $logger->error($t, "Unhandled exception occured during the OPAL session");
            throw new OpalException("Unhandled exception occured during the OPAL session ($class)", $t);
        }
        finally {
            if (!$leaveOpen) {
                $session->logout();
            }
        }
    }
    
    public function getCurrentId(HttpBotInterface $bot) {
        $string = $this->bot->getResponseQueryString();
        $matches = [];
        if (1 === \preg_match('/^\d+/', $string, $matches)) {
            $this->server = $matches[0];;
        }
        else {
            $this->server = '';
        }
        $this->logger->log($this->server, "Changed current server id", Logger::LEVEL_DEBUG);
    }
      
    public function url(string $url) : string {
        return $url . '?' . $this->server;
    }

    public function clear() {
        $this->bot->clearCookies();
        $this->bot->clearReturn();
        $this->server = '';
    }

    private function restoreSession($opal) {
        $this->bot
        ->addCookie(
                self::COOKIE_JSESSIONID['name'],
                $opal->getString(),
                time() + 24*60*60,
                self::COOKIE_JSESSIONID['path'],
                self::COOKIE_JSESSIONID['domain'],
                true, // sslOnly
                true, // httpOnly
                false); // hostOnly
    }
}