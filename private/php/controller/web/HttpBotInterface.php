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
use Moose\Util\BiPredicate;
use Moose\Util\MonoPredicate;

/**
 * <p>
 * The main interface for dealing with HTTP requests needed for accessing and
 * communication with third party web services. This usually boils down to
 * making a request, storing and sending the appropriate cookies, and following
 * redirects. Occasionally, the response body needs to be examined to retrieve
 * credentials, tokens, or links.
 * </p>
 * <p>
 * This takes care of following redirects, handling cookies transparently,
 * providing hooks, and asserting the expected response (code).
 * </p>
 * <p>
 * It also provides some methods for retrieving HTML elements and forms.
 * </p>
 * @author madgaksha
 */
interface HttpBotInterface {
    const HTTP_GET = 0;
    const HTTP_POST = 1;
    const HTTP_HEAD = 2;
    const HTTP_DELETE = 3;
    const HTTP_PUT = 4;
    const HTTP_OPTIONS = 5;
    const HTTP_PATCH = 6;
    const HTTP_TRACE = 7;
    
    const OPTION_AUTOMATIC_REDIRECT = 0;
    const OPTION_RESPONSE_TIMEOUT = 1;
    const OPTION_CONNECT_TIMEOUT = 2;
    const OPTION_REDIRECT_LIMIT = 3;
    const OPTION_HTTP_AUTH = 4;
    const OPTION_VERIFY_SSL = 5;
    const OPTION_VERIFY_NAME = 6;
    const OPTION_USER_AGENT = 7;
    const OPTION_LOGGER = 8;
    const OPTION_LOG_BODY = 9;
    const OPTION_REWRITE_302_TO_GET = 10;
    
    const COOKIE_SAME_SITE_STRICT = 0;
    const COOKIE_SAME_SITE_LAX = 1;
    
    const CONDITION_EQUALS = 'equals';
    
    /**
     * Sets the options to the specified value. Use one of the constants
     * defined by this interface for the option type.
     * @param int $option
     * @param mixed $value
     * @return HttpBotInterface
     */
    public function setOption(int $option, $value) : HttpBotInterface;
    public function setAutomaticRedirect(bool $automaticRedirect) : HttpBotInterface;
    public function enableAutomaticRedirect() : HttpBotInterface;
    public function disabledAutomaticRedirect() : HttpBotInterface;
    public function setVerifySSL(bool $verifySSL) : HttpBotInterface;
    public function enableVerifySSL() : HttpBotInterface;
    public function disableVerifySSL() : HttpBotInterface;
    public function setUserAgent(string $userAgent) : HttpBotInterface;
    public function setVerifyName(bool $verifyName) : HttpBotInterface;
    public function enableVerifyName() : HttpBotInterface;
    public function disableVerifyName() : HttpBotInterface;
    public function setLogger(Logger $logger = null) : HttpBotInterface;
    public function disableLogger() : HttpBotInterface;
    public function setLogBody(bool $logBody) : HttpBotInterface;
    public function enableLogBody() : HttpBotInterface;
    public function disableLogBody() : HttpBotInterface;
    public function setRewrite302ToGet(bool $rewrite302ToGet) : HttpBotInterface;
    public function enableRewrite302ToGet() : HttpBotInterface;
    public function disableRewrite302ToGet() : HttpBotInterface;
    
    /**
     * @param int $timeout In seconds.
     * @return HttpBotInterface
     */
    public function setResponseTimeout(int $timeout) : HttpBotInterface;
    /**
     * @param int $timeout In seconds.
     * @return HttpBotInterface
     */
    public function setConnectTimeout(int $timeout) : HttpBotInterface;
    public function setRedirectLimit(int $limit) : HttpBotInterface;
    /**
     * @param array $credentials An array with two entries, username and
     * password. If the array is empty, disabled http auth.
     * @return HttpBotInterface
     */
    public function setHttpAuth(array $credentials) : HttpBotInterface;
    
    public function addHeader(string $key, string $value) : HttpBotInterface;
    public function setHeaders(array & $keyValueMap) : HttpBotInterface;
    public function resetHeaders() : HttpBotInterface;
    
    public function addDatum(string $key, string $value) : HttpBotInterface;
    public function setData(array & $keyValueMap) : HttpBotInterface;
    public function resetData() : HttpBotInterface;
    
