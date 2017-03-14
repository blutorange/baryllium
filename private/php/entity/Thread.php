<?php

namespace Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use Ui\Message;
use Ui\PlaceholderTranslator;

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
     * @var string
     * thread name of this thread.
     */
    private static $MAX_LENGTH_NAME = 255;
    protected $name;

    /**
     * Each thread belongs to one forum.
     * @ManyToOne(targetEntity="Forum", inversedBy="threadList")
     * @JoinColumn(name="forum_id", referencedColumnName="id")
     */
    private $forum;

    /**
     *
     * @OneToMany(targetEntity="Post", mappedBy="thread")
     * @var ArrayCollection The posts this thread contains. Must be at least one post.
     */
    private $postList;

    public function __construct() {
        $this->postList = new ArrayCollection();
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

    public function setForum(Forum $forum) {
        $this->forum = $forum;
    }

    public function getPostList(): ArrayCollection {
        return $this->postList;
    }

    public function setPostList(ArrayCollection $postList) {
        $this->postList = $postList;
        return $this;
    }

    public function validate(array & $errMsg, PlaceholderTranslator $translator): bool {
        $valid = true;
        $valid = $valid && $this->validateNonEmptyStringLength($this->name,
                        self::$MAX_LENGTH_NAME, $errMsg, $translator,
                        'error.validation', 'error.thread.name.empty',
                        'error.thread.name.overlong');
        $valid = $valid && $this->validateNonEmptyArray($this->name, $errMsg,
                        $translator, 'error.validation',
                        'error.thread.post.empty');
        return $valid;
    }
}