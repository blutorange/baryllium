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

use Moose\Util\MonoPredicateInterface;
use Moose\Util\MonoPredicate as M;

/**
 * Implements some convenience methods required by the HttpBotInterface.
 * @author madgaksha
 */
trait HttpBotPredicatesTrait {
    public function checkResponseCode(MonoPredicateInterface $predicate) : bool {
        if (!$predicate->check($this->getResponseCode())) {
            return false;
        }
        return true;
    }
    public function checkResponsePath(MonoPredicateInterface $predicate): bool {
        if (!$predicate->check($this->getResponsePath())) {
            return false;
        }
        return true;
    }
    
    public function assertResponseCode(MonoPredicateInterface $predicate,
            string $exceptionClass = HttpBotException::class) : HttpBotInterface {
        if ($this->checkResponseCode($predicate) === false) {
            $isCode = $this->getResponseCode();
            if ($exceptionClass === HttpBotException::class) {
                throw new HttpBotException("Expected response code $predicate does not match actual response code $isCode", 'Assertion failure');
            }
            throw new $exceptionClass("Expected response code $predicate does not match actual response code $isCode");
        }
        return $this->getThis();
    }

    public function assertResponsePath(MonoPredicateInterface $predicate,
            string $exceptionClass = HttpBotException::class): HttpBotInterface {
        if ($this->checkResponsePath($predicate) === false) {
            $isPath = $this->getResponsePath();
            if ($exceptionClass === HttpBotException::class) {
                throw new HttpBotException("Expected response path $predicate does not match actual response path $isPath", 'Assertion failure');
            }
            throw new $exceptionClass("Expected response path $predicate does not match actual response path $isPath");
        }
        return $this->getThis();
    }

    public function always($callable) : HttpBotInterface {
        $this->addReturn(\call_user_func($callable, $this));
        return $this->getThis();
    }
    
    public function when(MonoPredicateInterface $predicate, $ifCallback, $elseCallback = null) : HttpBotInterface {
        if ($predicate->check($this)) {
            $this->addReturn(\call_user_func($ifCallback, $this));
        }
        else if ($elseCallback !== null) {
            $this->addReturn(\call_user_func($elseCallback, $this));
        }
        else {
            $this->addReturn(null);
        }
        return $this->getThis();
    }
    
    public function ifResponseCode(MonoPredicateInterface $predicate, $ifCallback, $elseCallback = null) : HttpBotInterface {
        return $this->when(M::custom(function() use ($predicate) {
            return $this->checkResponseCode($predicate);
        }, "responseCode($predicate)"), $ifCallback, $elseCallback);        
    }

    public function ifResponsePath(MonoPredicateInterface $predicate, $ifCallback, $elseCallback = null) : HttpBotInterface {
        return $this->when(M::custom(function() use ($predicate) {
            return $this->checkResponsePath($predicate);
        }, "responsePath($predicate)"), $ifCallback, $elseCallback);        
    }
    
    public function ifNonNullReturn($ifCallback, $elseCallback = null, int $position = -1) : HttpBotInterface {
        $return = $this->getReturn($position);
        return $this->when(M::constant($return !== null),
                function(HttpBotInterface $bot) use ($ifCallback, $return) {
                    return \call_user_func($ifCallback, $return, $bot);
                }, function(HttpBotInterface $bot) use ($elseCallback) {
                    return $elseCallback != null ? \call_user_func($elseCallback, $bot) : null;
                });
    }
    
    protected abstract function getThis() : HttpBotInterface;
    protected abstract function addReturn($value);
    
    public abstract function getReturn(int $position = -1);
    public abstract function getResponseUrl(): string;
    public abstract function getResponsePath(): string;
    public abstract function getResponseCode() : int;
}
