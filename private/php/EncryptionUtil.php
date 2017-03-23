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

use Defuse\Crypto\Crypto;
use Doctrine\Common\Proxy\Exception\InvalidArgumentException;
use Doctrine\DBAL\Types\ProtectedString;

/**
 * Utility functions for working with encryptions. Mainly encapsulates existing
 * PHP functions and libraries.
 *
 * @author madgaksha
 */
class EncryptionUtil {  
    public  static function hashPwd(ProtectedString $pwdToHash) : string {
        return password_hash($pwdToHash->getString(), PASSWORD_BCRYPT);
    }
    
    public static function verifyPwd(ProtectedString $password, string $hash) : bool {
        return password_verify($password->getString(), $hash);
    }
    
    public static function isWeakPwd(ProtectedString $password) : bool {
        return $password->isEmpty() || mb_strlen($password->getString()) < 5;
    }

    public static function decryptFromDatabase(string $base64) : string {
        // In case the encrypt method throws an error, the password or the
        // the cypher data may be leaked via the PHP stack trace. Thus, we set 
        // the data to the empty string. This only shows an empty string in the
        // stack trace.
        $protectedBase64 = $base64;
        $base64 = '';
        try {
            $raw = base64_decode($protectedBase64, true);
            return Crypto::decrypt($raw, self::getSecretKey(), true);
        }
        catch (Throwable $e) {
            $class = get_class($e);
            throw new $class($e->getMessage());            
        }
    }

    public static function encryptForDatabase(string $data) : string {
        // In case the encrypt method throws an error, the password or the
        // the data may be leaked via the PHP stack trace. Thus, we set the
        // data to the empty string. This only shows an empty string in the
        // stack trace.
        $protectedData = $data;
        $data = '';
        try {
            $raw = Crypto::encrypt($protectedData, self::getSecretKey(), true);
            return base64_encode($raw);
        }
        catch (Throwable $e) {
            $class = get_class($e);
            throw new $class($e->getMessage());
        }
        return base64_encode($raw);
    }
    
    private static function getSecretKey() {
        $context = $GLOBALS["context"];
        if ($context === null) {
            throw new InvalidArgumentException("Cannot find context.");
        }
        $privateKey = $context->getPrivateKey();
        if ($privateKey === null) {
            throw new InvalidArgumentException("Cannot find secret key.");
        }
        return $privateKey;
    }
}