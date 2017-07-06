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

require_once '../../bootstrap.php';

use Gettext\Translations;

$languages = ["de", "en"];
$all = array();

echo "<!DOCTYPE html><html><head><title>Check I18N file</title></head><body>";

for($i = 0; $i < sizeof($languages); ++$i) {
    $lang = $languages[$i];
    $cur = array();
    $me = dirname(__FILE__);
    $path = "$me/../../resource/locale/$lang/LC_MESSAGES/i18n.po";
    $trs = Translations::fromPoFile($path);
    foreach($trs->getIterator() as $t) {
        if (empty($t->getTranslation())) {
            $key = $t->getOriginal();
            echo "Translation for key $key and language $lang is empty.";
        }
        $key = $t->getOriginal();
        if ($i > 0) {
            if (!array_key_exists($key, $all)) {
                $l = $languages[$i-1];
                echo("<p>Key $key does not exist for language $l.</p>\n");
            }
        }
        $cur[$key] = true;
        $all[$key] = true;
    }
    if ($i > 0) {
        foreach ($all as $key => $_) {
            if (!array_key_exists($key, $cur)) {
                echo("<p>Key $key does not exist for language $lang.</p>\n");
            }
        }
    }
}

echo "<p>All done.</p>";
echo "</body></html>";