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
class RandomUtil {
    const CHAR_SEQUENCE_CAPITAL_AZ = 1;
    const CHAR_SEQUENCE_LOWERCASE_AZ = 2;
    const CHAR_SEQUENCE_DIGITS = 4;
    
    private function __construct() {}
    
    public static function randomMail() : string {
        $name = self::randomString(2, 12);
        $host = array_rand([
            'freenet.de', 'gmail.com', 'gmx.de', 'arcor.de', 'yahoo.co.jp'
        ]);
        return "$name@$host";
    }
    
    public static function randomString(int $min = 1, int $max = 10) : string {
        $string = base64_encode(random_bytes($max));
        return mb_substr($string, rand($min, $max));
    }
    
    /**
     * 
     * @param int $length
     * @return string 
     * @author http://www.xeweb.net/2011/02/11/generate-a-random-string-a-z-0-9-in-php/
     */
    public static function randomCharSequence(int $length = 6, int $flags = 1|2) : string {
	$str = "";
	$characters = array_merge(
            ($flags & self::CHAR_SEQUENCE_CAPITAL_AZ) !== 0 ? range('A','Z') : [],
            ($flags & self::CHAR_SEQUENCE_LOWERCASE_AZ) !== 0 ? range('a','z') : [],
            ($flags & self::CHAR_SEQUENCE_DIGITS) !== 0 ? range('0','9') : []
    );
	$max = count($characters) - 1;
	for ($i = 0; $i < $length; $i++) {
		$rand = mt_rand(0, $max);
		$str .= $characters[$rand];
	}
	return $str;
}
}
