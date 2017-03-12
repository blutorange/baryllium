<?php

/**
 * Instance of a session for the current user. Mostly immutable.
 * @todo Session timeout when users are inactive for long.
 * @author madgaksha
 */
class PortalSessionHandler extends SessionHandler {  
    private $user = null;
    private $context;
    
    private static $SESSION_TIMEOUT = 1800;
    
    public function __construct() {
        $this->context = $GLOBALS['context'];        
        switch (session_status()) {
        case PHP_SESSION_ACTIVE:
        case PHP_SESSION_NONE:
            session_start();
            break;
        case PHP_SESSION_DISABLED:
        default:
            $this->user = \Entity\User::getAnon();
            break;
        }
    }
    
    public function open($save_path, $name): bool {
        $res = parent::open($save_path, $name);
        return $res;
    }
    
    public function create_sid() : string {
        $res = parent::create_sid();
        return $res;
    }

    public function destroy ($session_id) : bool {
        $res = parent::destroy($session_id);
        $this->user = null;
        return $res;
    }
    
    public function getUser() : \Entity\User {
        if ($this->user !== null) {
            return $this->user;
        }
        $userId = $_SESSION['uid'];
        if ($userId == null) {
            return \Entity\User::getAnon();
        }
        try {
            $user = $this->context->getEm()->find('Entity\User', $userId);
            return $user ?? \Entity\User::getAnon();
        }
        catch (Exception $e) {
            error_log($e->getMessage());
            error_log($e->getTraceAsString());
            return \Entity\User::getAnon();
        }
    }
    
    public static function newSession($user, $lang) {
        session_abort();
        session_destroy();
        session_start();
        $this->setLang($lang);
        $_SESSION["uid"] = $user->getId();
    }
    
    public function setLang($lang) {
        setlocale(LC_ALL, $lang ?? "de");
        session_start();
        $_SESSION['lang'] = $lang ?? "de";
        session_commit();
    }

    public function getLang() : string {
        $lang = $_SESSION["lang"];
        if (empty($lang)) {
            $lang = $_REQUEST["locale"];
            $lang = empty($lang) ? 'de' : $lang;
            $this->setLang($req);
        }        
        return $lang;
    }
}