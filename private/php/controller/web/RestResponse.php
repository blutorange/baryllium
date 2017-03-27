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

namespace Servlet;

use Controller\HttpResponse;
use Controller\HttpResponseInterface;
use Ui\Message;

/**
 * @author mad_gaksha
 */
class RestResponse implements RestResponseInterface {
    /** @var HttpResponseInterface */
    private $httpResponse;
    
    /** @var array */
    private $json;

    /** @var int */
    private $errorCode;

    /** @var Message */
    private $errorMessage;

    public function __construct(HttpResponseInterface $httpResponse) {
        $this->httpResponse = $httpResponse;
        $this->json = [];
        $this->setStatusCode(200);
    }
    public function setJson(array $jsonObject) {
        $this->json = $jsonObject ?? [];
    }
    public function setKey(string $key, $value) {
        if ($value === null) {
            $this->unsetKey($key);
        }
        else {
            $this->json[$key] = $value;
        }
    }
    public function unsetKey(string $key) {
        unset($this->json[$key]);
    }
    
    public function setError(int $code, Message $message = null) {
        $this->errorCode = $code;
        $this->errorMessage = $message;
    }

    public function unsetError() {
        $this->errorCode = null;
        $this->errorMessage = null;
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
        $this->httpResponse->setContent(\json_encode($this->json));
    }

    public function addHeader(string $name, string $value) {
        $this->httpResponse->addHeader($name, $value);
    }

    public function setStatusCode($code, $text = null) {
        $this->httpResponse->setStatusCode($code, $text);
    }
}