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
     * @Assert\Type("string")
     * @var string
     * thread name of this thread.
     */
    protected $name;

    /**
     * Each thread belongs to one forum.
     * @ManyToOne(targetEntity="Forum", inversedBy="threadList")
     * @JoinColumn(name="forum_id", referencedColumnName="id")
     * @Assert\NotNull(message="thread.forum.missing")
     * @Assert\Type("Entity\\Forum")
     */
    private $forum;

    /**
     * @OneToMany(targetEntity="Post", mappedBy="thread", fetch="EXTRA_LAZY")
     * @Assert\Type("Doctrine\\Common\\Collections\\ArrayCollection")
     * @Assert\NotNull
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

    public function getPostList(): ArrayCollection {
        return $this->postList;
    }

    public function addPost(Post $post) {
        $this->postList->add($post);
        ReflectionFieldList::getPostThread()->setValue($post, $this);
    }
    
    public function removePost(Post $post) {
        $this->postList->removeElement($post);
        ReflectionFieldList::getPostThread()->setValue($post, null);
    }
}