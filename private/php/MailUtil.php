<?php

/**
 * Utility functions for working with mails.
 *
 * @author madgaksha
 */
class MailUtil {  
    private function __construct() {}
    public static function sendMail(string $to, string $subject, string $content, string $from) : int {
        $headers = "From: $from\r\n";
        return mail($to,$subject,$content, $headers);
    }
}