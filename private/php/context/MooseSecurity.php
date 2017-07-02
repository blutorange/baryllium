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

namespace Moose\Context;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\Translation\Exception\LogicException;

/**
 * Security related settings.
 *
 * @author madgaksha
 */
class MooseSecurity {
    /** @var int */
    private $rememberMeTimeout;
    
    /** @var int */
    private $sessionTimeout;
    
    /** @var bool */
    private $httpOnly;
    
    /** @var bool */
    private $sessionSecure;
    
    /** @var string Either Cookie::SAME_SITE_STRICT or Cookie::SAME_SITE_LAX */
    private $sameSite;

    private function __construct(array & $environment) {
        $top = $this->assertTop($environment);
        $this->httpOnly = $this->asBool($top, 'http_only');
        $this->sessionSecure = $this->asBool($top, 'session_secure');
        $this->sessionTimeout = $this->asTimeout($top, 'session_timeout', 3600);
        $this->rememberMeTimeout = $this->asTimeout($top, 'remember_me_timeout', 86400);
        
        $this->sameSite = \in_array($top['same_site'] ?? [], [Cookie::SAMESITE_STRICT, Cookie::SAMESITE_LAX]) ? $top['same_site'] : Cookie::SAMESITE_STRICT;
        
        if ($this->rememberMeTimeout < 0) {
            $this->rememberMeTimeout = 0;
        }
    }    

    /**
     * @return int The timeout of remember me cookies, in seconds.
     */
    public function getRememberMeTimeout(): int {
        return $this->rememberMeTimeout;
    }
    
    /**
     * @return int The timeout of a PHP session.
     */
    public function getSessionTimeout() : int {
        return $this->sessionTimeout;
    }
    
    /**
     * Whether security related cookies should be marked HTTP only.
     * @return bool
     */
    public function getHttpOnly() : bool {
        return $this->httpOnly;
    }

    /**
     * Whether security related cookies should be allowed only via HTTPS.
     * @return bool
     */
    public function getSessionSecure() : bool {
        return $this->sessionSecure;
    }

    /**
     * Whether security related cookies should be marked as lax or strict.
     * One of Cookie#SAME_SITE_STRICT or Cookie#SAME_SITE_LAX.
     * @return bool
     */
    public function getSameSite() : string {
        return $this->sameSite;
    }
        
    private function & assertTop(array & $top) : array {
        if (!isset($top['remember_me_timeout']))
            throw new LogicException('Cannot create config, missing security/remember_me_timeout entry.');
        if (!isset($top['session_timeout']))
            throw new LogicException('Cannot create config, missing security/session_timeout entry.');
        if (!isset($top['http_only']))
            $top['http_only'] = 'true';
        if (!isset($top['session_secure']))
            $top['session_secure'] = 'true';
        if (!isset($top['same_site']))
            $top['same_site'] = Cookie::SAMESITE_STRICT;
        return $top;
    }
    
    public function & convertToArray() : array {
        $base = [
            'remember_me_timeout' => (string)($this->rememberMeTimeout),
            'same_site' => $this->sameSite,
            'http_only' => $this->httpOnly ? 'true' : 'false',
            'session_secure' => $this->sessionSecure ? 'true' : 'false',
            'session_timeout' => (string)($this->sessionTimeout)
        ];
        return $base;
    }

    public static function makeFromArray(array & $security) : MooseSecurity {
        return new MooseSecurity($security);
    }

    private function asBool(array & $array, string $key) : bool {
        $bool = $array[$key] ?? 'true';
        if ($bool === true || $bool === false) {
           return $bool;
        }
        return $bool !== 'false';
    }

    private function asTimeout(array & $array, string $key, int $default, int $min = 0, int $max = null) {
        $val = \intval($array[$key] ?? $default);
        if ($min !== null && $val < $min) {
            $val = $min;
        }
        if ($max !== null && $val > $max) {
            $val = $max;
        }
        return $val;
    }
}