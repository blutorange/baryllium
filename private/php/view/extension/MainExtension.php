<?php

namespace PlatesExtension;

use Context;
use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;
use Ui\PlaceholderTranslator;

/**
 * Common functions for our templates.
 *
 * @author madgaksha
 */
class MainExtension implements ExtensionInterface {

    public $template;
    private $context;

    public function __construct(Context $context) {
        $this->context = $context;                
    }
    
    public function register(Engine $engine) {
        $engine->registerFunction('gettext', [$this, 'gettext']);
        $engine->registerFunction('egettext', [$this, 'egettext']);
        $engine->registerFunction('getResource', [$this, 'getResource']);
    }

    /**
     * @param type $path Path relative to this project's root.
     * @return string The path on the server to the requested resource.
     */
    public function getResource($path): string {
        return $this->getContext()->getServerPath($path);
    }
    
    public function getContext() : Context {
        return $this->context;
    }

    /**
     * @param type $key The i18n key.
     * @param array $vars Additional variables replaced in the i18n translation.
     * @return string The value mapped the i18n key for the current language.
     */
    public function gettext(string $key = null, array $vars = null): string {
        if ($key === null) {
            error_log('i18n Key is null.');
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