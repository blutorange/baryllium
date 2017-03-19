<?php

use Entity\User;
use Gettext\Translations;
use Symfony\Component\Filesystem\Exception\IOException;
use Ui\PlaceholderTranslator;

/**
 * Instance of a session for the current user. Mostly immutable.
 * @todo Session timeout when users are inactive for long.
 * @author madgaksha
 */
class PortalSessionHandler extends SessionHandler {  
    private $user = null;
    private $context;
    private $cachedLang;
    private $cachedTranslator;
    
    private static $SESSION_TIMEOUT = 1800;
    
    public function __construct(Context $context = null) {
        $this->context = $context ?? $GLOBALS['context'];
    }
    
    public function initSession() {
        switch (session_status()) {
        case PHP_SESSION_ACTIVE:
        case PHP_SESSION_NONE:
            try {
                session_start();
            }
            catch (Throwable $e) {
                error_log('Failed to start session: ' . $e);
            }
            break;
        case PHP_SESSION_DISABLED:
        default:
            $this->user = User::getAnon();
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
    
    public function getUser() : User {
        if ($this->user !== null) {
            return $this->user;
        }
        $userId = $_SESSION['uid'];
        if ($userId == null) {
            return User::getAnon();
        }
        try {
            $user = $this->context->getEm()->find('Entity\User', $userId);
            return $user ?? User::getAnon();
        }
        catch (Exception $e) {
            error_log($e->getMessage());
            error_log($e->getTraceAsString());
            return User::getAnon();
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
        $lang = $lang ?? "de";
        setlocale(LC_ALL, $lang);
        putenv("LANG=$lang"); 
        if (session_status() !== PHP_SESSION_ACTIVE) {
            try {
                session_start();
            }
            catch (Throwable $e) {
                error_log('Failed to start session: ' . $e);   
            }
        }
        $_SESSION['lang'] = $lang ?? "de";
        session_commit();
    }

    public function getLang() : string {
        $lang = array_key_exists('lang', $_REQUEST) ? $_REQUEST['lang'] : '';
        if (empty($lang) && isset($_SESSION)) {
            $lang = array_key_exists('lang', $_SESSION) ? $_SESSION["lang"] : '';
        }        
        if (empty($lang)) {
            $lang = 'de';
        }
        $this->setLang($lang);
        return $lang;
    }
    
    public function getTranslator() : PlaceholderTranslator {
        $lang = $this->getLang();
        if ($this->cachedTranslator === NULL || empty($this->cachedLang) || $this->cachedLang !== $lang) {
            $file = $this->context->getFilePath("resource/locale/$lang/LC_MESSAGES/i18n.po");
            $fileContent;
            try {
                if (($fileContent = file_get_contents($file)) === false) {
                    throw new IOException("Cannot read file $file.");
                }
            } catch (Throwable $e) {
                $lang = 'de';
                $this->setLang($lang);
                error_log("Failed to load translation file $file. Falling back to de.");
                $fileContent = file_get_contents($this->context->getFilePath("resource/locale/de/LC_MESSAGES/i18n.po"));
            }
            $this->cachedLang = $lang;
            $translations = Translations::fromPoString($fileContent);
            $this->cachedTranslator = (new PlaceholderTranslator($lang))->loadTranslations($translations);
        }
        return $this->cachedTranslator;
    }
}
