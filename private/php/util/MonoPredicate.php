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

/**
 * Encapsulates a condition with a single argument. For example, to check
 * whether a string is not null and cotains 5 characters:
 * <code>
 * MonoPredicate::_and(MonoPredicate::_notNull(), BiPredicate::lengthIs()->bindSecond(5))->check("12345", 5);
 * </code>
 *
 * @author madgaksha
 */
class MonoPredicate {
    private static $EMTPY;
    private static $NULL;
    private static $NOT_EMTPY;
    private static $NOT_NULL;
    
    private $predicate;

    public function __construct(callable $predicate) {
        $this->predicate = $predicate;
    }
    
    public function check($x) : bool {
        return \call_user_func($this->predicate, $x);
    }
    
    public static function bindFirst(BiPredicate $predicate, $x) : MonoPredicate {
        return new MonoPredicate(function($y) use ($predicate, $x) {
            return $predicate->check($x, $y);
        });
    }
    
    public static function bindSecond(BiPredicate $predicate, $y) : MonoPredicate {
        return new MonoPredicate(function($x) use ($predicate, $y) {
            return $predicate->check($x, $y);
        });
    }
    
    public static function equals($x) : MonoPredicate {
        return BiPredicate::equals()->bindSecond($x);
    }
    
    public static function startsWith($x) : MonoPredicate {
        return BiPredicate::startsWith()->bindSecond($x);
    }
    
    public static function _empty() : MonoPredicate {
        return self::$EMTPY ?? self::$EMTPY = new MonoPredicate(function($x) {
            return empty($x);
        });
    }
        
    public static function _notEmpty() : MonoPredicate {
        return self::$NOT_EMPTY ?? self::$NOT_EMTPY = new MonoPredicate(function($x) {
            return !empty($x);
        });
    }
    
    public static function _null() : MonoPredicate {
        return self::$NULL ?? self::$NULL = new MonoPredicate(function($x) {
            return $x === null;
        });
    }

    public static function _truthy() : MonoPredicate {
        return self::$TRUTHY ?? self::$TRUTHY = new MonoPredicate(function($x) {
            return !!$x;
        });
    }
    
    public static function _falsey() : MonoPredicate {
        return self::$FALSY ?? self::$FALSY = new MonoPredicate(function($x) {
            return !$x;
        });
    }
    
    public static function _notNull() : MonoPredicate {
        return self::$NOT_NULL ?? self::$NOT_NULL = new MonoPredicate(function($x) {
            return $x !== null;
        });
    }

    public static function _identity(MonoPredicate $predicate) : MonoPredicate {
        return new MonoPredicate(function($x) use ($predicate) {
            return $predicate->check($x);
        });
    }
    
    public static function _or(MonoPredicate $predicate1, MonoPredicate $predicate2) : MonoPredicate {
        return new MonoPredicate(function($x) use ($predicate1, $predicate2) {
            return $predicate1->check($x) or $predicate2->check($x);
        });
    }
    
    public static function _nor(MonoPredicate $predicate1, MonoPredicate $predicate2) : MonoPredicate {
        return new MonoPredicate(function($x) use ($predicate1, $predicate2) {
            return !($predicate1->check($x) or $predicate2->check($x));
        });
    }
    
    
    public static function _and(MonoPredicate $predicate1, MonoPredicate $predicate2) : MonoPredicate {
        return new MonoPredicate(function($x) use ($predicate1, $predicate2) {
            return $predicate1->check($x) and $predicate2->check($x);
        });
    }
    
    public static function _nand(MonoPredicate $predicate1, MonoPredicate $predicate2) : MonoPredicate {
        return new MonoPredicate(function($x) use ($predicate1, $predicate2) {
            return !($predicate1->check($x) and $predicate2->check($x));
        });
    }
    
    public static function _not(MonoPredicate $predicate) : MonoPredicate {
        return new MonoPredicate(function($x) use ($predicate) {
            return !$predicate->check($x);
        });
    }
    
    public static function _xor(MonoPredicate $predicate1, MonoPredicate $predicate2) {
        return new MonoPredicate(function($x) use ($predicate1, $predicate2) {
            return $predicate1->check($x) xor $predicate2->check($x);
        });
    }
}