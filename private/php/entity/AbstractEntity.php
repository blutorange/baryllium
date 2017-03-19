<?php

namespace Entity;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Ui\Message;
use Ui\PlaceholderTranslator;

/**
 * Base entity with an id.
 * 
 * @author madgaksha
 */
class AbstractEntity {    
    
    public static $INVALID_ID = -1;
    public static $INITIAL_ID = 0;

    /**
     * @Id
     * @Column(type="integer", length=32, unique=true, nullable=false)
     * @GeneratedValue
     * @var int
     */
    protected $id = 0;
  
    /**
     * Checks whether this entity validates within a context of other entities.
     * Usually not necessary, use this only in some rare cases when the database
     * cannot do the validation itself.
     * @param arry $errMsg Array with error messages to append to.
     * @param locale CUrrent locale to use for the error messages.
     * @param em Entity manager for the context.
     * @return bool Whether this entity is valid.
     */
    public function validateMore(array & $errMsg, EntityManager $em, PlaceholderTranslator $translator) : bool {
        return true;
    }

    public function getId() : int {
        return $this->id;
    }
    public function setId(int $id) {
        $this->id = $id;
    }
}