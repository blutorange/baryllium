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

use Moose\Entity\User;
use Moose\Util\Comparable;
use Moose\Util\PlaceholderTranslator;

/**
 * A logical section of the portal, such as the board, thread list or the user
 * profile. For example, this is used for rendering the toolbar and
 * breadcrumbs for navigation.
 *
 * @author madgaksha
 */
interface SectionInterface extends Comparable {

    public function __toString();
    
    /**
     * @return SectionInterface Parent section, or null when there is none.
     */
    public function getParent();

    /** @return SectionInterface[] */
    public function & getChildren() : array;
    
    /**
     * @param PlaceholderTranslator $translator Translator for localization.
     * @return string The I18N key of the name when $translator is null, or the localized named otherwise.
     */
    public function getName(PlaceholderTranslator $translator = null);
    
    public function getId();
    
    public function getNavPath();
    
    public function isParentOfOrSame(SectionInterface $child = null) : bool;
    
    public function isChildOfOrSame(SectionInterface $parent = null) : bool;

    public function compareTo($other): int;

    public function equals($other): bool;

    /**
     * @return SectionInterface[] An array with this section and all its parents, with the topmost parent last and this section first.
     */
    public function getAllFromChildToParent() : array;
    
    public function isAvailableToUser(User $user) : bool;

    /**
     * @return SectionInterface[] An array with this section and all its parents, with the topmost parent first and this section last.
     */
    public function getAllFromParentToChild() : array;
}