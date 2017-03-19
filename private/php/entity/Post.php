<?php

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
     * @Assert\Type("string")
     * @var string The title of this post.
     */
    protected $title;
    
    /**
     * @Column(type="text", unique=false, nullable=false)
     * @Assert\NotNull(message="post.content.empty")
     * @Assert\Type("string")
     * @var string The content (message) of this post.
     */
    protected $content;

    /**
     * @OneToOne(targetEntity="User")
     * @JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     * @Assert\NotNull(message="post.user.empty")
     * @Assert\Type("Entity\\User")
     * @var string The user who posted this post.
     */
    protected $user;    
    
    /**
     * @ManyToOne(targetEntity="Thread", inversedBy="postList")
     * @JoinColumn(name="thread_id", referencedColumnName="id", nullable=false)
     * @Assert\NotNull(message="post.thread.empty")
     * @Assert\Type("Entity\\Thread")
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