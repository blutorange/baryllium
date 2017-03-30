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

namespace Ui;

use Moose\Util\Comparable;
use Util\CmnCnst;

/**
 * A message, to be used in displaying messages with bootstrap.
 *
 * @author madgaksha
 */
class Section implements Comparable {

    public static $TYPE_SUCCESS = 0;
    public static $TYPE_INFO = 1;
    public static $TYPE_WARNING = 2;
    public static $TYPE_DANGER = 3;
    
    /** @var Section Parent section, nullable. */
    private $parent;

    /** @var string */
    private $nameI18n;

    /** @var string */
    private $navPath;
    
    /** @var string */
    private $id;
    
    private static $SECTIONS;

    private function __construct(string $id, Section $parent = null, string $navPath = null, string $nameI18n = null) {
       $this->id = $id;
       $this->navPath = $navPath;
       $this->nameI18n = $nameI18n ?? $id;
       $this->parent = $parent;
    }
    
    public function __toString() {
        $p = $this->parent ?? 'none';
        return "Section($this->id, $this->nameI18n)<<$p";
    }
    
    /**
     * @return Section Or null.
     */
    public function getParent() {
        return $this->parent;
    }

    /**
     * @param PlaceholderTranslator $translator Translator for localization.
     * @return type The I18N key of the name when $translator is null, or the localized named otherwise.
     */
    public function getName(PlaceholderTranslator $translator = null) {
        if ($translator === null) {
            return $this->nameI18n;
        }
        return $translator->gettext($this->nameI18n);
    }
    
    public function getNameI18n() : string {
        return $this->nameI18n;
    }

    public function getId() {
        return $this->id;
    }
    
    public function getNavPath() : string {
        return $this->navPath ?? '';
    }
    
    public function isParentOfOrSame(Section $child = null) : bool {
        if ($child === null) {
            return false;
        }
        return $child->isChildOf($this);
    }

    
    public function isChildOfOrSame(Section $parent = null) : bool {
        if ($parent === null) {
            return false;
        }
        $super = $this;
        do {
            if ($parent->equals($super)) {
                return true;
            }
        } while(($super = $super->getParent()) !== null);
        return false;
    }
    
    public static $NONE;
    public static $DASHBOARD;
    public static $FORUM;
    public static $THREAD;
    public static $POST;
    public static $LOGIN;
    public static $REGISTER;
    public static $PROFILE;

    public static function __static() {
        Section::$NONE = new self('sec-none');
        Section::$DASHBOARD = new Section('sec-dashboard', null, CmnCnst::PATH_DASHBOARD);
        Section::$FORUM = new Section('sec-forum', null, CmnCnst::PATH_FORUM);
        Section::$LOGIN = new Section('sec-login', null, CmnCnst::PATH_LOGIN_PAGE);
        Section::$PROFILE = new Section('sec-profile', null, CmnCnst::PATH_PROFILE);
        Section::$REGISTER = new Section('sec-register', null, CmnCnst::PATH_REGISTER);

        Section::$THREAD = new Section('sec-thread', Section::$FORUM);
        Section::$POST = new Section('sec-post', Section::$THREAD);        
    }

    public function compareTo($other): int {
        return $this->equals($other) ? 0 : ($this->id < $other->id ? -1 : 1);
    }

    public function equals($other): bool {
        if ($other === null) {
            return null;
        }
        return $this->id === $other->id;
    }

}

Section::__static();