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

namespace Moose\Context;

use Exception;
use Moose\Context\Context;
use Moose\Dao\AbstractDao;
use Moose\Entity\AbstractEntity;
use Moose\Entity\User;
use Gettext\Translations;
use Moose\Context\TranslatorProviderInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Moose\Util\PlaceholderTranslator;
use Throwable;

/**
 * Instance of a session for the current user. Mostly immutable.
 * @todo Session timeout when users are inactive for long.
 * @author madgaksha
 */
class PortalSessionHandler implements TranslatorProviderInterface {
    
    const LANGUAGES = [
        'de' => true,
        'en' => true,
        'cs' => false
    ];
    
    /** @var User */
    private $user = null;

    /**
     * @var string
     */
    private $cachedLang;
    /**
     * @var PlaceholderTranslator
     */
    private $cachedTranslator;

    private static $SESSION_TIMEOUT = 1800;

    public function __construct() {
    }

    public function initSession() {
        try {
            \session_start();
        } catch (Throwable $e) {
            \error_log('Failed to start session: ' . $e);
        }
    }

    private function getUserId() {
        if (!\array_key_exists('uid', $_SESSION)) {
            return null;
        }
        $userId = $_SESSION['uid'];
        return $userId;
    }

    public function killSession() {
        $this->user = null;
        try {
            if (\ini_get("session.use_cookies")) {
                $params = \session_get_cookie_params();
                \setcookie(\session_name(), '', time() - 424242,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }
        } catch (Throwable $e) {
            \error_log('Failed to kill session: ' . $e);
        }
        try {
            if (session_status() === PHP_SESSION_ACTIVE) {
                \session_destroy();
            }
        } catch (Exception $e) {
            \error_log('Failed to destroy session: ' . $e);
        }
    }

    public function closeSession() {
        try {
            \session_write_close();
        } catch (Throwable $e) {
            \error_log('Failed to close session: ' . $e);
        }
    }

    /** @return User The user from the current session. */
    public function getUser(): User
    {
        $userId = $this->getUserId();
        if ($userId == null || $userId === AbstractEntity::INVALID_ID) {
            return User::getAnonymousUser();
        }
        if ($this->user !== null) {
            if ($this->user->getId() === $userId) {
                return $this->user;
            }
            $this->user = null;
        }
        $user = $this->fetchUserFromDatabase($userId);
        $this->user = $user;
        $_SESSION['uid'] = $user->getId();
        return $user;
    }

    /**
     * @param string $userId
     * @return User
     */
    private function fetchUserFromDatabase(string $userId): User {
        try {
            $user = AbstractDao::user(Context::getInstance()->getEm())->findOneById($userId);
            if ($user === null) {
                return User::AnonymousUser();
            }
            return $user;
        } catch (Throwable $e) {
            \error_log("Failed to fetch user $userId from database: " . $e);
            return User::getAnonymousUser();
        }
    }

    public function exitSession() {
        try {
            if (session_status() == PHP_SESSION_ACTIVE) {
                \session_destroy();
            }
        } catch (Throwable $e) {
            \error_log("Failed to destroy session: " . $e);
        }
    }

    public function ensureOpenSession() {
        try {
            \session_start();
        } catch (Throwable $e) {
            \error_log('Failed to start session: ' . $e);
        }
    }

    public function newSession($user, $lang = null) {
        $this->exitSession();
        $this->initSession();
        $this->setLang($lang ?? $this->getLang());
        $_SESSION["uid"] = $user->getId();
    }

    /**
     * @param type $lang
     * @return string The language actually set.
     */
    public function setLang($lang) : string {
        $lang = $lang ?? 'de';
        if (!(self::LANGUAGES[$lang]??false)) {
            $lang = 'de';
        }
        \setlocale(LC_ALL, $lang);
        \putenv("LANG=$lang");
        $_SESSION['lang'] = $lang ?? "de";
        return $lang;
    }

    public function getLang(): string {
        $lang = \array_key_exists('lang', $_REQUEST) ? $_REQUEST['lang'] : '';
        if (empty($lang) && isset($_SESSION)) {
            $lang = \array_key_exists('lang', $_SESSION) ? $_SESSION["lang"] : '';
        }
        if (empty($lang)) {
            $lang = 'de';
        }
        return $this->setLang($lang);
    }

    public function getTranslatorFor(string $lang) {
        if ($this->cachedTranslator === NULL || empty($this->cachedLang) || $this->cachedLang !== $lang) {
            $translations = Context::getInstance()->getCache()->fetch("moose.locale.$lang");
            if ($translations === false) {
                $path = "resource/locale/$lang/LC_MESSAGES/i18n.po";
                $file = Context::getInstance()->getFilePath($path);
                try {
                    if (($fileContent = \file_get_contents($file)) === false) {
                        throw new IOException("Cannot read file $file.");
                    }
                } catch (Throwable $e) {
                    $lang = 'de';
                    $this->setLang($lang);
                    \error_log("Failed to load translation file $file. Falling back to de.");
                    $fileContent = \file_get_contents(Context::getInstance()->getFilePath("resource/locale/de/LC_MESSAGES/i18n.po"));
                }
                if ($fileContent !== false) {
                    $translations = Translations::fromPoString($fileContent);
                }
                else {
                    \error_log("Failed to read translation file $file. Falling back to empty file.");
                    $translations = new Translations();
                }
                Context::getInstance()->getCache()->save("moose.locale.$lang", $translations);
            }
            $this->cachedLang = $lang;
            $this->cachedTranslator = (new PlaceholderTranslator($lang));
            $this->cachedTranslator->loadTranslations($translations);
        }
        return $this->cachedTranslator;
    }

    public function getTranslator(): PlaceholderTranslator {
        $lang = $this->getLang();
        return $this->getTranslatorFor($lang);
    }
    
    public function getPagingCount() : int {
        return 10;
    }
}