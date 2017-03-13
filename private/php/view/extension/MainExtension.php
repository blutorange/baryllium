<?php

namespace PlatesExtension;

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

    public function register(Engine $engine) {
        $engine->registerFunction('gettext', [$this, 'gettext']);
        $engine->registerFunction('getResource', [$this, 'getResource']);
    }

    public function getResource($path): string {
        return $GLOBALS['context']->getServerPath($path);
    }
    
    public function gettext($key, array $vars = null): string {
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
}