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

namespace Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use ReflectionCache;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Description of Thread
 * 
 * @Entity
 * @Table(name="thread")
 * @author CaptainMalzbier
 */
class Thread extends AbstractEntity {

    /**
     * @Column(type="string", length=255, unique=false, nullable=false)
     * @Assert\NotNull(message="thread.name.empty")
     * @Assert\Length(min=1, max=255, minMessage="thread.name.empty", maxMessage="thread.name.maxlength")
     * @var string
     * thread name of this thread.
     */
    protected $name;

    /**
     * Each thread belongs to one forum.
     * @ManyToOne(targetEntity="Forum", inversedBy="threadList")
     * @JoinColumn(name="forum_id", referencedColumnName="id")
     * @Assert\NotNull(message="thread.forum.missing")
     */
    private $forum;

    /**
     * @OneToMany(targetEntity="Post", mappedBy="thread", fetch="EXTRA_LAZY")
     * @Assert\NotNull
     * @var ArrayCollection The posts this thread contains. Must be at least one post.
     */
    private $postList;
    
    
    /**
     * @Column(name="creationtime", type="datetime", unique=false, nullable=false)
     * @Assert\NotNull(message="post.creationtime.empty")
     * @var DateTime 
     */
    private $creationTime;

    public function __construct() {
        $this->postList = new ArrayCollection();
        $this->creationTime = new DateTime();
    }

    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function getForum() {
        return $this->forum;
    }

    public function getPostList(): Collection {
        return $this->postList;
    }

    public function addPost(Post $post) {
        $this->postList->add($post);
        ReflectionCache::getPostThread()->setValue($post, $this);
    }
    
    public function removePost(Post $post) {
        $this->postList->removeElement($post);
        ReflectionCache::getPostThread()->setValue($post, null);
    }
    
    public function getCreationTime(): DateTime {
        return $this->creationTime;
    }
}