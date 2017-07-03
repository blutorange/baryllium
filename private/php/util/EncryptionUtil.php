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

namespace Moose\Util;

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;
use Doctrine\Common\Proxy\Exception\InvalidArgumentException;
use Doctrine\DBAL\Types\ProtectedString;
use Moose\Context\Context;
use ParagonIE\PasswordLock\PasswordLock;
use Throwable;
use function mb_strlen;

/**
 * Utility functions for working with encryptions. Mainly encapsulates existing
 * PHP functions and libraries.
 *
 * @author madgaksha
 */
class EncryptionUtil {  
    public  static function hashPwd(ProtectedString $pwdToHash, Key $pk = null) : string {
        try {
            return PasswordLock::hashAndEncrypt($pwdToHash->getString() ?? '', $pk ?? self::getPrivateKey());
        } catch (Throwable $e) {
            $pwdToHash = null;
            $pk = null;
            $class = \get_class($e);
            throw new $class($e->getMessage());
        }
    }
    
    public static function verifyPwd(ProtectedString $password, string $hash, Key $pk = null) : bool {
        if (ProtectedString::isEmpty($password)) {
            return false;
        }
        try {
            return PasswordLock::decryptAndVerify($password->getString(), $hash, $pk ?? self::getPrivateKey());
        }
        catch (Throwable $e) {
            $password = null;
            $hash = null;
            $pk = null;
            DebugUtil::log('Password verification failed: ' . $e->getMessage());
            return false;
        }
    }
    
    public static function isWeakPwd(ProtectedString $password) : bool {
        return ProtectedString::isEmpty($password) || \mb_strlen($password->getString()) < 5;
    }

    public static function decryptArray(array & $array, Key $pk = null) {
        try {
            self::_decryptArray($array, $pk ?? self::getPrivateKey());
        }
        catch (\Throwable $e) {
            $array = null;
            $pk = null;
            $class = \get_class($e);
            throw new $class($e->getMessage());
        }
    }
    
    public static function encryptArray(array & $array, Key $pk) {
        try {
            self::_encryptArray($array, $pk ?? self::getPrivateKey());
        }
        catch (\Throwable $e) {
            $array = null;
            $pk = null;
            $class = \get_class($e);
            throw new $class($e->getMessage());
        }
    }

   
    public static function decryptFromDatabase(string $base64, Key $pk = null) : string {
        // In case the encrypt method throws an error, the password or the
        // the cypher data may be leaked via the PHP stack trace. Thus, we set 
        // the data to the empty string. This only shows an empty string in the
        // stack trace.
        $protectedBase64 = $base64;
        $base64 = '';
        try {
            $raw = \base64_decode($protectedBase64, true);
            return Crypto::decrypt($raw, $pk ?? self::getPrivateKey(), true);
        }
        catch (\Throwable $e) {
            $class = \get_class($e);
            throw new $class($e->getMessage());            
        }
    }
    
    private static function _encryptArray(array & $array, Key $pk) {
        foreach ($array as $key => & $value) {
            if (\is_array($value)) {
                self::_encryptArray($value, $pk);
            }
            else {
                $raw = Crypto::encrypt((string)$value, $pk, true);
                $array[$key] = \base64_encode($raw);
            }
        }
    }

    private static function _decryptArray(array & $array, Key $pk) {
        foreach ($array as $key => & $value) {
            if (\is_string($value)) {
                $raw = \base64_decode($value);
                $array[$key] = Crypto::decrypt($raw, $pk, true);
            }
            else if (\is_array($value)) {
                self::_decryptArray($value, $pk);
            }
        }
    }

    public static function encryptForDatabase(string $data, Key $pk = null) : string {
        // In case the encrypt method throws an error, the password or the
        // the data may be leaked via the PHP stack trace. Thus, we set the
        // data to the empty string. This only shows an empty string in the
        // stack trace.
        $protectedData = $data;
        $data = '';
        try {
            $raw = Crypto::encrypt($protectedData, $pk ?? self::getPrivateKey(), true);
            return base64_encode($raw);
        }
        catch (Throwable $e) {
            $class = get_class($e);
            throw new $class($e->getMessage());
        }
    }
    
    private static function getPrivateKey() {
        $privateKey = Context::getInstance()->getConfiguration()->getPrivateKey();
        if ($privateKey === null) {
            throw new InvalidArgumentException("Cannot find secret key.");
        }
        return $privateKey;
    }
}