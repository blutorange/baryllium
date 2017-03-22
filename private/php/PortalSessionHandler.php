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

use Dao\AbstractDao;
use Entity\User;
use Gettext\Translations;
use Symfony\Component\Filesystem\Exception\IOException;
use Ui\PlaceholderTranslator;

/**
 * Instance of a session for the current user. Mostly immutable.
 * @todo Session timeout when users are inactive for long.
 * @author madgaksha
 */
class PortalSessionHandler {  
    private $user = null;
    /**
     * @var Context
     */
    private $context;
    private $cachedLang;
    /**
     * @var PlaceholderTranslator
     */
    private $cachedTranslator;
    
    private static $SESSION_TIMEOUT = 1800;
    
    public function __construct(Context $context = null) {
        $this->context = $context ?? $GLOBALS['context'];
    }
    
    public function initSession() {
        switch (session_status()) {
        case PHP_SESSION_NONE:
            try {
                session_start();
            }
            catch (Throwable $e) {
                error_log('Failed to start session: ' . $e);
            }
            break;
        case PHP_SESSION_ACTIVE:
        case PHP_SESSION_DISABLED:
            break;
        }
    }
   
    public function checkUser(){
        $userId = $_SESSION['uid'];
        if ($this->user !== null && ((string)$this->user->getId()) !== $userId) {
            $this->user = null;
            $this->ensureSessionClosed();
            return null;
        }
        return $userId;
    }
    
    /**
     * @return User
     */
    public function getUser() : User {
        $userId = $this->checkUser();
        if ($this->user !== null) {
            return $this->user;
        }
        if ($userId == null) {
            return User::getAnonymousUser();
        }
        try {
            $user = AbstractDao::user($this->context->getEm())->findOneById($userId);
            if ($user === null) {
                return User::AnonymousUser();
            }
            $_SESSION['uid'] = $user->getId();
            session_commit();
            $this->user = $user;
            return $user;
        }
        catch (Throwable $e) {
            error_log($e->getMessage());
            error_log($e->getTraceAsString());
            return User::getAnonymousUser();
        }
    }
    
    public function ensureSessionClosed() {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_abort();
            session_destroy(); 
        }
    }

    public function ensureOpenSession() {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            try {
                session_start();
                session_register('uid');
                session_register('lang');
            }
            catch (Throwable $e) {
                error_log('Failed to start session: ' . $e);   
            }
        }
    }
    
    public function newSession($user, $lang) {
        $this->ensureSessionClosed();
        $this->ensureOpenSession();
        $this->setLang($lang);
        $_SESSION["uid"] = $user->getId();
    }
    
    public function setLang($lang) {
        $lang = $lang ?? "de";
        setlocale(LC_ALL, $lang);
        putenv("LANG=$lang"); 
        $this->ensureOpenSession();
        $_SESSION['lang'] = $lang ?? "de";
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
