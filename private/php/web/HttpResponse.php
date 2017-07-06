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

namespace Moose\Web;

use GuzzleHttp\Psr7\Uri;
use League\Plates\Engine;
use Moose\Context\Context;
use Moose\Util\CmnCnst;
use Moose\Util\DebugUtil;
use Moose\Util\PlaceholderTranslator;
use Moose\Util\UiUtil;
use Moose\ViewModel\Message;
use Moose\ViewModel\MessageInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;
use UnexpectedValueException;

/**
 * A response object that is rendered once a controller finishes processing.
 * @author madgaksha
 */
class HttpResponse extends Response implements HttpResponseInterface {
    /** @var MessageInterface[] */
    private $messageList;
    private $templateQueue;
    private $redirectUrl;
    private $redirectUrlParams;
    private $redirectUrlFragment;
    private $redirectUrlMessages;
    private $mayDump = true;

    public function __construct($content = '', $status = 200, $headers = []) {
        parent::__construct($content, $status, $headers);
        $this->messageList = [];
        $this->templateQueue = [];
        $this->redirectUrlParams = [];
        $this->redirectUrlMessages = [];
        $this->setCharset(CmnCnst::HTTP_CHARSET_UTF8);
    }
    
    public function addHeader(string $name, string $value) {
        $this->headers->set($name, $value);
    }
    
    public function setMayDump(bool $mayDump) {
        $this->mayDump = $mayDump;
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

    public function setRedirect(string $targetPage = null) {
        $this->redirectUrl = $targetPage;
    }
    
    public function setRedirectRelative(string $targetPage = null) {
        if ($targetPage === null) {
            $this->redirectUrl = null;
        }
        else {
            $this->setRedirect(Context::getInstance()->getServerPath($targetPage));
        }
    }
    
    public function addRedirectUrlParam(string $key, string $value) {
        $this->redirectUrlParams[$key] = $value;
    }
    
    public function addRedirectUrlMessage(string $name, int $type = null) {
        $typeName = Message::nameForType($type, Message::TYPE_WARNING);
        $this->redirectUrlMessages []= "$name:$typeName";
    }
    
    public function setRedirectUrlFragment(string $fragment = null) {
        $this->redirectUrlFragment = $fragment;
    }

    public function addCookie(Cookie $cookie) {
        $this->headers->setCookie($cookie);
    }

    public function addMessage(MessageInterface $message) {
        if ($message !== null) {
            array_push($this->messageList, $message);
        }
    }

    public function addMessages(array & $messages = []) {
        if ($messages !== null && sizeof($messages) > 0) {
            $this->messageList = array_merge($this->messageList, $messages);
        }
    }
    
    public function sendHeaders() {
        if ($this->redirectUrl !== null) {
            if (\sizeof($this->redirectUrlMessages) > 0) {
                $this->addRedirectUrlParam(CmnCnst::URL_PARAM_SYSTEM_MESSAGE, \implode(',', $this->redirectUrlMessages));
            }            
            $this->addHeader(CmnCnst::HTTP_HEADER_LOCATION, (string)($this->prepareRedirectUrl()));
            $this->setStatusCode(302);
        }
        if (!$this->headers->has('Content-Language')) {
            $this->addHeader('Content-Language', Context::getInstance()->getSessionHandler()->getLang());
        }
        parent::sendHeaders();
    }
    
    private function prepareRedirectUrl() : Uri {
        // Add requested URL params to redirect URL.
        $uri = new Uri($this->redirectUrl);
        $parsedQuery = [];
        \parse_str($uri->getQuery(), $parsedQuery);
        unset($parsedQuery[CmnCnst::URL_PARAM_PRIVATE_KEY]);
        $newQuery = \http_build_query(\array_merge($parsedQuery, $this->redirectUrlParams));
        $uri = $uri->withQuery($newQuery);
        if ($this->redirectUrlFragment !== null) {
            $uri = $uri->withFragment($this->redirectUrlFragment);
        }
        return $uri;
    }
    
    public function sendContent() {
        foreach ($this->templateQueue as $template) {
            $this->renderOneTemplate($template[0], $template[1], $template[2], $template[3], $template[4]);
        }
        if ($this->mayDump) {
            $this->addDump();
        }
        parent::sendContent();
    }

    private function renderOneTemplate(string $templateName, Engine $engine,
            PlaceholderTranslator $translator, string $lang,
            array & $data = null) {
        $html = UiUtil::renderTemplateToHtml($templateName, $engine,
                        $translator, $this->messageList, $lang, $data);
        $this->appendContent($html);
    }

    public function appendTemplate(string $templateName, Engine $engine,
            PlaceholderTranslator $translator, string $lang,
            array $data = null) {
        array_push($this->templateQueue, [$templateName, $engine, $translator, $lang, $data]);
    }

    private function addDump() {
        $dump = DebugUtil::getDumpHtml();
        if ($dump !== null) {
            $this->prependContent($dump);
        }
    }

    private function assertContent($fragment) {
        if (null !== $fragment && !is_string($fragment) && !is_numeric($fragment) && !is_callable([
                    $fragment, '__toString'])) {
            throw new UnexpectedValueException(sprintf('The Response content must be a string or object implementing __toString(), "%s" given.',
                    gettype($fragment)));
        }
    }

    public function setError(int $code, MessageInterface $errorMessage = null) {
        $this->addMessage($errorMessage);
    }

    public function setMime(string $mimeType) {
        $this->replaceHeader('Content-Type', $mimeType);
    }

    public function setKeepAlive(bool $keepAlive) {
        $this->replaceHeader('Connection', $keepAlive ? 'keep-alive' : 'close');
    }
    
    public function replaceHeader(string $name, string $value = null) {
        $this->headers->remove($name);
        if ($value !== null) {
            $this->addHeader($name, $value);
        }
    }
}