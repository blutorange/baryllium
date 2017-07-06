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

/**
 * Implements some convenience methods required by the HttpBotInterface.
 * @author madgaksha
 */
trait HttpBotOptionsTrait {
    public final function resetData() : HttpBotInterface {
        $emptyArray = [];
        return $this->setData($emptyArray);
    }
    
    public final function resetHeaders() : HttpBotInterface {
        $emptyArray = [];
        return $this->setHeaders($emptyArray);
    }
    
    public final function setAutomaticRedirect(bool $automaticRedirect): HttpBotInterface {
        return $this->setOption(HttpBotInterface::OPTION_AUTOMATIC_REDIRECT, $automaticRedirect);
    }

    public final function enableAutomaticRedirect(): HttpBotInterface {
        return $this->setAutomaticRedirect(true);
    }

    public final function disabledAutomaticRedirect(): HttpBotInterface {
        return $this->setAutomaticRedirect(false);
    }

    public final function setResponseTimeout(int $timeout): HttpBotInterface {
        return $this->setOption(HttpBotInterface::OPTION_RESPONSE_TIMEOUT, $timeout);
    }

    public final function setConnectTimeout(int $timeout): HttpBotInterface {
        return $this->setOption(HttpBotInterface::OPTION_CONNECT_TIMEOUT, $timeout);
    }

    public final function setRedirectLimit(int $limit): HttpBotInterface {
        return $this->setOption(HttpBotInterface::OPTION_REDIRECT_LIMIT, $limit);
    }

    public function setHttpAuth(array $credentials): HttpBotInterface {
        return $this->setOption(HttpBotInterface::OPTION_HTTP_AUTH, $credentials);
    }

    public function setVerifySSL(bool $verifySSL): HttpBotInterface {
        return $this->setOption(HttpBotInterface::OPTION_VERIFY_SSL, $verifySSL);
    }

    public function enableVerifySSL(): HttpBotInterface {
        return $this->setVerifySSL(true);
    }

    public function disableVerifySSL(): HttpBotInterface {
        return $this->setVerifySSL(false);
    }

    public function setVerifyName(bool $verifyName): HttpBotInterface {
        return $this->setOption(HttpBotInterface::OPTION_VERIFY_NAME, $verifyName);
    }

    public function enableVerifyName(): HttpBotInterface {
        return $this->setVerifyName(true);
    }

    public function disableVerifyName(): HttpBotInterface {
        return $this->setVerifyName(false);
    }

    public function setUserAgent(string $userAgent): HttpBotInterface {
        return $this->setOption(HttpBotInterface::OPTION_USER_AGENT, $userAgent);
    }

    public function setLogger(Logger $logger = null): HttpBotInterface {
        return $this->setOption(HttpBotInterface::OPTION_LOGGER, $logger);
    }

    public function disableLogger(): HttpBotInterface {
        return $this->setLogger(null);
    }

    public function setLogBody(bool $logBody): HttpBotInterface {
        return $this->setOption(HttpBotInterface::OPTION_LOG_BODY, $logBody);
    }

    public function enableLogBody(): HttpBotInterface {
        return $this->setLogBody(true);
    }

    public function disableLogBody(): HttpBotInterface {
        return $this->setLogBody(false);
    }

    public function setRewrite302ToGet(bool $rewrite302ToGet): HttpBotInterface {
        return $this->setOption(HttpBotInterface::OPTION_REWRITE_302_TO_GET, $rewrite302ToGet);
    }

    public function enableRewrite302ToGet(): HttpBotInterface {
        return $this->setRewrite302ToGet(true);
    }

    public function disableRewrite302ToGet(): HttpBotInterface {
        return $this->setRewrite302ToGet(false);
    }

    public abstract function setHeaders(array & $keyValueMap) : HttpBotInterface;
    public abstract function setData(array & $keyValueMap) : HttpBotInterface;
    public abstract function setOption(int $option, $value): HttpBotInterface;
}
