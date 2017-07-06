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

namespace Moose\Web;

use Moose\Log\Logger;
use Moose\Util\MonoPredicate;
use Requests_Cookie;
use Symfony\Component\DomCrawler\Crawler;
use const MB_CASE_UPPER;
use function mb_convert_case;

/**
 * Implements some convenience methods required by the HttpBotInterface.
 * @author madgaksha
 */
trait HttpBotConvenienceTrait {

    public final function setAutomaticRedirect(bool $automaticRedirect) : HttpBotInterface {
        return $this->setOption(HttpBotInterface::OPTION_AUTOMATIC_REDIRECT, $automaticRedirect);
    }
    public final function enableAutomaticRedirect(): HttpBotInterface {
        return $this->setAutomaticRedirect(true);
    }
    public final function disabledAutomaticRedirect(): HttpBotInterface {
        return $this->setAutomaticRedirect(false);
    }
    public final function setResponseTimeout(int $timeout) : HttpBotInterface {
        return $this->setOption(HttpBotInterface::OPTION_RESPONSE_TIMEOUT, $timeout);
    }
    public final function setConnectTimeout(int $timeout) : HttpBotInterface {
        return $this->setOption(HttpBotInterface::OPTION_CONNECT_TIMEOUT, $timeout);
    }
    public final function setRedirectLimit(int $limit) : HttpBotInterface {
        return $this->setOption(HttpBotInterface::OPTION_REDIRECT_LIMIT, $limit);
    }
    public function setHttpAuth(array $credentials) : HttpBotInterface {
        return $this->setOption(HttpBotInterface::OPTION_HTTP_AUTH, $credentials);
    }
    public function setVerifySSL(bool $verifySSL) : HttpBotInterface {
        return $this->setOption(HttpBotInterface::OPTION_VERIFY_SSL, $verifySSL);
    }
    public function enableVerifySSL() : HttpBotInterface {
        return $this->setVerifySSL(true);
    }
    public function disableVerifySSL() : HttpBotInterface {
        return $this->setVerifySSL(false);
    }
    public function setVerifyName(bool $verifyName) : HttpBotInterface {
        return $this->setOption(HttpBotInterface::OPTION_VERIFY_NAME, $verifyName);
    }
    public function enableVerifyName() : HttpBotInterface {
        return $this->setVerifyName(true);
    }
    public function disableVerifyName() : HttpBotInterface {
        return $this->setVerifyName(false);
    }
    public function setUserAgent(string $userAgent) : HttpBotInterface {
        return $this->setOption(HttpBotInterface::OPTION_USER_AGENT, $userAgent);
    }
    public function setLogger(Logger $logger = null) : HttpBotInterface {
        return $this->setOption(HttpBotInterface::OPTION_LOGGER, $logger);
    }
    public function disableLogger() : HttpBotInterface {
        return $this->setLogger(null);
    }
    public function setLogBody(bool $logBody) : HttpBotInterface {
        return $this->setOption(HttpBotInterface::OPTION_LOG_BODY, $logBody);
    }
    public function enableLogBody() : HttpBotInterface {
        return $this->setLogBody(true);
    }
    public function disableLogBody() : HttpBotInterface {
        return $this->setLogBody(false);
    }
    public function setRewrite302ToGet(bool $rewrite302ToGet) : HttpBotInterface {
        return $this->setOption(HttpBotInterface::OPTION_REWRITE_302_TO_GET, $rewrite302ToGet);
    }
    public function enableRewrite302ToGet() : HttpBotInterface {
        return $this->setRewrite302ToGet(true);
    }
    public function disableRewrite302ToGet() : HttpBotInterface {
        return $this->setRewrite302ToGet(false);
    }

