<?php

namespace Ui;

use Gettext\Translator;

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

    public function getDetails(): string {
        return $this->details;
    }

    public static function success(string $message, string $details) : Message {
        return new Message(self::$TYPE_SUCCESS, $message, $details);
    }
    
    public static function successI18n(string $message, string $details, Translator $translator) : Message {
        return self::success($translator->gettext($message), $translator->gettext($details));
    }
    
    public static function info(string $message, string $details) : Message {
        return new Message(self::$TYPE_INFO, $message, $details);
    }
    
    public static function infoI18n(string $message, string $details, Translator $translator) : Message {
        return self::info($translator->gettext($message), $translator->gettext($details));
    }
    
    public static function warning(string $message, string $details) : Message {
        return new Message(self::$TYPE_WARNING, $message, $details);
    }
    
    public static function warningI18n(string $message, string $details, Translator $translator) : Message {
        return self::warning($translator->gettext($message), $translator->gettext($details));
    }

    public static function danger(string $message, string $details) : Message {
        return new Message(self::$TYPE_DANGER, $message, $details);
    }
    
    public static function dangerI18n(string $message, string $details, Translator $translator) : Message {
        return self::danger($translator->gettext($message), $translator->gettext($details));
    }
}