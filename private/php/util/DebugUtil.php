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

namespace Util;

use Context;
use Exception;
use Kint;

/**
 * Description of DebugUtil
 *
 * @author madgaksha
 */
class DebugUtil {
    private function __construct() {}
    
    private static $DUMP_LIST = [];
    
    public static function dump($data = null, string $label = null) {
        if (!Kint::enabled()) {
            $context = Context::getInstance();
            if ($context !== null && ($context->isMode(Context::$MODE_DEVELOPMENT) || $context->isMode(Context::$MODE_TESTING))) {
                Kint::enabled(Kint::MODE_RICH);
            }
        }
        if (!Kint::enabled()) {
            error_log('Warning: DebugUtil::DUMP left in production code.');
            return;
        }

        Kint::$maxLevels = 5;
        Kint::$returnOutput = true;
        Kint::$maxStrLength = 255;
        if (headers_sent()) {
            echo self::makeDump($data, $label);
        }
        else {
            array_push(self::$DUMP_LIST, self::makeDump($data, $label));
        }
    }
    
    /** @return string The dump HTML, or null when there are no dumps. */
    public static function getDumpHtml() {
        return sizeof(self::$DUMP_LIST) === 0 ? null : implode('', self::$DUMP_LIST);
    }
    
    public static function sendDump() {
        echo self::getDumpHtml();
    }

    private static function makeDump($data = null, string $label = null) {
        $message = $label !== null ? htmlentities($label) : null;
        $body = Kint::dump($data);
        if ($message !== null) {
            return '<div class="kint" style="margin:6px;"><dt class="panel-heading">Debug output: ' . $message . '</dt><div style="padding-left:1em;border:2px solid #e0eaef;background-color:#f8f8f8";>' . $body . '</div></div>';
        }
        else {
            return Kint::dump($data);
        }
    }
}
