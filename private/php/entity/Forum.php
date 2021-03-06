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

namespace Moose\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use Moose\Util\ReflectionCache;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Description of Forum
 * 
 * @Entity
 * @Table(name="forum")
 * @author CaptainMalzbier
 */
class Forum extends AbstractEntity {

    /**
     * @Column(name="title", type="string", length=255, unique=false, nullable=false)
     * @Assert\NotBlank(message="forum.title.empty")
     * @Assert\Length(max=255, maxMessage="forum.title.maxlength")
     * @var string Some arbitrary name of this forum.
     */
    protected $title;

    /**
     * List of forums this forum contains.
     * @OneToMany(targetEntity="Forum", mappedBy="parent")
     */
    protected $children;
    
    /**
     * The parent forum. May be null for the topmost forum.
     * @ManyToOne(targetEntity="Forum", inversedBy="children")
     * @JoinColumn(name="parent_id", referencedColumnName="id", nullable=true)
     */
    protected $parent;

    /**
     * One forum may contain one thread, many threads or none at all.
     * @OneToMany(targetEntity="Thread", mappedBy="forum", fetch="EXTRA_LAZY")
     * @Assert\NotNull
     * @var Collection
     */
    protected $threadList;

    public function __construct() {
        $this->children = new ArrayCollection();
        $this->threadList = new ArrayCollection();
    }

    public function getName() {
        return $this->title;
    }

    public function setName($name) : Forum {
        $this->title = $name;
        return $this;
    }

    public function getParentForum() {
        return $this->parent;
    }

    public function setParentForum(Forum $parentForum = null) : Forum {
        $this->parent = $parentForum;
        if ($parentForum !== null) {
            $parentForum->children->add($this);
        }
        return $this;
    }

    public function getSubForumList() {
        return $this->children;
    }

    public function addSubForum(Forum $subForum) {
        $this->getSubForumList()->add($subForum);
        $subForum->parent = $this;
    }

    public function getThreadList() : Collection {
        return $this->threadList;
    }

    public function addThread(Thread $thread) : Forum {
        $this->threadList->add($thread);
        ReflectionCache::getThreadForum()->setValue($thread, $this);
        return $this;
    }
    
    public function removeThread(Thread $thread) : Forum {
        $this->threadList->removeElement($thread);
        ReflectionCache::getThreadForum()->setValue($thread, null);
        return $this;
    }

    public static function create() : Forum {
        return new Forum();
    }

}