    public final function get(string $url, array $data = [], array $headers = [],
            array $options = []): HttpBotInterface {
        return $this->request($url, HttpBotInterface::HTTP_GET, $data, $headers, $options);
    }
    public final function post(string $url, array $data = [], array $headers = [],
            array $options = []): HttpBotInterface {
        return $this->request($url, HttpBotInterface::HTTP_POST, $data, $headers, $options);
    }
    public final function delete(string $url, array $data = [], array $headers = [],
            array $options = []): HttpBotInterface {
        return $this->request($url, HttpBotInterface::HTTP_DELETE, $data, $headers, $options);
    }
    public final function put(string $url, array $data = [], array $headers = [],
            array $options = []): HttpBotInterface {
        return $this->request($url, HttpBotInterface::HTTP_PUT, $data, $headers, $options);
    }
    public final function head(string $url, array $data = [], array $headers = [],
            array $options = []): HttpBotInterface {
        return $this->request($url, HttpBotInterface::HTTP_HEAD, $data, $headers, $options);
    }
    public final function patch(string $url, array $data = [], array $headers = [],
            array $options = []): HttpBotInterface {
        return $this->request($url, HttpBotInterface::HTTP_PATCH, $data, $headers, $options);
    }
    public final function options(string $url, array $data = [], array $headers = [],
            array $options = []): HttpBotInterface {
        return $this->request($url, HttpBotInterface::HTTP_OPTIONS, $data, $headers, $options);
    }
    public final function trace(string $url, array $data = [], array $headers = [],
            array $options = []): HttpBotInterface {
        return $this->request($url, HttpBotInterface::HTTP_TRACE, $data, $headers, $options);
    }
    
    public final function resetData() : HttpBotInterface {
        $emptyArray = [];
        return $this->setData($emptyArray);
    }
    public final function resetHeaders() : HttpBotInterface {
        $emptyArray = [];
        return $this->setHeaders($emptyArray);
    }
    
    
    public function checkResponseCode(MonoPredicate $predicate) : bool {
        if (!$predicate->check($this->getResponseCode())) {
            return false;
        }
        return true;
    }
    public function checkResponsePath(MonoPredicate $predicate): bool {
        if (!$predicate->check($this->getResponsePath())) {
            return false;
        }
        return true;
    }
    
    public function assertResponseCode(MonoPredicate $predicate) : HttpBotInterface {
        if ($this->checkResponseCode($predicate) === false) {
            $isCode = $this->getResponseCode();
            throw new HttpBotException("Expected response code $expectedCode does not match actual response code $isCode", 'Assertion failure');
        }
        return $this->getThis();
    }
    public function assertResponsePath(MonoPredicate $predicate): HttpBotInterface {
        if ($this->checkResponsePath($predicate) === false) {
            $isPath = $this->getResponsePath();
            throw new HttpBotException("Expected response path $expectedPath does not match actual response path $isPath", 'Assertion failure');
        }
        return $this->getThis();
    }
    
    public function ifResponseCode(MonoPredicate $predicate, $callback) : HttpBotInterface {
        if ($this->checkResponseCode($predicate)) {
            $this->addReturn(\call_user_func($callback, $this));
        }
        else {
            $this->addReturn(null);
        }
        return $this->getThis();
    }
    public function ifResponsePath(MonoPredicate $predicate, $callback) : HttpBotInterface {
        if ($this->checkResponsePath($predicate)) {
            $this->addReturn(\call_user_func($callback, $this));
        }
        else {
            $this->addReturn(null);
        }
        return $this->getThis();
    }
    
    public function selectOne(string $selector, $callback): HttpBotInterface {
        return $this->selectMulti($selector, $callback, 1);
    }
    public function selectOneDom(string $selector, $callback) : HttpBotInterface {
        return $this->selectMultiDom($selector, $callback, 1);
    }
    public function selectMultiDom(string $selector, $callback, int $expectedCount = -1) : HttpBotInterface {
        return $this->selectMulti($selector, function(Crawler $crawler) use ($callback) {
            /* @var $crawler Crawler */
            $nodes = [];
            for ($i = 0; $i < $crawler->count(); ++$i) {
                $nodes []= $crawler->getNode($i);
            }
            return \call_user_func($callback, $nodes);
        }, $expectedCount);
    }
    
