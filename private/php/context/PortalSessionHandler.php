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
use Gettext\Translations;
use Moose\Context\Context;
use Moose\Context\TranslatorProviderInterface;
use Moose\Dao\Dao;
use Moose\Entity\AbstractEntity;
use Moose\Entity\TutorialGroup;
use Moose\Entity\User;
use Moose\Util\CmnCnst;
use Moose\Util\PlaceholderTranslator;
use Moose\Web\HttpRequestInterface;
use Symfony\Component\Filesystem\Exception\IOException;
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

    public function __construct() {
        if (!isset($_SESSION)) {
            $_SESSION = [];
        }
    }

    public function initSession() {
        try {
            \session_start();
        } catch (Throwable $e) {
            Context::getInstance()->getLogger()->error($e, 'Failed to start session');
        }
    }

    private function getUserId() {
        if (!\array_key_exists(CmnCnst::SESSION_USER_ID, $_SESSION)) {
            return null;
        }
        return $_SESSION[CmnCnst::SESSION_USER_ID];
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
            Context::getInstance()->getLogger()->error($e, 'Failed to kill session: ');
        }
        try {
            if (session_status() === PHP_SESSION_ACTIVE) {
                \session_destroy();
            }
        } catch (Exception $e) {
            Context::getInstance()->getLogger()->error($e, 'Failed to destroy session: ');
        }
    }

    public function closeSession() {
        try {
            \session_write_close();
        } catch (Throwable $e) {
            Context::getInstance()->getLogger()->error($e, 'Failed to close session');
        }
    }

    /** @return User The user from the current session. */
    public function getSessionUser(): User {
        $user = $this->user;
        if ($user === null) {
            if (session_status() === \PHP_SESSION_NONE) {
                $this->initSession();
            }
            $userId = $this->getUserId();
            if ($userId === null || $userId === AbstractEntity::INVALID_ID) {
                $user = User::getAnonymousUser();
            }
            else {
                $user = $this->fetchUserFromDatabase($userId);
                if ($_SESSION[CmnCnst::SESSION_COOKIE_AUTHED] ?? false) {
                    Context::getInstance()->getLogger()->debug('Authorized cookie authed user from session');
                    $user->markCookieAuthed();
                }
                else {
                    Context::getInstance()->getLogger()->debug('Authorized session user');
                }
            }
            if ($user->isValid() && !$user->isAnonymous()) {
                $_SESSION[CmnCnst::SESSION_USER_ID] = $user->getId();
                $_SESSION[CmnCnst::SESSION_COOKIE_AUTHED] = $user->isCookieAuthed();
            }            
            $this->user = $user;
        }
        return $user;
    }

    /**
     * @param string $userId
     * @return User
     */
    private function fetchUserFromDatabase(string $userId): User {
        try {
            $user = Dao::user(Context::getInstance()->getEm())->findOneById($userId, [
                'tutorialGroup',
                TutorialGroup::class => 'university'
            ]);
            if ($user === null) {
                return User::AnonymousUser();
            }
            return $user;
        } catch (Throwable $e) {
            Context::getInstance()->getLogger()->error($e, "Failed to fetch user $userId from database");
            return User::getAnonymousUser();
        }
    }

    public function exitSession() {
        try {
            if (session_status() == PHP_SESSION_ACTIVE) {
                \session_destroy();
            }
        } catch (Throwable $e) {
            Context::getInstance()->getLogger()->error($e, 'Failed to destroy session');
        }
    }

    public function ensureOpenSession() {
        try {
            \session_start();
        } catch (Throwable $e) {
            Context::getInstance()->getLogger()->error($e, 'Failed to start session');
        }
    }

    public function newSession(User $user, $lang = null) {
        $this->exitSession();
        $this->initSession();
        $this->setLang($lang ?? $this->getLang());
        $_SESSION[CmnCnst::SESSION_USER_ID] = $user->getId();
        $_SESSION[CmnCnst::SESSION_COOKIE_AUTHED] = $user->isCookieAuthed();
    }
    
    public function store(string $key, string $data) {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            $this->ensureOpenSession();
        }
        $_SESSION[$key] = $data;
    }
    
    /**
     * @param string $key
     * @return string|null
     */
    public function fetch(string $key, string $default = null) {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return $default;
        }
        return $_SESSION[$key] ?? $default;
    }
    
    public function delete(string $key) {
        if (session_status() === PHP_SESSION_ACTIVE) {
            unset($_SESSION[$key]);
        }
    }

    /**
     * @param type $lang
     * @return string The language actually set.
     */
    public function setLang(string $lang = null) : string {
        $lang = $lang ?? 'de';
        if (!(self::LANGUAGES[$lang]??false)) {
            $lang = 'de';
        }
        \setlocale(LC_ALL, $lang);
        \putenv("LANG=$lang");
        $_SESSION[CmnCnst::SESSION_LANGUAGE] = $lang ?? "de";
        return $lang;
    }

    public function getLang(): string {
        $lang = Context::getInstance()->getRequest()->getParam(CmnCnst::URL_PARAM_LANGUAGE, '', HttpRequestInterface::PARAM_QUERY);
        if (empty($lang) && isset($_SESSION)) {
            $lang = $_SESSION[CmnCnst::SESSION_LANGUAGE] ?? '';
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
                    Context::getInstance()->getLogger()->error("Failed to load translation file $file. Falling back to de.");
                    $fileContent = \file_get_contents(Context::getInstance()->getFilePath("resource/locale/de/LC_MESSAGES/i18n.po"));
                }
                if ($fileContent !== false) {
                    $translations = Translations::fromPoString($fileContent);
                }
                else {
                    Context::getInstance()->getLogger()->error("Failed to read translation file $file. Falling back to empty file.");
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
