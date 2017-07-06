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

use Moose\Util\DebugUtil;

/**
 * Description of RestRequest
 *
 * @author madgaksha
 */
class RestRequest implements RestRequestInterface {
    /** @var HttpRequestInterface */
    private $httpRequest;
    
    /** @var array */
    private $jsonArray;
    
    /** @var array */
    private $jsonObject;
    
    public function __construct(HttpRequestInterface $httpRequest) {
        $this->httpRequest = $httpRequest;
    }
    
    public function getJson($convertToAssociativeArray = false) {
        if (!$convertToAssociativeArray && $this->jsonObject !== null) {
            return $this->jsonObject;
        }
        if ($convertToAssociativeArray && $this->jsonArray !== null) {
            return $this->jsonArray;
        }
        $json = $this->getJsonFallback($convertToAssociativeArray);
        if ($json === null) {
            Context::getInstance()->getLogger()->log("Could not get request JSON: " . $this->httpRequest->getContent());
            $json = [];
        }
        if ($convertToAssociativeArray) {
            $this->jsonArray = $json;
        }
        else {
            $this->jsonObject = $json;
        }
        return $json;
    }

    public function getQueryParam(string $key, $defaultValue = null) {
        return $this->httpRequest->getParam($key, $defaultValue, HttpRequestInterface::PARAM_QUERY);
    }
    
    public function getQueryParamBool(string $key, bool $defaultValue = null, bool $strict = false) {
        return $this->httpRequest->getParamBool($key, $defaultValue, HttpRequestInterface::PARAM_QUERY, $strict);
    }
    
    public function getHttpRequest(): HttpRequestInterface {
        return $this->httpRequest;
    }

    public function convertToObject($json) {
        if (\is_array($json)) {
            $new = [];
            foreach ($json as $key => $value) {
                $new[$key] = $this->convertToObject($value);
            }
            return (object)$new;
        }
        return $json;
    }

    private function getJsonFromRequest(bool $convertToAssociativeArray = null) {
        switch ($this->httpRequest->getHttpMethod()) {
        case 'GET':
            $json = $this->httpRequest->getAllParams(HttpRequestInterface::PARAM_QUERY);
            if (!$convertToAssociativeArray) {
                $json = $this->convertToObject($json);
            }
            return $json;
        case 'POST':
        case 'PATCH':
            $json = $this->httpRequest->getAllParams(HttpRequestInterface::PARAM_FORM);
            if ($json !== null && !$convertToAssociativeArray) {
                $json = $this->convertToObject($json);
            }
            return $json;
        }
        return null;
    }

    private function getJsonFromContent(bool $convertToAssociativeArray = null) {
        $json = \json_decode($this->httpRequest->getContent(), $convertToAssociativeArray);
        if (\json_last_error() !== JSON_ERROR_NONE) {
            Context::getInstance()->getLogger()->log("Could not parse request JSON: " . \json_last_error_msg());
            $json = null;
        }
        return $json;
    }

    private function getJsonFallback(bool $convertToAssociativeArray = null) {
        $json = null;
        if ($this->httpRequest->getContentType() === 'json') {
            $json = $this->getJsonFromContent($convertToAssociativeArray);
            if ($json === null) {
               $json = $this->getJsonFromRequest($convertToAssociativeArray);
            }
        }
        else {
            $json = $this->getJsonFromRequest($convertToAssociativeArray);
            if ($json === null) {
                $json = $this->getJsonFromContent($convertToAssociativeArray);
            }
        }
        return $json;
    }

}
