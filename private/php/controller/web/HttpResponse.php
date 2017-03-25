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

namespace Controller;

use League\Plates\Engine;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Ui\Message;
use Ui\PlaceholderTranslator;
use UnexpectedValueException;
use Util\CmnCnst;
use Util\DebugUtil;

/**
 * A response object that is rendered once a controller finishes processing.
 * @author madgaksha
 */
class HttpResponse extends Response implements HttpResponseInterface {
    /** @var Message[] */
    private $messageList;
    private $templateQueue;
    
    public function __construct($content = '', $status = 200, $headers = array()) {
        parent::__construct($content, $status, $headers);
        $this->messageList = [];
        $this->templateQueue = [];
        $this->setCharset(CmnCnst::HTTP_CHARSET_UTF8);
    }
    
    public function addHeader(string $name, string $value) {
        $this->headers->set($name, $value);
    }

    public function appendContent($fragment) {
        $this->assertContent($fragment);
        $this->content .= (string) $fragment;
    }
    
    public function prependContent($fragment) {
        $this->assertContent($fragment);
        $this->content = ((string) $fragment) . $this->content;
    }

    public function clearHeaders() {
        $this->headers->replace();
    }

    public function setRedirect(string $targetPage) {
        $this->addHeader(CmnCnst::HTTP_HEADER_LOCATION, $targetPage);
        $this->setStatusCode(302);
    }

    public function addCookie(Cookie $cookie) {
        $this->headers->setCookie($cookie);
    }

    public function addMessage(Message $message) {
        if ($message !== null) {
            array_push($this->messageList, $message);
        }
    }

    public function addMessages(array & $messages = []) {
        if ($messages !== null && sizeof($messages) > 0) {
            $this->messageList = array_merge($this->messageList, $messages);
        }
    }
    
    public function sendContent() {
        foreach ($this->templateQueue as $template) {
            $this->renderOneTemplate($template[0], $template[1], $template[2], $template[3], $template[4]);
        }
        $this->addDump();
        parent::sendContent();
    }

    private function renderOneTemplate(string $templateName, array & $data = null, Engine $engine, PlaceholderTranslator $translator, string $lang) {
        $locale = 'de';
        $selfUrl = '';
        $messageList = $this->messageList;
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
            $selfUrl = array_key_exists('PHP_SELF', $_SERVER) ? $_SERVER['PHP_SELF'] : '';
            if (array_key_exists('QUERY_STRING', $_SERVER)) {
                $selfUrl = $selfUrl . '?' . filter_input(INPUT_SERVER, 'QUERY_STRING', FILTER_UNSAFE_RAW);
            }
        }
        else {
            $selfUrl = $data['selfUrl'];
        }
        $engine->addData([
            'i18n' => $translator,
            'locale' => $locale,
            'messages' => $messageList,
            'selfUrl' => $selfUrl
        ]);
        if ($data === null) {
            $this->appendContent($engine->render($templateName));
        }
        else {
            $this->appendContent($engine->render($templateName, $data));
        }
    }

    public function appendTemplate(Engine $engine, PlaceholderTranslator $translator, string $lang, string $templateName, array $data = null) {
        array_push($this->templateQueue, [$templateName, $data, $engine, $translator, $lang]);
    }

    private function addDump() {
        $dump = DebugUtil::getDumpHtml();
        if ($dump !== null) {
            $this->prependContent($dump);
        }
    }

    private function assertContent($fragment) {
        if (null !== $fragment && !is_string($fragment) && !is_numeric($fragment) && !is_callable(array(
                    $fragment, '__toString'))) {
            throw new UnexpectedValueException(sprintf('The Response content must be a string or object implementing __toString(), "%s" given.',
                    gettype($fragment)));
        }
    }
}