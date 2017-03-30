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

namespace PlatesExtension;

use Context;
use Entity\User;
use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;
use Ui\PlaceholderTranslator;

/**
 * Common functions for our templates.
 *
 * @author madgaksha
 */
class MainExtension implements ExtensionInterface {

    /** @var \League\Plates\Template */
    public $template;
   
    public function __construct() {
    }
    
    public function register(Engine $engine) {
        $engine->registerFunction('gettext', [$this, 'gettext']);
        $engine->registerFunction('egettext', [$this, 'egettext']);
        $engine->registerFunction('getResource', [$this, 'getResource']);
        $engine->registerFunction('getUser', [$this, 'getUser']);
    }

    /**
     * @param type $path Path relative to this project's root.
     * @return string The path on the server to the requested resource.
     */
    public function getResource($path): string {
        return Context::getInstance()->getServerPath($path);
    }
       
    public function getContext() : Context {
        return Context::getInstance();
    }
    
    public function getUser() : User {
        return Context::getInstance()->getSessionHandler()->getUser();
    }

    /**
     * @param type $key The i18n key.
     * @param array $vars Additional variables replaced in the i18n translation.
     * @return string The value mapped the i18n key for the current language.
     */
    public function gettext(string $key = null, array $vars = null): string {
        if ($key === null) {
            error_log('i18n key is null.');
            return '???NULL???';
        }
        $data = $this->template->data();
        if (!array_key_exists('i18n', $data)) {
            error_log('Translator not set.');
            return "???$key???";
        }
        $translator = $data['i18n'];
        if ($translator === null || !($translator instanceof PlaceholderTranslator)) {
            error_log("Not a translator: " . get_class($translator));
            return "???$key???";
        }
        $val = isset($vars) ? $translator->gettextVar($key, $vars) : $translator->gettext($key);
        if ($val === null || $val === $key) {
            error_log("Unable to find translation for key $key.");
            return "???$key???";
        }
        return $val;
    }
    
    /**
     * Same as gettext, but HTML-escaped the return value.
     * @see MainExtension::getText()
     */
    public function egettext(string $key = null, array $vars = null) : string {
        return $this->template->escape($this->gettext($key, $vars));
    }
}