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

namespace Moose\Extension;

use Requests;

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
    
    const OPTION_AUTOMATIC_REDIRECT = 0;
    const OPTION_RESPONSE_TIMEOUT = 1;
    const OPTION_CONNECT_TIMEOUT = 2;
    const OPTION_REDIRECT_LIMIT = 3;
    const OPTION_HTTP_AUTH = 4;
    const OPTION_VERIFY_SSL = 5;
    
    /**
     * 
     * @param int $option
     * @param mixed $value
     */
    public function setOption(int $option, $value) : HttpBotInterface;
    public function setAutomaticRedirect(bool $automaticRedirect) : HttpBotInterface;
    public function enableAutomaticRedirect() : HttpBotInterface;
    public function disabledAutomaticRedirect() : HttpBotInterface;
    public function setVerifySSL(bool $verifySSL) : HttpBotInterface;
    public function enableVerifySSL(bool $verifySSL) : HttpBotInterface;
    public function disableVerifySSL(bool $verifySSL) : HttpBotInterface;
    /**
     * @param int $timeout In seconds.
     */
    public function setResponseTimeout(int $timeout) : HttpBotInterface;
    /**
     * @param int $timeout In seconds.
     */
    public function setConnectTimeout(int $timeout) : HttpBotInterface;
    public function setRedirectLimit(int $limit) : HttpBotInterface;
    /**
     * @param array $credentials An array with two entries, username and
     * password. If the array is empty, disabled http auth.
     */
    public function setHttpAuth(array $credentials) : HttpBotInterface;
    
    public function addHeader(string $key, string $value) : HttpBotInterface;
    public function setHeaders(array & $keyValueMap) : HttpBotInterface;
    public function resetHeaders() : HttpBotInterface;
    
    public function addDatum(string $key, string $value) : HttpBotInterface;
    public function setData(array & $keyValueMap) : HttpBotInterface;
    public function resetData() : HttpBotInterface;
    
    public function request(string $url, int $method = HttpBotInterface::HTTP_GET, array $data = [], array $headers = [], array $options = []) : HttpBotInterface;
    public function get(string $url, array $data = [], array $headers = [], array $options = []) : HttpBotInterface;
    public function post(string $url, array $data = [], array $headers = [], array $options = []) : HttpBotInterface;
    public function delete(string $url, array $data = [], array $headers = [], array $options = []) : HttpBotInterface;
    public function head(string $url, array $data = [], array $headers = [], array $options = []) : HttpBotInterface;
    public function put(string $url, array $data = [], array $headers = [], array $options = []) : HttpBotInterface;
}