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

use Doctrine\Common\Comparable;

/**
 * @author madgaksha
 */
class MathUtil {
    private function __construct() {}
    public static function intervalOverlap(int $x1, int $x2, int $y1, int $y2) : bool {
        return $x1 <= $y2 && $y1 <= $x2;
    }

    /**
     * @param mixed|Comparable $x A value comparable with y.
     * @param mixed|Comparable $y A value comparable with x.
     * @return mixed The larger of the two values, or one of both if they are equal.
     */
    public static function max($x, $y) {
        if ($x instanceof Comparable && $y instanceof Comparable) {
            return $x->compareTo($y) < 0 ? $x : $y;
        }
        return $x < $y ? $y : $x;
    }
    
        /**
     * @param mixed|Comparable $x A value comparable with y.
     * @param mixed|Comparable $y A value comparable with x.
     * @return mixed The smaller of the two values, or one of both if they are equal.
     */
    public static function min($x, $y) {
        if ($x instanceof Comparable && $y instanceof Comparable) {
            return $x->compareTo($y) < 0 ? $y : $x;
        }
        return $x < $y ? $x : $y;
    }

    public static function clamp($value, $min, $max) {
        return $value < $min ? min : ($value > $max ? $max : $value);
    }

}
