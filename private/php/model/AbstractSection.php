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

/**
 * @author madgaksha
 */
abstract class AbstractSection implements SectionInterface {
    /** @var SectionInterface Parent section, nullable. */
    private $parent;

    /** @var string A link for navigating to this section. May be null when section cannot be accessed directly. */
    private $navPath;
    
    /** @var string A unique ID for this sections, also used for HTML elements. */
    private $id;
    
    /** @var int */
    private $allowedUserTypes;
    
    /** AbstractSection[] */
    private $children;
    
    /** @var int Flag for the site admin. */
    const USER_RESTRICTION_SADMIN = 1;
    /** @var int Flag for a signed in user. */
    const USER_RESTRICTION_USER = 2;
    /** @var int Flag for an anonymous user. */
    const USER_RESTRICTION_ANONYMOUS = 4;
    /** @var int Flag for a student with Campus Dual credentials. */
    const USER_RESTRICTION_CAMPUS_DUAL_CREDENTIALS = 8;
    /** @var int Flag for a student with Campus Dual credentials. */
    const USER_RESTRICTION_WITH_TUTORIAL_GROUP = 16;
    /** @var int When any child is visible. Used by dropdown menu entries
     * containing other entries*/
    const USER_RESTRICTION_ANY_CHILD = 32;
    /** @var int A temporary system administrator, mostly for changing database
     * settings without a working database connection.*/
    const USER_RESTRICTION_SADMIN_TEMPORARY = 64;

    protected function __construct(string $id, AbstractSection $parent = null, string $navPath = null, int $allowedUserTypes = null) {
        $this->children = [];
        $this->id = $id;
        $this->navPath = $navPath;
        $this->parent = $parent;
        if ($parent !== null) {
            $this->parent->children []= $this;
        }
        $this->allowedUserTypes = $allowedUserTypes ?? 0;
    }
    
    public function __toString() {
        $p = $this->parent ?? 'none';
        $name = $this->getName();
        return "Section($this->id, $name)<<$p";
    }
    
    public final function getParent() {
        return $this->parent;
    }
   
    /** @return AbstractSection[] */
    public function & getChildren() : array {
        return $this->children;
    }
    
    public final function getId() {
        return $this->id;
    }
    
    public final function getNavPath() : string {
        return $this->navPath ?? '';
    }
    
    public final function isParentOfOrSame(SectionInterface $child = null) : bool {
        if ($child === null) {
            return false;
        }
        return $child->isChildOf($this);
    }
    
    public final function isChildOfOrSame(SectionInterface $parent = null) : bool {
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

    public final function compareTo($other): int {
        return $this->equals($other) ? 0 : ($this->id < $other->id ? -1 : 1);
    }

    public final function equals($other): bool {
        if ($other === null) {
            return null;
        }
        return $this->id === $other->id;
    }

    public final function getAllFromChildToParent() : array {
        $array = [];
        $s = $this;
        do {
            $array[] = $s;
        } while (($s = $s->getParent()) !== null);
        return $array;
    }

    public final function getAllFromParentToChild() : array {
        return \array_reverse($this->getAllFromChildToParent(), false);
    }
    
    public function isAvailableToUser(User $user) : bool {
        return
               (($this->allowedUserTypes & self::USER_RESTRICTION_USER)                    !== 0 && $user->isValid() && !$user->getIsSiteAdmin())
            || (($this->allowedUserTypes & self::USER_RESTRICTION_WITH_TUTORIAL_GROUP)     !== 0 && $user->getTutorialGroup() !== null)
            || (($this->allowedUserTypes & self::USER_RESTRICTION_SADMIN)                  !== 0 && $user->getIsSiteAdmin() && !$user->isTemporarySadmin())
            || (($this->allowedUserTypes & self::USER_RESTRICTION_SADMIN_TEMPORARY)        !== 0 && $user->isTemporarySadmin())
            || (($this->allowedUserTypes & self::USER_RESTRICTION_ANONYMOUS)               !== 0 && $user->isAnonymous())
            || (($this->allowedUserTypes & self::USER_RESTRICTION_CAMPUS_DUAL_CREDENTIALS) !== 0 && $user->hasCampusDualCredentials())
            || (($this->allowedUserTypes & self::USER_RESTRICTION_ANY_CHILD)               !== 0 && $this->isAvailableToAnyChildren($user))
        ;
    }
    
    private function isAvailableToAnyChildren(User $user) : bool {
        foreach ($this->getChildren() as $section) {
            if ($section->isAvailableToUser($user)) {
                return true;
            }
        };
        return false;
    }
}