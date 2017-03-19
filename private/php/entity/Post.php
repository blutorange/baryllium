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

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use Entity\AbstractEntity;
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
     * @Column(name="title", type="string", length=255, unique=false, nullable=false)
     * @Assert\NotNull(message="post.title.empty")
     * @Assert\Length(min=1, max=255, minMessage="post.name.empty", maxMessage="post.name.maxlength")
     * @var string The title of this post.
     */
    protected $title;
    
    /**
     * @Column(type="text", unique=false, nullable=false)
     * @Assert\NotNull(message="post.content.empty")
     * @var string The content (message) of this post.
     */
    protected $content;

    /**
     * @OneToOne(targetEntity="User")
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
    
    public function getContent(): string {
        return $this->title;
    }

    public function setContent(string $title) {
        $this->title = $title;
    }
    
    public function getUser() : User {
        return $this->user;
    }
    
    public function setUser(User $user) {
        $this->user = $user;
    }
    
    public function getTitle(): string {
        return $this->title;
    }

    public function setTitle(string $title) {
        $this->title = $title;
    }
    
    public function getThread() {
        return $this->thread;
    }
}