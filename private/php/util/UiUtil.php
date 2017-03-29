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

use League\Plates\Engine;
use Ui\PlaceholderTranslator;

/**
 * @author Philipp
 */
class UiUtil {

    private function __construct() {
        
    }

    /**
     * 
     * @return string The rendered template as HTML.
     */
    public static function renderTemplateToHtml(string $templateName,
            Engine $engine, PlaceholderTranslator $translator,
            array & $messageList = null, string $lang = 'de', array & $data = null): string {
        $locale = 'de';
        $selfUrl = '';
        $messageList = $messageList ?? [];
        if ($data !== null && array_key_exists('messages', $data)) {
            $messageList = array_merge($messageList, $data['messages']);
        }
        if ($data !== null && array_key_exists('locale', $data)) {
            $locale = $data['locale'];
        }
        else {
            $locale = $lang;
        }
        if (empty($data) || !array_key_exists('selfUrl', $data)) {
            $selfUrl = array_key_exists('PHP_SELF', $_SERVER) ? $_SERVER['PHP_SELF']
                        : '';
            if (array_key_exists('QUERY_STRING', $_SERVER)) {
                $selfUrl = $selfUrl . '?' . filter_input(INPUT_SERVER,
                                'QUERY_STRING', FILTER_UNSAFE_RAW);
            }
        }
        else {
            $selfUrl = $data['selfUrl'];
        }
        $engine->addData([
            'i18n'     => $translator,
            'locale'   => $locale,
            'messages' => $messageList,
            'selfUrl'  => $selfUrl,
        ]);
        if ($data === null) {
            return $engine->render($templateName);
        }
        else {
            return $engine->render($templateName, $data);
        }
    }

}
