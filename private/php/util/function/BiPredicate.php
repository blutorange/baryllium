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

use function mb_substr;

/**
 * Encapsulates a condition with two arguments. For example:
 * <code>BiPredicate::startsWith()->bindSecond('World')->check('Hello World');</code>
 * 
 * @author madgaksha
 */
class BiPredicate implements BiPredicateInterface {
    private static $EQUALS;
    private static $STARTS_WITH;
    private static $CONTAINS;
    private static $LENGTH_IS;
    private static $CONSTANT_TRUE;
    private static $CONSTANT_FALSE;

    
    private $predicate;
    private $name;

    public function __construct(callable $predicate, string $name = null) {
        $this->predicate = $predicate;
        $this->name = $name ?? "BiPredicate";
    }

    public function __toString() : string {
        return $this->name;
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
    public static function equals() : BiPredicateInterface {
        return self::$EQUALS ?? self::$EQUALS = new BiPredicate(function($x,$y) {
            $x = (string)$x;
            $y = (string)$y;
            return $x === $y;
        }, 'equals');
    }
    
    public static function lengthIs() : BiPredicateInterface {
        return self::$LENGTH_IS ?? self::$LENGTH_IS = new BiPredicate(function($x, $y) {
            $length = \is_string($x) ? \mb_strlen($x) : \sizeof($x);
            return $length === $y;
        }, 'lengthIs');
    }

    /**
     * @return BiPredicate Whether $x.startsWith($y).
     */
    public static function startsWith() : BiPredicateInterface {
        return self::$STARTS_WITH ?? self::$STARTS_WITH = new BiPredicate(function($x,$y) {
            $x = (string)$x;
            $y = (string)$y;
            return mb_substr($x, 0, \mb_strlen($y)) === $y;
        }, 'startsWith');
    }
    
    /**
     * @return BiPredicate Whether $x contains $y.
     */
    public static function contains() : BiPredicateInterface {
        return self::$CONTAINS ?? self::$CONTAINS = new BiPredicate(function($x,$y) {
            $x = (string)$x;
            $y = (string)$y;
            return \mb_strpos($x, $y) !== false;
        }, 'contains');
    }

    public static function _identity(BiPredicate $condition) : BiPredicateInterface {
        return new BiPredicate(function($x, $y) use ($condition) {
            return $condition->check($x, $y);
        }, 'identity');
    }
    
    public static function _or(BiPredicate $predicate1, BiPredicate $predicate2) : BiPredicateInterface {
        return new BiPredicate(function($x, $y) use ($predicate1, $predicate2) {
            return $predicate1->check($x, $y) or $predicate2->check($x, $y);
        }, "or($predicate1,$predicate2)");
    }
    
    public static function _nor(BiPredicate $predicate1, BiPredicate $predicate2) : BiPredicateInterface {
        return new BiPredicate(function($x, $y) use ($predicate1, $predicate2) {
            return !($predicate1->check($x, $y) or $predicate2->check($x, $y));
        }, "nor($predicate1,$predicate2)");
    }
    
    
    public static function _and(BiPredicate $predicate1, BiPredicate $predicate2) : BiPredicateInterface {
        return new BiPredicate(function($x, $y) use ($predicate1, $predicate2) {
            return $predicate1->check($x, $y) and $predicate2->check($x, $y);
        }, "and($predicate1,$predicate2)");
    }
    
    public static function _nand(BiPredicate $predicate1, BiPredicate $predicate2) : BiPredicateInterface {
        return new BiPredicate(function($x, $y) use ($predicate1, $predicate2) {
            return !($predicate1->check($x, $y) and $predicate2->check($x, $y));
        }, "nand($predicate1,$predicate2)");
    }
    
    public static function _not(BiPredicate $predicate) : BiPredicateInterface {
        return new BiPredicate(function($x, $y) use ($predicate) {
            return !$predicate->check($x, $y);
        }, "not($predicate)");
    }
    
    public static function _xor(BiPredicate $predicate1, BiPredicate $predicate2) : BiPredicateInterface {
        return new BiPredicate(function($x, $y) use ($predicate1, $predicate2) {
            return $predicate1->check($x, $y) xor $predicate2->check($x, $y);
        }, "xor($predicate1,$predicate2)");
    }
    
    public static function custom($callback, string $name = null) : BiPredicateInterface {
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException("Must be a valid callback");
        }
        return new BiPredicate($callback, $name);
    }
    
    public static function constant(bool $bool) : BiPredicateInterface {
        if ($bool) {
            return self::$CONSTANT_TRUE ?? self::$CONSTANT_TRUE = new BiPredicate(function($x){return true;});
        }
        return self::$CONSTANT_FALSE ?? self::$CONSTANT_FALSE = new BiPredicate(function($x){return false;});
    }
}