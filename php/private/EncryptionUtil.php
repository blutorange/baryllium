<?php

/**
 * Utility functions for working with encryptions. Mainly encapsulates existing
 * PHP functions, but fixing algorithms etc.
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
}