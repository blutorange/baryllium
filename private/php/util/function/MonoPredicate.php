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
class MonoPredicate implements MonoPredicateInterface {
    private static $EMTPY;
    private static $NULL;
    private static $TRUTHY;
    private static $FALSY;
    private static $NOT_EMPTY;
    private static $NOT_NULL;
    private static $CONSTANT_TRUE;
    private static $CONSTANT_FALSE;
    
    private $predicate;
    private $name;

    public function __construct(callable $predicate, string $name = null) {
        $this->predicate = $predicate;
        $this->name = $name ?? "MonoPredicate";
    }

    public function __toString() : string {
        return $this->name;
    }

    public function check($x) : bool {
        return \call_user_func($this->predicate, $x);
    }
    
    public static function bindFirst(BiPredicate $predicate, $x) : MonoPredicateInterface {
        return new MonoPredicate(function($y) use ($predicate, $x) {
            return $predicate->check($x, $y);
        }, $predicate . "[1=>$x]");
    }
    
    public static function bindSecond(BiPredicate $predicate, $y) : MonoPredicateInterface {
        return new MonoPredicate(function($x) use ($predicate, $y) {
            return $predicate->check($x, $y);
        }, $predicate . "[2=>$y]");
    }
    
    public static function equals($x) : MonoPredicateInterface {
        return BiPredicate::equals()->bindSecond($x);
    }
    
    public static function startsWith($x) : MonoPredicateInterface {
        return BiPredicate::startsWith()->bindSecond($x);
    }
    
    public static function _empty() : MonoPredicateInterface {
        return self::$EMTPY ?? self::$EMTPY = new MonoPredicate(function($x) {
            return empty($x);
        }, 'empty');
    }
        
    public static function _notEmpty() : MonoPredicateInterface {
        return self::$NOT_EMPTY ?? self::$NOT_EMPTY = new MonoPredicate(function($x) {
            return !empty($x);
        }, 'notEmpty');
    }
    
    public static function _null() : MonoPredicateInterface {
        return self::$NULL ?? self::$NULL = new MonoPredicate(function($x) {
            return $x === null;
        }, 'null');
    }

    public static function _truthy() : MonoPredicateInterface {
        return self::$TRUTHY ?? self::$TRUTHY = new MonoPredicate(function($x) {
            return !!$x;
        }, 'truthy');
    }
    
    public static function _falsy() : MonoPredicateInterface {
        return self::$FALSY ?? self::$FALSY = new MonoPredicate(function($x) {
            return !$x;
        }, 'falsy');
    }
    
    public static function _notNull() : MonoPredicateInterface {
        return self::$NOT_NULL ?? self::$NOT_NULL = new MonoPredicate(function($x) {
            return $x !== null;
        }, 'notNull');
    }

    public static function _identity(MonoPredicate $predicate) : MonoPredicateInterface {
        return new MonoPredicate(function($x) use ($predicate) {
            return $predicate->check($x);
        }, "identity($predicate)");
    }
    
    public static function _or(MonoPredicate $predicate1, MonoPredicate $predicate2) : MonoPredicateInterface {
        return new MonoPredicate(function($x) use ($predicate1, $predicate2) {
            return $predicate1->check($x) or $predicate2->check($x);
        }, "or($predicate1,$predicate2)");
    }
    
    public static function _nor(MonoPredicate $predicate1, MonoPredicate $predicate2) : MonoPredicateInterface {
        return new MonoPredicate(function($x) use ($predicate1, $predicate2) {
            return !($predicate1->check($x) or $predicate2->check($x));
        }, "nor($predicate1,$predicate2)");
    }
    
    
    public static function _and(MonoPredicate $predicate1, MonoPredicate $predicate2) : MonoPredicateInterface {
        return new MonoPredicate(function($x) use ($predicate1, $predicate2) {
            return $predicate1->check($x) and $predicate2->check($x);
        }, "and($predicate1,$predicate2)");
    }
    
    public static function _nand(MonoPredicate $predicate1, MonoPredicate $predicate2) : MonoPredicateInterface {
        return new MonoPredicate(function($x) use ($predicate1, $predicate2) {
            return !($predicate1->check($x) and $predicate2->check($x));
        }, "nand($predicate1,$predicate2)");
    }
    
    public static function _not(MonoPredicate $predicate) : MonoPredicateInterface {
        return new MonoPredicate(function($x) use ($predicate) {
            return !$predicate->check($x);
        }, "not($predicate)");
    }
    
    public static function _xor(MonoPredicate $predicate1, MonoPredicate $predicate2) : MonoPredicateInterface {
        return new MonoPredicate(function($x) use ($predicate1, $predicate2) {
            return $predicate1->check($x) xor $predicate2->check($x);
        }, "xor($predicate1,$predicate2)");
    }
    
    public static function custom($callback, string $name = null) : MonoPredicateInterface {
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException("Must be a valid callback");
        }
        return new MonoPredicate($callback, $name);
    }

    public static function constant(bool $bool) : MonoPredicateInterface {
        if ($bool) {
            return self::$CONSTANT_TRUE ?? self::$CONSTANT_TRUE = new MonoPredicate(function($x) {
                return true;
            }, 'constant(true)');
        }
        return self::$CONSTANT_FALSE ?? self::$CONSTANT_FALSE = new MonoPredicate(function($x) {
            return false;
        }, 'constant(false)');
    }
}