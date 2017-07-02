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

namespace Moose\PlatesExtension;

use DateTime;
use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;
use League\Plates\Template\Template;
use Moose\Context\Context;
use Moose\Context\MooseConfig;
use Moose\Entity\User;
use Moose\Util\PlaceholderTranslator;
use Moose\ViewModel\SectionBasic;
use Moose\ViewModel\SectionInterface;

/**
 * Common functions for our templates.
 *
 * @author madgaksha
 */
class PlatesMooseExtension implements ExtensionInterface {

    /** @var Template */
    public $template;
    
    /** @var SectionInterface */
    private $activeSection;
   
    public function __construct() {
    }
    
    public function register(Engine $engine) {
        $engine->registerFunction('j', [$this, 'escapeJavascript']);
        $engine->registerFunction('escapeJavascript', [$this, 'escapeJavascript']);
        $engine->registerFunction('gettext', [$this, 'gettext']);
        $engine->registerFunction('egettext', [$this, 'egettext']);
        $engine->registerFunction('getTranslator', [$this, 'getTranslator']);
        $engine->registerFunction('getResource', [$this, 'getResource']);
        $engine->registerFunction('egetResource', [$this, 'egetResource']);
        $engine->registerFunction('edatetime', [$this, 'edatetime']);
        $engine->registerFunction('edate', [$this, 'edate']);
        $engine->registerFunction('getUser', [$this, 'getUser']);
        $engine->registerFunction('setActiveSection', [$this, 'setActiveSection']);
        $engine->registerFunction('getActiveSection', [$this, 'getActiveSection']);
        $engine->registerFunction('getCookieOption', [$this, 'getCookieOption']);
        $engine->registerFunction('config', [$this, 'config']);
        $engine->registerFunction('serializeHtmlData', [$this, 'serializeHtmlData']);
    }

    /**
     * @param string $path Path relative to this project's root.
     * @return string The path on the server to the requested resource.
     */
    public function getResource($path): string {
        return Context::getInstance()->getServerPath($path);
    }
       
    public function getContext() : Context {
        return Context::getInstance();
    }
    
    public function getUser() : User {
        return Context::getInstance()->getUser();
    }

    public function getTranslator(): PlaceholderTranslator {
        $data = $this->template->data();
        if (!array_key_exists('i18n', $data)) {
            \error_log('Translator not set.');
            return new PlaceholderTranslator('de');
        }
        $translator = $data['i18n'];
        if ($translator === null || !($translator instanceof PlaceholderTranslator)) {
            \error_log("Not a translator: " . (\is_object($translator) ? get_class($translator) : print_r($translator, true)));
            return new PlaceholderTranslator('de');
        }
        return $translator;        
    }
    
    /**
     * @param string $key The i18n key.
     * @param array $vars Additional variables replaced in the i18n translation.
     * @return string The value mapped the i18n key for the current language.
     */
    public function gettext(string $key = null, array $vars = null): string {
        if ($key === null) {
            \error_log('i18n key is null.');
            return '???NULL???';
        }
        $translator = $this->getTranslator();
        $val = isset($vars) ? $translator->gettextVar($key, $vars) : $translator->gettext($key);
        if ($val === null || $val === $key) {
            \error_log("Unable to find translation for key $key.");
            return "???$key???";
        }
        return $val;
    }
    
    /**
     * Same as gettext, but HTML-escapes the return value.
     * @see PlatesMooseExtension::getText()
     */
    public function egettext(string $key = null, array $vars = null) : string {
        return $this->template->escape($this->gettext($key, $vars));
    }
    
    /**
     * Same as getResource, but HTML-escapes the return value.
     * @see PlatesMooseExtension::getResource()
     */
    public function egetResource(string $path) : string {
        return $this->template->escape($this->getResource($path));
    }
    
    
    public function getActiveSection(): SectionInterface {
        return $this->activeSection ?? SectionBasic::$NONE;
    }

    public function setActiveSection(SectionInterface $activeSection = null) {
        $this->activeSection = $activeSection;
    }
    
    public function config() : MooseConfig {
        return Context::getInstance()->getConfiguration();
    }
    
    public function edatetime(DateTime $dateTime) {
        return $this->e($dateTime->format($this->gettext('default.datetime.format')));
    }
    
    public function edate(DateTime $dateTime) {
        return $this->template->e($dateTime->format($this->gettext('default.date.format')));
    }
    
    public function escapeJavascript($data) : string {
        return \json_encode($data) ?? '""';
        // May or may not break Javascript code, but makes sure the HTML is valid.
        return \str_replace('</script', '</sc\\ript', $string);
    }
    
    public function getCookieOption(string $type, string $key, $defaultValue = null) {
        return Context::getInstance()->getRequest()->getCookieOption($type, $key, $defaultValue);
    }
    
    public function serializeHtmlData(array $data) : string {
        return \implode(' ', \array_map(function($value, $key) {
            return "data-$key=\"" . $this->template->e($value) . '"';
        }, $data, array_keys($data)));
    }
}