    public function submitForm(string $selector,
            string $submitButtonSelector = null, array $values = [],
            array $headers = [], array $options = []) : HttpBotInterface {
        $config = $this
                ->selectOne($selector, function(Crawler $form) use ($values, $submitButtonSelector) : array {
                    /* @var $form Crawler */
                    switch (mb_convert_case($form->attr('method'), MB_CASE_UPPER)) {
                        case 'DELETE':
                            $method = HttpBotInterface::HTTP_DELETE; break;
                        case 'HEAD':
                            $method = HttpBotInterface::HTTP_HEAD; break;
                        case 'OPTIONS':
                            $method = HttpBotInterface::HTTP_OPTIONS; break;
                        case 'PATCH':
                            $method = HttpBotInterface::HTTP_PATCH; break;
                        case 'PUT':
                            $method = HttpBotInterface::HTTP_PUT; break;
                        case 'TRACE':
                            $method = HttpBotInterface::HTTP_TRACE; break;
                        case 'GET':
                            $method = HttpBotInterface::HTTP_GET; break;
                        case 'POST':
                        default:
                            $method = HttpBotInterface::HTTP_POST; break;
                    }
                    $htmlForm = $form->form($values);
                    $values = $htmlForm->getPhpValues();
                    if ($submitButtonSelector !== null) {
                        $submitButton = $form->filter($submitButtonSelector);
                        if ($submitButton->count() !== 1) {
                            $this->getLogger()->log($submitButtonSelector, 'Cannot submit form, submit button not found', Logger::LEVEL_ERROR);
                            throw new HttpBotException("Cannot submit form, submit button $submitButtonSelector not found.");
                        }
                        $submitButtonName = $submitButton->attr('name');
                        if (!empty($submitButtonName)) {
                            $values[$submitButton->attr('name')] = $submitButton->attr('value') ?? '';
                        }
                        else {
                            $this->getLogger()->log($submitButtonSelector, "Cannot submit submit button, it has got no name", Logger::LEVEL_WARNING);
                        }
                    }
                    return [
                        'action' => $htmlForm->getUri(),
                        'method' => $method,
                        // Merge with values that may have been set already on the form.
                        'values' => $values
                    ];
                })
                ->popReturn();
        return $this->request($config['action'], $config['method'], $config['values'], $headers, $options);
    }
    
    public function findCookieOne($predicate, $callback) : HttpBotInterface {
        return $this->findCookieMulti($predicate, function(array $cookies) use ($callback) {
            return \call_user_func($callback, $cookies[0]);
        }, 1);
    }
    
    public function getCookieOne(array $searchCriteria, $callback) : HttpBotInterface {
        return $this->getCookieMulti($searchCriteria, function(array $cookies) use ($callback) {
            return \call_user_func($callback, $cookies[0]);
        }, 1);
    }
    
    public function getCookieMulti(array $searchCriteria, $callback, int $expectedCount = -1) : HttpBotInterface {
        return $this->findCookieMulti(function(Requests_Cookie $cookie) use (&$searchCriteria) {
            return self::matchCookie($cookie, $searchCriteria);
        }, $callback, $expectedCount);
    }
    
    public function deleteCookies(array $searchCriteria) : HttpBotInterface {
        return $this->clearCookies(function(Requests_Cookie $cookie) use (&$searchCriteria) {
            return self::matchCookie($cookie, $searchCriteria);
        });
    }

    private static function matchCookie(Requests_Cookie $cookie, array & $searchCriteria) : bool {
        /* @var $cookie Requests_Cookie */
        if (isset($searchCriteria['name']) && $searchCriteria['name'] !== $cookie->name) {
            return false;
        }
        if (isset($searchCriteria['value']) && $searchCriteria['value'] !== $cookie->value) {
            return false;
        }
        if (isset($searchCriteria['domain']) && !$cookie->domain_matches($searchCriteria['domain'])) {
            return false;
        }
        if (isset($searchCriteria['path']) && !$cookie->path_matches($searchCriteria['path'])) {
            return false;
        }
        if (isset($searchCriteria['expired'])) {
            if (!$searchCriteria['expired'] === $cookie->is_expired()) {
                return false;
            }
        }
        return true;
    }
    
    protected abstract function getThis() : HttpBotInterface;
    protected abstract function addReturn($value);
    protected abstract function popReturn();
    protected abstract function getLogger() : Logger;
    
    public abstract function clearCookies($predicate = null): HttpBotInterface;
    public abstract function findCookieMulti($predicate, $callback, int $expectedCount) : HttpBotInterface;
    public abstract function selectMulti(string $selector, $callback, int $expectedCount = -1) : HttpBotInterface;
    public abstract function getResponseUrl(): string;
    public abstract function getResponsePath(): string;
    public abstract function getResponseCode() : int;
    public abstract function setHeaders(array & $keyValueMap) : HttpBotInterface;
    public abstract function setData(array & $keyValueMap) : HttpBotInterface;
    public abstract function request(string $url,
            int $method = HttpBotInterface::HTTP_GET, array $data = [],
            array $headers = [], array $options = []) : HttpBotInterface;
    public abstract function setOption(int $option, $value) : HttpBotInterface;
}
