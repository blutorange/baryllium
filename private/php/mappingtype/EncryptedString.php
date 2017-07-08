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

namespace Doctrine\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Moose\Util\EncryptionUtil;
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
        if ($value == null) {
            return null;
        }
        if (!($value instanceof ProtectedString)) {
            throw new InvalidArgumentException("Must be a protected string.");
        }
        $value = parent::convertToDatabaseValue($value->getString(), $platform);
        if ($value === null) {
            return null;
        }
        return EncryptionUtil::encryptForDatabase($value);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform) {
        $value = parent::convertToPHPValue($value, $platform);
        if ($value === null) {
            return null;
        }
        return new ProtectedString(EncryptionUtil::decryptFromDatabase($value));
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
        return ProtectedString::class;
    }
    public function  __debugInfo() {
        return [$this->__toString()];
    }
    public function _isEmpty() {
        return empty($this->string);
    }
    public function __set($name, $value) {
        throw new \Exception("Cannot set value to protected string.");
    }
    public function __get($name) {
        throw new \Exception("Cannot get value for protected string.");
    }

    public static function isEmpty(ProtectedString $string = null) : bool {
        return $string === null || $string->_isEmpty();
    }
}