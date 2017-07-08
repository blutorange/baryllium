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

use InvalidArgumentException;
use Moose\Log\Logger;
use Moose\Util\MonoPredicate;
use Requests;
use Requests_Cookie;
use Requests_Cookie_Jar;
use Requests_Exception;
use Requests_IRI;
use Requests_Response;
use Symfony\Component\DomCrawler\Crawler;

/**
 * An implementation of HttpBotImpl using Requests. 
 * @uses Requests For providing HTTP requests support.
 * @author madgaksha
 */
class HttpBot implements HttpBotInterface {
    use HttpBotCookiesTrait;
    use HttpBotDomTrait;
    use HttpBotOptionsTrait;
    use HttpBotPredicatesTrait;
    use HttpBotRequestsTrait;
    use HttpBotResponseTrait;
    
    /** @var array */
    private $data;
    
    /** @var array */
    private $headers;
    
    /** @var array */
    private $options;
    
    /** @var array */
    private $return;
    
    /** @var Requests_Response|null */
    private $response;
    
    /** @var Crawler|null */
    private $crawler;
    
    /** @var \Requests_Cookie_Jar[] */
    private $cookies;
    
    /** @var \Requests_IRI|null */
    private $iri;
    
    /** @var Logger */
    private $logger;
    
    /** @var bool */
    private $logBody;
    
    /** @var bool */
    private $rewrite302ToGet;
    
    /** @var HttpBotHooks */
    private $hooks;
    
    /** @var array */
    private $query;

    public function __construct() {
        $this->data = [];
        $this->headers = [];
        $this->logger = Logger::none();
        $this->logBody = false;
        $this->rewrite302ToGet = false;
        $this->cookies = [];
        $this->hooks = new HttpBotHooks();
        $this->options = [
            'timeout' => 10,
            'connect_timeout' => 10,
            'useragent' => 'Mozilla/5.0 (X11; Linux x86_64; rv:10.0) Gecko/20100101 Firefox/10.0',
            'follow_redirects' => true,
            'redirects' => 10,
            'blocking' => true,
            'filename' => false,
            'auth' => false,
            'proxy' => false,
            'max_bytes' => 10000000,
            'idn' => true,
            'verify' => true,
            'verifyname' => true,
            'data_format' => 'query',
        ];
    }

    public function request(string $url,
            string $method = HttpBotInterface::HTTP_GET, array $data = [],
            array $headers = [], array $options = []): HttpBotInterface {
        // Merge custom options with global options.
        $iri = new Requests_IRI($url);
        $requestData = \array_merge($data, $this->data);
        $requestHeaders = \array_merge($headers, $this->headers);
        $requestOptions = \array_merge($options, $this->options);
        $this->assertMethod($method);
        if (!isset($this->cookies[$iri->host])) {
            $this->cookies[$iri->host] = new \Requests_Cookie_Jar();
        }
        $requestOptions['cookies'] = $this->cookies[$iri->host];
        $requestOptions['hooks'] = $this->initHooks($this->hooks);
        try {
            $response = Requests::request($url, $requestHeaders, $requestData, $method, $requestOptions);
        }
        catch (Requests_Exception $e) {
            $this->logger->log($e, "Request failed", Logger::LEVEL_ERROR);
            throw new HttpBotException('Request failed: ' . $e->getMessage(), $e->getType(), $e);
        }
        if (!$response->success) {
            $this->logger->log("No response", "Request failed", Logger::LEVEL_ERROR);            
            throw new HttpBotException('Request failed', 'Net failure');
        }
        $this->response = $response;
        $this->crawler = null;
        $this->query = null;
        $this->iri = null;
        return $this;
    }
    
    public function addDatum(string $key, string $value): HttpBotInterface {
        $this->data[$key] = $value;
        return $this;
    }

    public function addHeader(string $key, string $value): HttpBotInterface {
        $this->headers[$key] = $value;
        return $this;
    }

    public function setData(array & $keyValueMap): HttpBotInterface {
        $this->data = $keyValueMap;
        return $this;
    }

    public function setHeaders(array & $keyValueMap): HttpBotInterface {
        $this->headers = $keyValueMap;
        return $this;
    }

