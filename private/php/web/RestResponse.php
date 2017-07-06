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

namespace Moose\Web;

use Moose\ViewModel\Message;
use Moose\ViewModel\MessageInterface;

/**
 * @author mad_gaksha
 */
class RestResponse implements RestResponseInterface {
    /** @var HttpResponseInterface */
    private $httpResponse;
    
    /** @var mixed */
    private $json;

    /** @var int */
    private $errorCode;

    /** @var MessageInterface */
    private $errorMessage;

    public function __construct(HttpResponseInterface $httpResponse) {
        $this->httpResponse = $httpResponse;
        $this->json = [];
        $this->setStatusCode(200);
        $this->httpResponse->setMime('application/json');
        $this->httpResponse->setMayDump(false);
    }
    public function setJson($jsonObject) : RestResponseInterface {
        $this->json = $jsonObject ?? [];
        return $this;
    }
    public function setKey(string $key, $value) : RestResponseInterface {
        if ($value === null) {
            $this->unsetKey($key);
        }
        else {
            $this->json[$key] = $value;
        }
        return $this;
    }
    public function unsetKey(string $key) : RestResponseInterface {
        unset($this->json[$key]);
        return $this;
    }
    
    public function setError(int $code, MessageInterface $message = null) {
        $this->errorCode = $code;
        $this->errorMessage = $message;
        return $this;
    }

    public function unsetError() {
        $this->errorCode = null;
        $this->errorMessage = null;
        return $this;
    }
    
    public function apply() {
        if ($this->errorCode !== null) {
            $this->setStatusCode($this->errorCode);
            $errorMessage = $this->errorMessage !== null ?
                $this->errorMessage :
                Message::danger('No message available.', 'No details available.');
            $this->setJson(['error' => [
                'message' => $errorMessage->getMessage(),
                'details' => $errorMessage->getDetails(),
                'severity' => $errorMessage->getSeverity()
            ]]);
        }
        $encode = \json_encode($this->json);
        $this->httpResponse->setContent($encode !== false ? $encode : (string)($this->json));
    }

    public function addHeader(string $name, string $value) : RestResponseInterface {
        $this->httpResponse->addHeader($name, $value);
        return $this;
    }

    public function setStatusCode($code, $text = null) {
        $this->httpResponse->setStatusCode($code, $text);
    }

    public function getHttpResponse(): HttpResponseInterface {
        return $this->httpResponse;
    }

}