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
    private static $MAX_LENGTH_TITLE = 255;
    protected $title;
    
    /**
     * @Column(type="text", unique=false, nullable=false)
     * @var string The content (message) of this post.
     */
    protected $content;

    /**
     * @OneToOne(targetEntity="User")
     * @JoinColumn(name="user_id", referencedColumnName="id")
     * @var string The user who posted this post.
     */
    protected $user;            
    
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

    public function validate(array & $errMsg, PlaceholderTranslator $translator): bool {
        $valid = true;
        if (empty($this->title)) {
            array_push($errMsg,
                    Message::dangerI18n('error.validation',
                            'error.post.name.empty', $translator));
            $valid = false;
        }
        else if (strlen($this->title) > self::$MAX_LENGTH_TITLE) {
            array_push($errMsg,
                    Message::dangerI18n('error.validation',
                            'error.post.title.overlong', $translator,
                            ['count' => self::$MAX_LENGTH_TITLE]));
            $valid = false;
        }
        if (empty($this->content)) {
            array_push($errMsg,
                    Message::dangerI18n('error.validation',
                            'error.post.content.empty', $translator));
            $valid = false;
        }
        if (is_null($this->user)) {
            array_push($errMsg,
                    Message::dangerI18n('error.validation',
                            'error.post.user.null', $translator));
            $valid = false;
        }
        return $valid;
    }
}