    public function setOption(int $option, $value): HttpBotInterface {
        switch ($option) {
            case self::OPTION_AUTOMATIC_REDIRECT:
                $this->options['follow_redirects'] = !!$value;
                return $this;
            case self::OPTION_CONNECT_TIMEOUT:
                $this->options['connect_timeout'] = \floatval($value);
                return $this;
            case self::OPTION_HTTP_AUTH:
                if ($value === false || empty($value)) {
                    $this->options['auth'] = false;
                    return $this;
                }
                else if (\is_array($value)) {
                    if (\sizeof($value) === 2) {
                        $this->options['auth'] = $value;
                        return $this;                
                    }
                }
                throw new InvalidArgumentException('Bad value for option HTTP_AUTH, expected either false or an array with two entries (username, password).');
            case self::OPTION_REDIRECT_LIMIT:
                $this->options['redirects'] = \intval($value);
                return $this;
            case self::OPTION_RESPONSE_TIMEOUT:
                $this->options['timeout'] = \floatval($value);
                return $this;       
            case self::OPTION_VERIFY_NAME:
                $this->options['verifyname'] = !!$value;
                return $this;
            case self::OPTION_VERIFY_SSL:
                $this->options['verify'] = !!$value;
                return $this;
            case self::OPTION_USER_AGENT:
                $this->options['useragent'] = (string)$value;
                return $this;
            case self::OPTION_LOGGER:
                $this->logger = $value ?? Logger::none();
                return $this;
            case self::OPTION_LOG_BODY:
                $this->logBody = !!$value;
                return $this;
            case self::OPTION_REWRITE_302_TO_GET:
                $this->rewrite302ToGet = !!$value;
                return $this;
            default:
                $this->logger->log($option, 'No such option name', Logger::LEVEL_ERROR);
                throw new HttpBotException('No such option: ' . $option, 'Invalid argument');
        }
    }
   
    public function getResponseUrl(): string {
        return $this->assertResponse()->url;
    }
    
    public function & getResponseBody(): string {
        return $this->assertResponse()->body;
    }
   
    public function selectMulti(string $selector, $callback, int $expectedCount = -1): HttpBotInterface {
        $result = $this->getCrawler()->filter($selector);
        $count = $result->count();
        if ($expectedCount > -1 && $count !== $expectedCount) {
            $this->logger->log($count, "Expected exactly $expectedCount match(es) for selector $selector, but got", Logger::LEVEL_ERROR);
            throw new HttpBotException("Expected exactly $expectedCount match(es) for selector $selector, but got $count", 'Expectation violated');
        }
        $this->addReturn(\call_user_func($callback, $result));
        return $this;
    }
    
    public function clearReturn() : HttpBotInterface {
        $this->return = [];
        return $this;
    }

    public function getReturn(int $position = -1) {
        $size = \sizeof($this->return);
        if ($size === 0) {
            $this->logger->log('Cannot get return value, there are none.', null, Logger::LEVEL_ERROR);
            throw new HttpBotException('Cannot get return value, there are none.');
        }
        $normalizedPosition = $position % $size;
        if ($normalizedPosition < 0) {
            $normalizedPosition += $size;
        }
        if ($normalizedPosition >= $size) {
            $this->logger->log($position, 'No such index', Logger::LEVEL_ERROR);
            throw new HttpBotException("Cannot get return value at index $position, it does not exist.");
        }
        return $this->return[$normalizedPosition];
    }

    public function addCookie(string $name, string $value, int $expire = 0,
            string $path = '/', string $domain = null, bool $secure = false,
            bool $httpOnly = true, bool $hostOnly = false): HttpBotInterface {
        $domain = $domain ?? $this->getResponseIri()->ihost;
        $attributes = [
            'httponly' => !!$httpOnly,
            'secure' => !!$secure,
            'expires' => \intval($expire),
            'max-age' => \intval($expire)
        ];
        if (!empty($domain)) {
            $attributes['domain'] = $domain;
        }
        if (!empty($path)) {
            $attributes['path'] = $path;
        }
        $flags = [
            'host-only' => !!$hostOnly
        ];
        $cookie = new Requests_Cookie($name, $value, $attributes, $flags, null);
        $this->cookies[$domain][$name] = $cookie;
        return $this;
    }

    public function clearCookies($predicate = null): HttpBotInterface {
        if ($predicate === null) {
            $this->cookies = [];
        }
        else {
            \array_map(function(\Requests_Cookie_Jar $jar) use ($predicate) {
                return \array_filter($jar->getIterator()->getArrayCopy(), function(Requests_Cookie $cookie) use ($predicate) {
                    return \call_user_func($predicate);
                });
            }, $this->cookies);
        }
        return $this;
    }
    
