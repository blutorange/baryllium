<?php

namespace Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use Entity\AbstractEntity;
use Ui\Message;
use Ui\PlaceholderTranslator;

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
     * @Column(type="string", length=255, unique=false, nullable=false)
     * @var string The title of this post.
     */
    protected $title;
    private static $MAX_LENGTH_TITLE = 255;
    
    /**
     * @Column(type="text", unique=false, nullable=false)
     * @var string The content (message) of this post.
     */
    protected $content;

    /**
     * @OneToOne(targetEntity="User")
     * @JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     * @var string The user who posted this post.
     */
    protected $user;    
    
    /**
     * @ManyToOne(targetEntity="Thread", inversedBy="postList", fetch="EXTRA_LAZY")
     * @JoinColumn(name="thread_id", referencedColumnName="id", nullable=false)
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

    public function setThread(Thread $thread) : Post {
        $this->thread = $thread;
        return $this;
    }

    public function validate(array & $errMsg, PlaceholderTranslator $translator): bool {
        $valid = true;
        $valid = $valid && $this->validateNonEmptyStringLength($this->title,
                self::$MAX_LENGTH_TITLE, $errMsg, $translator,
                'error.validation', 'error.post.title.empty',
                'error.post.title.overlong');
        $valid = $valid && $this->validateNonEmpty($this->content, $errMsg, $translator,
                'error.validation', 'error.post.content.empty');
        $valid = $valid && $this->validateNonNull($this->user, $errMsg, $translator,
                'error.validation', 'error.post.user.null');
        $valid = $valid && $this->validateNonNull($this->thread, $errMsg, $translator,
                'error.validation', 'error.post.thread.null');
        return $valid;
    }
}