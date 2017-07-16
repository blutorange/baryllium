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

/**
 * Description of MooseSmtp
 *
 * @author madgaksha
 */
class AbstractOptions implements \ArrayAccess, \IteratorAggregate, \Countable  {
    
    protected $options;
    
    public function convertToArray() : array {
        return $this->options;
    }
    
    public function __construct(array $options) {
        $this->options = $options;
    }
    
    public function getIterator(): \Traversable {
        return new \ArrayIterator($this->options);
    }

    public function offsetExists($offset): bool {
        return \array_key_exists($offset, $this->options);
    }

    public function offsetGet($offset) {
        return $this->options[$offset];                
    }

    public function offsetSet($offset, $value) {
        $this->options[$offset] = $value;
    }

    public function offsetUnset($offset): void {
        unset($this->options[$offset]);
    }

    public function count(): int {
        return count($this->options);
    }
    
    protected function asBool($key, string $field) {
        $value = $this->options[$key] ?? false;
        if (\is_bool($value)) {
            return $key;
        }
        if ($value === "false") {
            return false;
        }
        if ($value === "true") {
            return true;
        }
        throw new \LogicException("$field must be a bool");
    }

    protected function notNull($key, string $field) {
        $value = $this->options[$key];
        if ($value === null) {
            throw new \LogicException("$field must not be null");
        }
        return $value;
    }
}