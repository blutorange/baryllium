<?php

namespace Entity;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Entity;
use Gettext\Translator;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Description of Forum
 * 
 * @Entity
 * @Table(name="forum")
 * @author CaptainMalzbier
 */
class Forum extends AbstractEntity {

    /**
     * @Column(type="string", length=255, unique=true, nullable=false)
     * @var string
     * forum name of this forum.
     */
    protected $name;

//    1 parent, one to one
//    many subforums one to many,
//    many threads one to many

    /**
     * One Category has Many Categories.
     * @OneToMany(targetEntity="Forum", mappedBy="parentForum")
     */
    private $subForumList;

    /**
     * Many Categories have One Category.
     * @ManyToOne(targetEntity="Forum", inversedBy="subForumList")
     * @JoinColumn(name="parent_id", referencedColumnName="id")
     */
    private $parentForum;
    
    /**
     * One forum may contain many threads or none
     * @OneToMany(targetEntity="Thread", mappedBy="forum")
     */
    private $threadList;

    public function __construct() {
        $this->subForumList = new ArrayCollection();
        $this->threadList = new ArrayCollection();
    }

    public function validate(array & $errMsg, Translator $translator): bool {
        return true;
    }

    public function validateMore(array & $errMsg, EntityManager $em, Translator $translator): bool {
        return true;
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
    
    public function getThreadList(){
        return $this->threadList;
    }
    
    public function setThreadList($threadList){
        $this->threadList = $threadList;
        foreach ($threadList as $thread) {
            $thread->forum = $this;
        }
    }
    
    public function addThread(Thread $thread){
        $this->getThreadList()->add($thread);
        $thread->setForum($this);
    }

}
