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

namespace Moose\ViewModel;

use Moose\Util\CmnCnst;
use Moose\Util\PlaceholderTranslator;

/**
 * A basic section with a fixed name retrieved from an I18N string and requiring
 * no additional URL parameters etc.
 *
 * @author madgaksha
 */
class SectionBasic extends AbstractSection {
    /** @var SectionInterface */
    public static $NONE;
    /** @var SectionInterface */
    public static $DASHBOARD;
    /** @var SectionInterface */
    public static $BOARD;
    /** @var SectionInterface */
    public static $LOGIN;
    /** @var SectionInterface */
    public static $REGISTER;
    /** @var SectionInterface */
    public static $PROFILE;

    /** @var string */
    private $nameI18n;

    protected function __construct(string $id, SectionInterface $parent = null, string $navPath = null, string $nameI18n = null) {
        parent::__construct($id, $parent, $navPath);
       $this->nameI18n = $nameI18n ?? $id;
    }
    
    /**
     * @param PlaceholderTranslator $translator Translator for localization.
     * @return string The I18N key of the name when $translator is null, or the localized named otherwise.
     */
    public function getName(PlaceholderTranslator $translator = null) {
        if ($translator === null) {
            return $this->nameI18n;
        }
        return $translator->gettext($this->nameI18n);
    }

    public static function __static() {
        SectionBasic::$NONE = new self('sec-none');
        SectionBasic::$DASHBOARD = new SectionBasic('sec-dashboard', null, CmnCnst::PATH_DASHBOARD);
        SectionBasic::$BOARD = new SectionBasic('sec-board', null, CmnCnst::PATH_BOARD);
        SectionBasic::$LOGIN = new SectionBasic('sec-login', null, CmnCnst::PATH_LOGIN_PAGE);
        SectionBasic::$PROFILE = new SectionBasic('sec-profile', null, CmnCnst::PATH_PROFILE);
        SectionBasic::$REGISTER = new SectionBasic('sec-register', null, CmnCnst::PATH_REGISTER);
    }
}

SectionBasic::__static();