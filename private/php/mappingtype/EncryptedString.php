<?php

namespace Doctrine\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use EncryptionUtil;
use InvalidArgumentException;

/**
 * Same as a string, but with encryption.
 *
 * @author madgaksha
 */
class EncryptedStringType extends TextType {

    const TPYE_NAME = "crypt_string";
    
    public function getName(): string {
        return self::TPYE_NAME;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform) {
        if (!($value instanceof ProtectedString)) {
            throw new InvalidArgumentException("Must be a protected string.");
        }
        $value = parent::convertToDatabaseValue($value->getString(), $platform);
        return EncryptionUtil::encryptForDatabase($value);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform) {
        $value = parent::convertToPHPValue($value, $platform);
        new ProtectedString(EncryptionUtil::decryptFromDatabase($value));
    }
}

class ProtectedString {
    private $string;
    public function __construct(string $string) {
        $this->string = $string;
    }
    public function getString() {
        return $this->string;
    }
    public function __toString() {
        return self::class;
    }
}
