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

namespace Moose\Util;

use function mb_substr;

/**
 * Encapsulates a condition with two arguments. For example:
 * <code>BiPredicate::startsWith()->bindSecond('World')->check('Hello World');</code>
 * 
 * @author madgaksha
 */
class BiPredicate {
    private static $EQUALS;
    private static $STARTS_WITH;
    private static $LENGTH_IS;
    
    private $predicate;

    public function __construct(callable $predicate) {
        $this->predicate = $predicate;
    }
    
    public function check($x, $y) : bool {
        return \call_user_func($this->predicate, $x, $y);
    }
    
    public function bindFirst($x) : MonoPredicate {
        return MonoPredicate::bindFirst($this, $x);
    }

    public function bindSecond($y) : MonoPredicate {
        return MonoPredicate::bindSecond($this, $y);
    }
    
    /**
     * @return BiPredicate Whether $x is the same as $y.
     */
    public static function equals() : BiPredicate {
        return self::$EQUALS ?? self::$EQUALS = new BiPredicate(function($x,$y) {
            $x = (string)$x;
            $y = (string)$y;
            return $x === $y;
        });
    }
    
    public static function lengthIs() : BiPredicate {
        return self::$LENGTH_IS ?? self::$LENGTH_IS = new BiPredicate(function($x, $y) {
            $length = \is_string($x) ? \mb_strlen($x) : \sizeof($x);
            return $length === $y;
        });
    }

    
    /**
     * @return BiPredicate Whether $x.startsWith($y).
     */
    public static function startsWith() : BiPredicate {
        return self::$STARTS_WITH ?? self::$STARTS_WITH = new BiPredicate(function($x,$y) {
            $x = (string)$x;
            $y = (string)$y;
            return \mb_substr($x, 0, \mb_strlen($y)) === $y;
        });
    }
    
        public static function _identity(BiPredicate $condition) : BiPredicate {
        return new BiPredicate(function($x, $y) use ($condition) {
            return $condition->check($x, $y);
        });
    }
    
    public static function _or(BiPredicate $predicate1, BiPredicate $predicate2) : BiPredicate {
        return new BiPredicate(function($x, $y) use ($predicate1, $predicate2) {
            return $predicate1->check($x, $y) or $predicate2->check($x, $y);
        });
    }
    
    public static function _nor(BiPredicate $predicate1, BiPredicate $predicate2) : BiPredicate {
        return new BiPredicate(function($x, $y) use ($predicate1, $predicate2) {
            return !($predicate1->check($x, $y) or $predicate2->check($x, $y));
        });
    }
    
    
    public static function _and(BiPredicate $predicate1, BiPredicate $predicate2) : BiPredicate {
        return new BiPredicate(function($x, $y) use ($predicate1, $predicate2) {
            return $predicate1->check($x, $y) and $predicate2->check($x, $y);
        });
    }
    
    public static function _nand(BiPredicate $predicate1, BiPredicate $predicate2) : BiPredicate {
        return new BiPredicate(function($x, $y) use ($predicate1, $predicate2) {
            return !($predicate1->check($x, $y) and $predicate2->check($x, $y));
        });
    }
    
    public static function _not(BiPredicate $condition) : BiPredicate {
        return new BiPredicate(function($x, $y) use ($condition) {
            return !$condition->check($x, $y);
        });
    }
    
    public static function _xor(BiPredicate $predicate1, BiPredicate $predicate2) {
        return new BiPredicate(function($x, $y) use ($predicate1, $predicate2) {
            return $predicate1->check($x, $y) xor $predicate2->check($x, $y);
        });
    }
}