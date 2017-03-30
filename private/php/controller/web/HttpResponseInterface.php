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

namespace Moose\Web;

use League\Plates\Engine;
use Symfony\Component\HttpFoundation\Cookie;
use Ui\Message;
use Ui\PlaceholderTranslator;

interface HttpResponseInterface extends BaseResponseInterface {
    public function addHeader(string $name, string $value);
    /**
     * Replaces the header with the given value. Removes the header when
     * value is null.
     * @param string $name
     * @param string $value
     */
    public function replaceHeader(string $name, string $value = null);
    public function clearHeaders();
    public function setContent($body);
    public function addCookie(Cookie $cookie);
    public function setRedirect(string $targetPage);
    public function appendContent($fragment);
    public function prependContent($fragment);
    public function setStatusCode($code, $text = null);
    public function setMime(string $mimeType);
    public function setKeepAlive(bool $keepAlive);
    public function sendHeaders();
    public function sendContent();
    public function send();
    /** @var Message */
    public function addMessage(Message $message);
    /** @param Message[] $messages */
    public function addMessages(array & $messages = []);
    public function appendTemplate(string $templateName, Engine $engine, PlaceholderTranslator $translator, string $lang, array $data = null);
}