    /**
     * 
     * @param string $name
     * @param string $value
     * @param int $expire UNIX timestamp.
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httpOnly
     * @param bool $hostOnly
     * @return HttpBotInterface
     */
    public function addCookie(string $name, string $value,
            int $expire = 0, string $path = '/', string $domain = null,
            bool $secure = false, bool $httpOnly = true,
            bool $hostOnly = false) : HttpBotInterface;

    /**
     * Clear ALL cookies, or those matching the predicate when given.
     * @param callable|array|string|null $predicate
     * @return HttpBotInterface this for chaining.
     */
    public function clearCookies($predicate) : HttpBotInterface;

    /**
     * Clears the matching cookies.
     * @param callable|array|string $predicate
     * @return HttpBotInterface this for chaining.
     */
    public function deleteCookies(array $searchCriteria) : HttpBotInterface;

    /**
     * @param callable|array|string $predicate It is passed the
     * Requests_Cookie. Must return true when the option matches.
     * @param callable|array|string $callback Called with the found Requests_Cookie.
     * @return HttpBotInterface this for chaining.
     * @throws HttpBotException When not exactly one cookie was found.
     */
    public function findCookieOne($predicate, $callback) : HttpBotInterface;

    /**
     * @param $predicate callable|array|string Must return true when the option matches.
     * @param $callback callable|array|string Called with the found Requests_Cookie[].
     * @param int $expectedCount How many cookie are expected to be found.
     * Defaults to -1, which means any number is allowed.
     * @return HttpBotInterface this for chaining.
     * @throws HttpBotException When the expected number of cookies was not found.
     */
    public function findCookieMulti($predicate, $callback, int $expectedCount) : HttpBotInterface;

    /**
     * @param array $searchCriteria An associative array with may contain the following
     * criteria for matching the cookie:
     * <ul>
     *   <li>name: (string) Matches the name of the cookie</li>
     *   <li>value: (string) Matches the value of the cookie</li>
     *   <li>domain: (string) Matches the domain of the cookie</li>
     *   <li>path: (string) Matches the path of the cookie</li>
     *   <li>expired: (bool) Whether the cookie should be expired or not.</li>
     * </ul>
     * @param callable|array|string $callback Called with the cookie value if found.
     * @return HttpBotInterface this for chaining.
     * @throws HttpBotException When the cookie was not found.
     */

    public function getCookieOne(array $searchCriteria, $callback) : HttpBotInterface;

    /**
     * @param array $searchCriteria Criteria for matching the cookies. See
     * #getCookieOne.
     * @param callable|array|string $callback Called with the cookies value if found.
     * @param int $expectedCount Throws an HttpBotException when the number
     * of cookies found does not match this count. Set this to -1 to not have
     * an exception thrown.
     * @return HttpBotInterface this for chaining.
     * @throws HttpBotException When the expected count does not match.
     */
    public function getCookieMulti(array $searchCriteria, $callback, int $expectedCount = -1) : HttpBotInterface;
    
    /**
     * @param int $position Return value to fetch.
     * @return mixed The return value of the n-th callback. Use -1 for the most
     * recent, -2 for the second to last etc.
     * @throws HttpBotException When there is no return value.
     * @return HttpBotInterface
     */
    public function getReturn(int $position = -1);

    /**
     * @return HttpBotInterface
     */
    public function clearReturn() : HttpBotInterface;

    /**
     * @param string $url URL to send the request to.
     * @param int $method Use the constants provided by this interface, eg. HttpBotInterface::HTTP_METHOD_GET.
     * @param array $data
     * @param array $headers
     * @param array $options
     * @return HttpBotInterface
     * @throws HttpBotException When the request fails.
     */
    public function request(string $url, int $method = HttpBotInterface::HTTP_GET, array $data = [], array $headers = [], array $options = []) : HttpBotInterface;
    public function get(string $url, array $data = [], array $headers = [], array $options = []) : HttpBotInterface;
    public function post(string $url, array $data = [], array $headers = [], array $options = []) : HttpBotInterface;
    public function delete(string $url, array $data = [], array $headers = [], array $options = []) : HttpBotInterface;
    public function head(string $url, array $data = [], array $headers = [], array $options = []) : HttpBotInterface;
    public function put(string $url, array $data = [], array $headers = [], array $options = []) : HttpBotInterface;
    public function patch(string $url, array $data = [], array $headers = [], array $options = []) : HttpBotInterface;
    public function options(string $url, array $data = [], array $headers = [], array $options = []) : HttpBotInterface;
    public function trace(string $url, array $data = [], array $headers = [], array $options = []) : HttpBotInterface;
    