    public function findCookieMulti($predicate, $callback, int $expectedCount = -1) : HttpBotInterface {
        $matches = [];
        foreach ($this->cookies as $domain => $jar) {
            foreach ($jar as $name => $cookie) {
                /* @var $cookie Requests_Cookie */
                if (\call_user_func($predicate, $cookie)) {
                    $matches []= $cookie;
                }
            }
        }
        $count = \sizeof($matches);
        if ($expectedCount >= 0 && $count !== $expectedCount) {
            $this->logger->log($count, "Expected to find exactly $expectedCount cookies, but got", Logger::LEVEL_ERROR);
            throw new HttpBotException("Expected to find exactly $expectedCount cookies, but got $count", 'Expectation violated');
        }
        $this->addReturn(\call_user_func($callback, $matches));
        return $this;
    }
    
    protected function getThis(): HttpBotInterface {
        return $this;
    }
    
    protected function getLogger(): Logger {
        return $this->logger;
    }

    protected function addReturn($value) {
        $this->return []= $value;
    }
    
    protected function popReturn() {
        return \array_pop($this->return);
    }

    private function getCrawler() {
        if ($this->crawler === null) {
            $response = $this->assertResponse();
            $this->crawler = new Crawler($response->body, $response->url);
        }
        return $this->crawler;
    }

    protected function assertResponse() : \Requests_Response {
        if ($this->response === null) {
            $this->logger->log('Cannot access response, no request was made yet.', null, Logger::LEVEL_ERROR);
            throw new HttpBotException('Cannot access response, no request was made yet.', 'Logic exception');
        }
        return $this->response;
    }

    private function assertMethod(string $method) {
        if (!\in_array($method, HttpBotInterface::HTTP_METHODS_SUPPORTED)) {
            $this->logger->log($method, 'Unknown HTTP method', Logger::LEVEL_ERROR);                
            throw new HttpBotException("Unknown HTTP method $method", 'Invalid argument');            
        }
    }
    
    public function getResponseHeader(string $name) {
        return $this->assertResponse()->headers[$name];
    }

    public function & getResponseQuery() : array {
        if ($this->query === null) {
            $queryString = $this->getResponseIri()->iquery ?? '';
            $query = [];
            \parse_str($queryString, $query);
            $this->query = & $query;
        }
        return $this->query;
    }
    
    protected function getResponseIri() : Requests_IRI {
        if ($this->iri === null) {
            $url = $this->assertResponse()->url;
            $iri = new Requests_IRI($url);
            if (!$iri->is_valid()) {
                $this->logger->log($url, 'Invalid response URL', Logger::LEVEL_ERROR);
                throw new HttpBotException("Response URL $url is not valid", 'Invalid argument');
            }
            $this->iri = $iri;
        }
        return $this->iri;   
    }

    /**
     * // TODO May need escaping.
     * https://stackoverflow.com/questions/1969232/allowed-characters-in-cookies
     * @param mixed $cookies A set of cookies, either a Requests_Cookie_Jar or an associative array with names and values.
     * @return string The cookies in serialized form, ready to be set as an HTTP header.
     */
    public static function serializeCookies($cookies) : string {
        $res = [];
        if ($cookies instanceof Requests_Cookie_Jar) {
            foreach ($cookies as $name => $cookie) {
                \array_push($res, $cookie->format_for_header());
            }
        }
        else {
            foreach ($cookies as $name => $value) {
                \array_push($res, \sprintf('%s=%s', $name, $value));
            }
        }
        return \implode('; ', $res);
    }

    // Should be private, but PHP sucks.
    public function logResponseBeforeRedirectCheck(Requests_Response & $response, array $headers, $data, array $options) {
        if ($response->is_redirect() && $options['follow_redirects'] === true) {
            return;
        }
        $this->logResponse($response);
    }
    
    public function logResponseBeforeRedirect(string & $location, array & $headers, & $data, array & $options, Requests_Response $response) {
        $this->logResponse($response);
    }

