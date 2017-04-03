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

    protected function __construct(string $id, SectionInterface $parent = null, string $navPath = null) {
       $this->id = $id;
       $this->navPath = $navPath;
       $this->parent = $parent;
    }
    
    public function __toString() {
        $p = $this->parent ?? 'none';
        $name = $this->getName();
        return "Section($this->id, $name)<<$p";
    }

    public final function getParent() {
        return $this->parent;
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
}