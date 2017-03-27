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

namespace Ui;

use Ui\Message;

/**
 * A message, to be used in displaying messages with bootstrap.
 *
 * @author madgaksha
 */
class Message {

    public static $TYPE_SUCCESS = 0;
    public static $TYPE_INFO = 1;
    public static $TYPE_WARNING = 2;
    public static $TYPE_DANGER = 3;
    
    private static $TYPE_NAME_MAP;

    /** @var integer */
    private $type;

    /** @var string */
    private $message;

    /** @var string */
    private $details;

    private function __construct(int $type, string $message, string $details) {
        $this->type = $type;
        $this->message = $message ?? '';
        $this->details = $details ?? '';
    }

    public function isSuccess(): bool {
        return $this->type === self::$TYPE_SUCCESS;
    }

    public function isInfo(): bool {
        return $this->type === self::$TYPE_INFO;
    }

    public function isWarning(): bool {
        return $this->type === self::$TYPE_WARNING;
    }

    public function isDanger(): bool {
        return $this->type === self::$TYPE_DANGER;
    }

    public function getMessage(): string {
        return $this->message;
    }
    
    public function getSeverity() : int {
        return $this->type;
    }

    public function getSeverityName() : string {
        return self::getTypeNameMap()[$this->type];
    }

    public function getDetails(): string {
        return $this->details;
    }

    public static function success(string $message, string $details) : Message {
        return new Message(self::$TYPE_SUCCESS, $message, $details);
    }
    
    public static function successI18n(string $message, string $details, PlaceholderTranslator $translator, array $vars = null) : Message {
        return self::success($translator->gettextVar($message, $vars), $translator->gettextVar($details, $vars));
    }
    
    public static function info(string $message, string $details) : Message {
        return new Message(self::$TYPE_INFO, $message, $details);
    }
    
    public static function infoI18n(string $message, string $details, PlaceholderTranslator $translator, array $vars = null) : Message {
        return self::info($translator->gettextVar($message, $vars), $translator->gettextVar($details, $vars));
    }
    
    public static function warning(string $message, string $details) : Message {
        return new Message(self::$TYPE_WARNING, $message, $details);
    }
    
    public static function warningI18n(string $message, string $details, PlaceholderTranslator $translator, array $vars = null) : Message {
        return self::warning($translator->gettextVar($message, $vars), $translator->gettextVar($details, $vars));
    }

    public static function danger(string $message, string $details) : Message {
        return new Message(self::$TYPE_DANGER, $message, $details);
    }
    
    public static function dangerI18n(string $message, string $details, PlaceholderTranslator $translator, array $vars = null) : Message {
        return self::danger($translator->gettextVar($message, $vars), $translator->gettextVar($details, $vars));
    }
    
    private static function getTypeNameMap() {
        if (self::$TYPE_NAME_MAP !== null) {
            self::$TYPE_NAME_MAP = [
                self::$TYPE_SUCCESS = 'success',
                self::$TYPE_INFO = 'info',
                self::$TYPE_WARNING = 'warning',
                self::$TYPE_DANGER = 'danger' 
            ];
        }
        return self::$TYPE_NAME_MAP;
    }
}