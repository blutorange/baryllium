<?php

use Defuse\Crypto\Crypto;
use Doctrine\Common\Proxy\Exception\InvalidArgumentException;

/**
 * Utility functions for working with encryptions. Mainly encapsulates existing
 * PHP functions and libraries.
 *
 * @author madgaksha
 */
class EncryptionUtil {  
    public  static function hashPwd(string $pwdToHash) : string {
        return password_hash($pwdToHash, PASSWORD_BCRYPT);
    }
    
    public static function verifyPwd(string $password, string $hash) : bool {
        return password_verify($password, $hash);
    }
    
    public static function isWeakPwd($password) : bool {
        return empty($password) || strlen($password) < 5;
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