    private function logResponse(Requests_Response & $response) {
            $this->logger->log(function() use ($response) {
            $message = [];
            $pos = \strpos($response->raw, "\r\n");
            $message [] = "<<< " . \substr($response->raw, 0, $pos);
            foreach ($response->headers as $key => $value) {
                $message []= "  $key: $value";
            }
            $message []= "Cookie JAR:";
            $message []= self::inspectCookies($this->cookies, '  ');
            if ($this->logBody) {
                $message []= $response->body;
            }
            return implode("\n", $message);
        }, null, Logger::LEVEL_DEBUG);
    }
    
    // Should be private, but PHP sucks.
    public function logRequest(string & $url, array & $headers, $data, string & $method, array & $options) {
        $this->logger->log(function() use (&$url, &$headers, &$data, &$method, &$options) {
            $message = [];
            $message []= ">>> $method $url HTTP/1.1";
            $message []= "Headers:";
            foreach ($headers as $key => $value) {
                $message []= "  $key: $value";
            }
            $message []= "Cookie JAR:";
            $message []= self::inspectCookies($this->cookies, '  ');
            $message []= "Data:";
            if (\is_array($data)) {
                foreach ($data as $key => $value) {
                    $message []= "  $key: $value";
                }
            }
            else {
                $message []= (string)$data;
            }
            return implode("\n", $message);
        }, null, Logger::LEVEL_DEBUG);
    }

    public function handleBeforeRedirect(string & $location, array & $headers, & $data, array & $options, Requests_Response $response) {
        // Save the cookies we got.
        $oldIri = new Requests_IRI($response->url);
        $this->cookies[$oldIri->host] = $options['cookies'];
        // Make sure we do not send any cookies that do not match.
        unset($headers['Cookie']);
        // Do not multiply handlers...
        $this->initHooks($options['hooks']);
        // Replace cookies with the correct domain.
        $newIri = new Requests_IRI($location);
        if (!isset($this->cookies[$newIri->host])) {
            $this->cookies[$newIri->host] = new Requests_Cookie_Jar();
        }
        $options['cookies'] = $this->cookies[$newIri->host];
    }
    
    public function rewrite302ToGet(string & $location, array & $headers, & $data, array & $options, Requests_Response $response) {
        if (!$this->rewrite302ToGet || $response->status_code !== 302) {
            return;
        }
        $method = $options['type'];
        if ($method === Requests::GET || $method === Requests::HEAD || $method === Requests::OPTIONS) {
            return;
        }
        $this->logger->log("Rewriting 302 $method to GET", null, Logger::LEVEL_DEBUG);
        $options['type'] = Requests::GET;
        if (\is_array($data)) {
            foreach ($data as $key => $value) {
                unset($data[$key]);
            }
        }
        else {
            $data &= '';
        }
    }
    
    private static function inspectCookies(array $cookies, string $prefix = '', string $glue = "\n") : string {
        $lines = [];
        foreach ($cookies as $domain => $jar) {
            foreach ($jar as $name => $cookie) {
                $lines []= $prefix . self::inspectCookie($cookie);
            }
        }
        return \implode($glue, $lines);
    }
    
    private static function inspectCookie(Requests_Cookie $cookie) : string {
        $domain = $cookie->attributes['domain'] ?? '';
        $path = $cookie->attributes['path'] ?? '/';
        $maxAge = $cookie->attributes['max-age'] ?? 0;
        $expires = $cookie->attributes['expires'] ?? 0;
        $flags = [];
        if ($cookie->attributes['httponly'] ?? false) {
            $flags []= 'http-only';
        }
        if ($cookie->attributes['secure'] ?? false) {
            $flags []= 'secure';
        }
        $flagString = \implode(',', $flags);
        return "$cookie->name: $cookie->value @ $domain $path  (max-age: $maxAge, expires: $expires) [$flagString]";
    }

    private function initHooks(HttpBotHooks $hooks) : HttpBotHooks {
        $hooks->clearHooks();
        $hooks->register('requests.before_request', [$this, 'logRequest'], 1);
        $hooks->register('requests.before_redirect_check', [$this, 'logResponseBeforeRedirectCheck'], 1);
        $hooks->register('requests.before_redirect', [$this, 'handleBeforeRedirect'], 1);
        $hooks->register('requests.before_redirect', [$this, 'logResponseBeforeRedirect'], 2);
        $hooks->register('requests.before_redirect', [$this, 'rewrite302ToGet'], 3);
        return $hooks;
    }
}
