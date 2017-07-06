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

use Requests_Cookie;

/**
 * Implements some convenience methods required by the HttpBotInterface.
 * @author madgaksha
 */
trait HttpBotCookiesTrait {
    public function findCookieOne($predicate, $callback): HttpBotInterface {
        return $this->findCookieMulti($predicate, function (array $cookies) use ($callback) {
            return $callback !== null ? \call_user_func($callback, $cookies[0]) : $cookies[0];
        }, 1);
    }

    public function getCookieOne(array $searchCriteria, $callback): HttpBotInterface {
        return $this->getCookieMulti($searchCriteria, function (array $cookies) use ($callback) {
            return $callback !== null ? \call_user_func($callback, $cookies[0]) : $cookies[0];
        }, 1);
    }

    public function getCookieMulti(array $searchCriteria, $callback, int $expectedCount = -1): HttpBotInterface {
        return $this->findCookieMulti(function (Requests_Cookie $cookie) use (&$searchCriteria) {
            return self::matchCookie($cookie, $searchCriteria);
        }, $callback, $expectedCount);
    }

    public function deleteCookies(array $searchCriteria): HttpBotInterface {

        return $this->clearCookies(function (Requests_Cookie $cookie) use (&$searchCriteria) {
            return self::matchCookie($cookie, $searchCriteria);
        });
    }

    private static function matchCookie(Requests_Cookie $cookie, array & $searchCriteria): bool {

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


    public abstract function clearCookies($predicate = null): HttpBotInterface;

    public abstract function findCookieMulti($predicate, $callback, int $expectedCount): HttpBotInterface;
}
