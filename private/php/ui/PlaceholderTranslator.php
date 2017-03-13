<?php

namespace Ui;

use Gettext\Translator;

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
class PlaceholderTranslator extends Translator {
    public function gettextVar($original, array $vars = null) {
        return $this->processVars(parent::gettext($original), $vars);
    }
    
    public function dnpgettextVar($domain, $context, $original, $plural, $value, array $vars = null) {
        return $this->processVars(parent::dnpgettext($domain, $context, $original, $plural, $value), $vars);
    }
    
    public function dngettextVar($domain, $original, $plural, $value, array $vars = null) {
        return $this->processVars(parent::dngettext($domain, $original, $plural, $value), $vars);
    }
    
    public function dgettextVar($domain, $original, array $vars = null) {
        return $this->processVars(parent::dgettext($domain, $original), $vars);
    }

    public function dpgettextVar($domain, $context, $original, array $vars = null) {
        return $this->processVars(parent::dpgettext($domain, $context, $original), $vars);
    }

    public function ngettextVar($original, $plural, $value, array $vars = null) {
        return $this->processVars(parent::ngettext($original, $plural, $value), $vars);
    }
    
    public function npgettextVar($context, $original, $plural, $value, array $vars = null) {
        return $this->processVars(parent::npgettext($context, $original, $plural, $value), $vars);
    }

    public function pgettextVar($context, $original, array $vars = null) {
        return $this->processVars(parent::pgettext($context, $original), $vars);
    }
    
    private function processVars($val, $vars) {
        if ($vars !== null && $val !== null && sizeof($vars) > 0) {
            return self::vars($val, $vars);
        }
        return $val;
    }
    
    private static function vars(string $original, array $vars) : string {
        $chars = preg_split('/(?<!^)(?!$)/u', $original);
        $len = sizeof($chars);
        $buffer = array_fill(0, $len, '');
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
                    $closing = strpos($original, '}', $i);
                    if ($closing === false) {
                        // Bad syntax? Let's just use the string literally.
                        $buffer[$out_pos] = $chars[$i];
                        ++$out_chars;
                        ++$out_pos;
                        ++$i;
                    }
                    else {
                        $var = substr($original, $i + 1, $closing - $i - 1);
                        $buffer[$out_pos] = array_key_exists($var, $vars) ? $vars[$var] : '{' . $var . '}';
                        $out_chars += strlen($buffer[$out_pos]);
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
        return substr(implode($buffer), 0, $out_chars);
    }
}