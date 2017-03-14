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

    public function validate(array & $errMsg, Translator $translator): bool {
        $valid = true;
        if (empty($this->name)) {
            array_push($errMsg,
                    Message::dangerI18n('error.validation',
                            'error.thread.name.empty', $translator));
            $valid = false;
        }
        else if (strlen($this->name) > self::$MAX_LENGTH_NAME) {
            array_push($errMsg,
                    Message::dangerI18n('error.validation',
                            'error.thread.name.overlong', $translator,
                            ['count' => self::$MAX_LENGTH_NAME]));
            $valid = false;
        }
        return $valid;
    }
}
