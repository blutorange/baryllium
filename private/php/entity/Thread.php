<?php

namespace Entity;

use Doctrine\ORM\EntityManager;
use Gettext\Translator;

/**
 * Description of Thread
 * 
 * @Entity
 * @Table(name="thread")
 * @author CaptainMalzbier
 */
class Thread extends AbstractEntity {

    /**
     * @Column(type="string", length=255, unique=true, nullable=false)
     * @var string
     * thread name of this thread.
     */
    protected $name;

    /**
     * Many Features have One Product.
     * @ManyToOne(targetEntity="Forum", inversedBy="threadList")
     * @JoinColumn(name="forum_id", referencedColumnName="id")
     */
    private $forum;

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
    
    public function getForum(){
        return $this->forum;
    }
    
    public function setForum(Forum $forum){
        $this->forum = $forum;
    }

}
