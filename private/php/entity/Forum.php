<?php

namespace Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use Gettext\Translator;
use Ui\Message;

/**
 * Description of Forum
 * 
 * @Entity
 * @Table(name="forum")
 * @author CaptainMalzbier
 */
class Forum extends AbstractEntity {

    /**
     * @Column(type="string", length=255, unique=false, nullable=false)
     * @var string Some arbitrary name of this forum.
     */
    private static $MAX_LENGTH_NAME = 255;
    protected $name;

    /**
     * List of forums this forum contains.
     * @OneToMany(targetEntity="Forum", mappedBy="parentForum")
     */
    private $subForumList;

    /**
     * The parent forum. May be null for the topmost forum.
     * @ManyToOne(targetEntity="Forum", inversedBy="subForumList")
     * @JoinColumn(name="parent_id", referencedColumnName="id")
     */
    private $parentForum;

    /**
     * One forum may contain one thread, many threads or none at all.
     * @OneToMany(targetEntity="Thread", mappedBy="forum")
     */
    private $threadList;

    public function __construct() {
        $this->subForumList = new ArrayCollection();
        $this->threadList = new ArrayCollection();
    }

    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function getParentForum() {
        return $this->parentForum;
    }

    public function setParentForum(Forum $parentForum = null) {
        $this->parentForum = $parentForum;
        if ($parentForum !== null) {
            $parentForum->subForumList->add($this);
        }
    }

    public function getSubForumList() {
        return $this->subForumList;
    }

    public function setSubForumList(ArrayCollection $subForumList) {
        $this->subForumList = $subForumList;
        foreach ($subForumList as $f) {
            $f->parentForum = $this;
        }
    }

    public function addSubForum(Forum $subForum) {
        $this->getSubForumList()->add($subForum);
        $subForum->parentForum = $this;
    }

    public function getThreadList() {
        return $this->threadList;
    }

    public function setThreadList($threadList) {
        $this->threadList = $threadList;
        foreach ($threadList as $thread) {
            $thread->forum = $this;
        }
    }

    public function addThread(Thread $thread) {
        $this->getThreadList()->add($thread);
        $thread->setForum($this);
    }

    public function validate(array & $errMsg, Translator $translator): bool {
        $valid = true;
        if (empty($this->name)) {
            array_push($errMsg,
                    Message::dangerI18n('error.validation',
                            'error.forum.name.empty', $translator));
            $valid = false;
        }
        else if (strlen($this->name) > self::$MAX_LENGTH_NAME) {
            array_push($errMsg,
                    Message::dangerI18n('error.validation',
                            'error.forum.name.overlong', $translator,
                            ['count' => self::$MAX_LENGTH_NAME]));
            $valid = false;
        }
        return $valid;
    }

}
