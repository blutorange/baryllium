<?php

/* Note: This license has also been called the "New BSD License" or "Modified
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

use Gettext\Translator;
use Symfony\Component\Translation\TranslatorInterface;
use function mb_strlen;
use function mb_strpos;
use function mb_substr;

/**
 * Pretty much the same as \Gettext\Translator, but there are additional methods
 * accepting an optional second argument of type array with a list of variables
 * to be replaced in the returned string. For example, when the translation
 * contains an entry
 * 
 * <pre>
 *  strid "error.pass"
 *  strmsg "Minimum number of characters is {count}."
 * </pre>
 * 
 * the following method call will replace the placeholder `count`.
 * 
 * <pre>$translator->getText('error.pass', ['count' => 5]);</pre>
 * 
 * You may use a backslash to escape any character, two backslashes to insert
 * a literal backslash.
 *
 * @author madgaksha
 */
class PlaceholderTranslator extends Translator implements TranslatorInterface {
    private $locale;
    private $translations;
    public function __construct(string $locale, array $translations = []) {
        $this->locale = $locale;
        $this->translations = $translations;
    }
    
    public function gettext($key) {
        return $this->translations[$key] ?? "???$key???";
    }
    
    public function gettextVar($key, array $vars = null) {
        $val = $this->translations[$key] ?? "???$key???";
        if ($vars !== null && $val !== null && sizeof($vars) > 0) {
            return self::vars($val, $vars);
        }
        return $val;
    }
    
    private static function vars(string $original, array $vars) : string {
        $chars = \preg_split('/(?<!^)(?!$)/u', $original);
        $len = \sizeof($chars);
        $buffer = \array_fill(0, $len, '');
        $out_pos = 0;
        $out_chars = 0;
        $i = 0;
        while ($i < $len) {
            switch ($chars[$i]) {
                case "\\":
                    if ($i < $len - 1) {
                        ++$i;
                    }
                    $buffer[$out_pos] = $chars[$i];
                    ++$out_chars;
                    ++$out_pos;
                    ++$i;
                    break;
                case '{':
                    // Look for the closing parenthesis.
                    $closing = mb_strpos($original, '}', $i);
                    if ($closing === false) {
                        // Bad syntax? Let's just use the string literally.
                        $buffer[$out_pos] = $chars[$i];
                        ++$out_chars;
                        ++$out_pos;
                        ++$i;
                    }
                    else {
                        $var = mb_substr($original, $i + 1, $closing - $i - 1);
                        $buffer[$out_pos] = array_key_exists($var, $vars) ? $vars[$var] : '{' . $var . '}';
                        $out_chars += mb_strlen($buffer[$out_pos]);
                        ++$out_pos;
                        $i = $closing + 1;
                    }
                    break;
                default:
                    $buffer[$out_pos] = $chars[$i];
                    ++$out_chars;
                    ++$out_pos;
                    ++$i;
            }
        }
        return mb_substr(\implode($buffer), 0, $out_chars);
    }

    public function getLocale(): string {
        return $this->locale;
    }

    /** @deprecated This translator is meant only for a specific locale. */
    public function setLocale($locale) {
        $this->locale = $locale ?? 'de';
    }

    public function trans($id, array $parameters = [], $domain = null,
                          $locale = null): string {
        $params = [];
        foreach ($parameters as $key => $value) {
            $params[trim(mb_substr($key, 2, mb_strlen($key)-4))] = $value;
        }
        return $this->gettextVar("$domain.$id", $params);
    }

    public function transChoice($id, $number, array $parameters = [],
            $domain = null, $locale = null): string {
        return $this->trans($id, $parameters, $domain, $locale);
    }
}