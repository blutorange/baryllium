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

use Moose\Util\DebugUtil;
use Symfony\Component\HttpFoundation\Request;
use const MB_CASE_LOWER;
use function mb_convert_case;

class HttpRequest extends Request implements HttpRequestInterface {

    /** @var array */
    private $allParameters;

    public function __construct(array $query = [],
                                array $request = [], array $attributes = [],
                                array $cookies = [], array $files = [],
                                array $server = [], $content = null) {
        parent::__construct($query, $request, $attributes, $cookies, $files,
                $server, $content);
    }
   
    public function getParam(string $key, $defaultValue = null, int $fromWhere = self::PARAM_ALL) {
        switch ($fromWhere) {
            case self::PARAM_QUERY:
                return $this->query->get($key, $defaultValue) ?? $defaultValue;
            case self::PARAM_FORM:
                return $this->request->get($key, $defaultValue) ?? $defaultValue;
            case self::PARAM_FILE:
                return $this->files->get($key, $defaultValue) ?? $defaultValue;
            case self::PARAM_HEADER:
                return $this->headers->get($key, $defaultValue, true) ?? $defaultValue;
            case self::PARAM_COOKIE:
                return $this->cookies->get($key, $defaultValue) ?? $defaultValue;
            default:
                return $this->get($key, $defaultValue) ?? $defaultValue;
        }
    }
        
    public function getAllParams(int $fromWhere = self::PARAM_ALL) {
        switch ($fromWhere) {
            case self::PARAM_QUERY:
                return $this->query->all();
            case self::PARAM_FORM:
                return $this->request->all();
            case self::PARAM_FILE:
                return $this->files->all();
            case self::PARAM_HEADER:
                return $this->headers->all();
            case self::PARAM_COOKIE:
                return $this->cookies->all();
            default:
                return $this->lazyGetAllParams();
        }   
    }

    private function lazyGetAllParams() {
        if ($this->allParameters === null) {
            $all = $this->attributes->all();
            $all = array_merge($all, $this->query->all());
            $all = array_merge($all, $this->request->all());
            $this->allParameters = $all;
        }
        return $this->allParameters;
    }
        
    public function getParamBool(string $key, bool $defaultValue = null, int $fromWhere = self::PARAM_ALL, bool $strict = false) {
        $raw = $this->getParam($key, $defaultValue, $fromWhere);
        if ($raw === null) {
            return $strict ? $defaultValue : false;
        }
        $str = mb_convert_case((string)$raw, MB_CASE_LOWER);
        if ($str === 'true') {
            return true;
        }
        if (!$strict && $str === 'on' || $str === '1') {
            return true;
        }
        return false;
    }

    public function getParamInt(string $key, int $defaultValue = null, int $fromWhere = self::PARAM_ALL) {
        $val = $this->getParam($key, $defaultValue, $fromWhere);
        if ($val === null) {
            return $defaultValue;
        }
        $res = filter_var($val, FILTER_VALIDATE_INT);
        if ($res === false) {
            return $defaultValue;
        }
        return intval($val, 10);
    }
   
    public function getFiles(string $name = null) : array {
        $keys = $name !== null ? [$name] : array_keys($this->files->all());
        $fileList = [];
        foreach ($keys as $key) {
            $fileOrFiles = $this->files->get($key);
            if ($fileOrFiles !== null) {
                if (\is_array($fileOrFiles)) {
                    $fileList = \array_merge($fileList, $fileOrFiles);
                }
                else {
                    \array_push($fileList, $fileOrFiles);
                }
            }
        }
        return array_values($fileList);
    }
    
    public function getQueryString() {
        return parent::getQueryString() ?? '';
    }
    
    public function hasQuery() {
        return parent::getQueryString() !== null;
    }

    public function getHttpMethod() {
        return parent::getRealMethod();
    }

    public static function createFromGlobals() {
        return parent::createFromGlobals();
    }

    public function getCookieOption(string $field, string $key,
            $defaultValue = null) {
        $cookie = $this->getParam($field, "", HttpRequest::PARAM_COOKIE);
        $value = null;
        if (empty($cookie))
            return $defaultValue;
        $base64 = \base64_decode($cookie);
        $json = $base64 === false ? null : \json_decode($base64);
        if($json === null || !\is_object($json)) {
            Context::getInstance()->getLogger()->log("Found illegal json for option cookie: $cookie");
        }
        else {
            $value = $json->$key ?? null;
        }
        return $value ?? $defaultValue;
    }
    
    public function getRemoteAddressList() : array {
        return $this->getClientIps() ?? [];
    }
    
    public function isLocalhost() : bool {
        $whitelist = ['127.0.0.1', '::1'];
        if (\in_array($this->server->get('REMOTE_ADDR') ?? '', $whitelist)) {
            return true;
        }
        return false;
    }
}