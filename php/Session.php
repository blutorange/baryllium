<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Session
 *
 * @author madgaksha
 */
class Session {
    private $isAnon = true;
    private $lang = 'de';
    public function __construct() {
        switch (session_status()) {
        case PHP_SESSION_ACTIVE:
            $this->restoreSession();
            break;
        case PHP_SESSION_NONE:
            if (session_start()) {
                $this->restoreSession();
                break;
            }
        case PHP_SESSION_DISABLED:
        default:
            $this->isAnon = true;
            break;
        }
    }
    public function getLang() : string {
        return $this->lang;
                
    }

    private function restoreSession() {
        $lang = $_SESSION["lang"];
        if ($lang != 'de' && lang != 'en')
            $lang = 'de';
        $this->lang = $lang;
    }
}
