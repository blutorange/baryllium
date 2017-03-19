<?php

namespace Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use ReflectionFieldList;
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
    private $children;

    /**
     * The parent forum. May be null for the topmost forum.
     * @ManyToOne(targetEntity="Forum", inversedBy="children")
     * @JoinColumn(name="parent_id", referencedColumnName="id", nullable=true)
     */
    private $parent;

    /**
     * One forum may contain one thread, many threads or none at all.
     * @OneToMany(targetEntity="Thread", mappedBy="forum", fetch="EXTRA_LAZY")
     * @Assert\NotNull
     */
    private $threadList;

    public function __construct() {
        $this->children = new ArrayCollection();
        $this->threadList = new ArrayCollection();
    }

    public function getName() {
        return $this->title;
    }

    public function setName($name) {
        $this->title = $name;
    }

    public function getParentForum() {
        return $this->parent;
    }

    public function setParentForum(Forum $parentForum = null) {
        $this->parent = $parentForum;
        if ($parentForum !== null) {
            $parentForum->children->add($this);
        }
    }

    public function getSubForumList() {
        return $this->children;
    }

    public function setSubForumList(ArrayCollection $subForumList) {
        $this->children = $subForumList;
        foreach ($subForumList as $f) {
            $f->parentForum = $this;
        }
    }

    public function addSubForum(Forum $subForum) {
        $this->getSubForumList()->add($subForum);
        $subForum->parent = $this;
    }

    public function getThreadList() {
        return $this->threadList;
    }

    public function addThread(Thread $thread) {
        $this->threadList->add($thread);
        ReflectionFieldList::getThreadForum()->setValue($thread, $this);
    }
    
    public function removeThread(Thread $thread) {
        $this->threadList->removeElement($thread);
        ReflectionFieldList::getThreadForum()->setValue($thread, null);
    }
}