    /**
     * @param string $selector CSS selector to search for.
     * @param callable|array|string $callback It is passed the found elements as a Crawler.
     * @return HttpBotInterface this for chaining
     * @throws HttpBotException When not exactly one element was found, or no request was made yet.
     */
    public function selectOne(string $selector, $callback) : HttpBotInterface;
    /**
     * @param string $selector CSS selector to search for.
     * @param callable|array|string $callback It is passed the found element as a Crawler.
     * @param int $expectedCount Number of elements expected to be found. -1 for no expectation.
     * @return HttpBotInterface this for chaining
     * @throws HttpBotException When the number of found elements does not match the expectation,
     * or no request was made yet.
     */
    public function selectMulti(string $selector, $callback, int $expectedCount = -1) : HttpBotInterface;
    /**
     * @param string $selector CSS selector to search for.
     * @param callable|array|string $callback It is passed the found DOMElement.
     * @return HttpBotInterface this for chaining
     * @throws HttpBotException When not exactly one element was found, or no request was made yet.
     */
    public function selectOneDom(string $selector, $callback) : HttpBotInterface;

    /**
     * @param string $selector CSS selector to search for.
     * @param callable|array|string $callback It is passed the found DOMElement[].
     * @param int $expectedCount Number of elements expected to be found. -1 for no expectation.
     * @return HttpBotInterface this for chaining
     * @throws HttpBotException When the number of found elements does not match the expectation,
     * or no request was made yet.
     */
    public function selectMultiDom(string $selector, $callback, int $expectedCount = -1) : HttpBotInterface;

    /**
     * @param string $selector CSS selector for the form. Must match exactly one element.
     * @param array $values Value to set on the form. When not specified, the default
     * value from the HTML form is used.
     * @param array $headers
     * @param array $options
     * @return HttpBotInterface this for chaining.
     * @throws HttpBotException When the selector does not match exactly one element, or the request fails.
     */
    public function submitForm(string $selector,
            string $submitButtonSelector = null, array $values = [],
            array $headers = [], array $options = []) : HttpBotInterface;

    /**
     * @return int The status code of the previous response.
     * @throws HttpBotException When no successful request was made yet.
     */
    public function getResponseCode() : int;

    /**
     * @return string The path part of the URL of the previous response.
     * @throws HttpBotException When no successful request was made yet.
     */
    public function getResponsePath() : string;

    /**
     * @return string The complete URL of the response.
     * @throws HttpBotException When no successful request was made yet.
     */
    public function getResponseUrl(): string;
    
    /**
     * @param MonoPredicate $predicate Predicate to check the response code against.
     * @return bool True iff the response code matches the expectation.
     * @throws HttpBotException When no successful request was made yet.
     */
    public function checkResponseCode(MonoPredicate $predicate) : bool;

    /**
     * @param MonoPredicate $predicate
     * @return bool True iff the response path matches the expectation.
     * @throws HttpBotException When no successful request was made yet.
     */
    public function checkResponsePath(MonoPredicate $predicate) : bool;

    /**
     * @param MonoPredicate $predicate Predicate to check the response code against.
     * @return HttpBotInterface
     * @throws HttpBotException When the assertion fails.
     */
    public function assertResponseCode(MonoPredicate $predicate) : HttpBotInterface;

    /**
     * @param MonoPredicate $predicate Predicate to check the response path against.
     * @return HttpBotInterface
     * @throws HttpBotException When the assertion fails.
     */
    public function assertResponsePath(MonoPredicate $predicate) : HttpBotInterface;

    /**
     * @param MonoPredicate $predicate Predicate to check the response code against.
     * @param callable|array|string $callback Called if the response code matches.
     * @return HttpBotInterface this for chaining
     * @throws HttpBotException When no successful request was made yet.
     */
    public function ifResponseCode(MonoPredicate $predicate, $callback) : HttpBotInterface;

    /**
     * @param MonoPredicate $predicate Predicate to check the response path against.
     * @param callable|array|string $callback Called if the response code matches.
     * @return HttpBotInterface this for chaining
     * @throws HttpBotException When no successful request was made yet.
     */
    public function ifResponsePath(MonoPredicate $predicate, $callback) : HttpBotInterface;
}