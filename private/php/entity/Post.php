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

use DateTime;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Entity for post on the message board.
 *
 * @Entity
 * @Table(name="post")
 * 
 * @author CaptainMalzbier
 */
class Post extends AbstractEntity {

    /**
     * @Column(type="text", unique=false, nullable=false)
     * @Assert\NotNull(message="post.content.empty")
     * @var string The content (message) of this post.
     */
    protected $content;

    /**
     * @Column(name="creationtime", type="datetime", unique=false, nullable=false)
     * @Assert\NotNull(message="post.creationtime.empty")
     * @var DateTime 
     */
    protected $creationTime;

    /**
     * @Column(name="edittime", type="datetime", unique=false, nullable=true)
     * @var DateTime 
     */
    protected $editTime;

    /**
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     * @Assert\NotNull(message="post.user.empty")
     * @var string The user who posted this post.
     */
    protected $user;

    /**
     * @ManyToOne(targetEntity="Thread", inversedBy="postList")
     * @JoinColumn(name="thread_id", referencedColumnName="id", nullable=false)
     * @Assert\NotNull(message="post.thread.empty")
     * @var string The thread to which this post belongs to.
     */
    protected $thread;

    public function __construct() {
        $this->creationTime = new DateTime();
    }

    function getContent() {
        return $this->content;
    }

    function setContent($content) : Post {
        $this->content = $content;
        return $this;
    }

    public function getUser(): User {
        return $this->user;
    }

    public function setUser(User $user) : Post {
        $this->user = $user;
        return $this;
    }

    /**
     * @return Thread
     */
    public function getThread() {
        return $this->thread;
    }

    public function getCreationTime(): DateTime {
        return $this->creationTime;
    }
    
    public function setCreationTime(DateTime $creationTime) : Post {
        $this->creationTime = $creationTime;
        return $this;
    }

    /**
     * @return DateTime
     */
    function getEditTime() {
        return $this->editTime;
    }

    function setEditTime(DateTime $editTime = null) : Post {
        $this->editTime = $editTime;
        return $this;
    }

    /** @return Post */
    public static function create() : Post {
        return new Post();
    }

}
