<?php

namespace Entity;

use Doctrine\ORM\EntityManager;
use Ui\Message;

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
     * @param arry $errMsg Array with error messages to append to.
     * @return bool Whether this entity validates standalone.
     */
    public function validate(array & $errMsg, string $locale) : bool {
        return true;
    }
    
    /**
     * @param arry $errMsg Array with error messages to append to.
     * @param locale CUrrent locale to use for the error messages.
     * @param em Entity manager for the context.
     * @return bool Whether this entity validates within a context of other entities. No need to repeat what validate did.
     */
    public function validateMore(array & $errMsg, string $locale, EntityManager $em) : bool {
        return true;
    }

    public function getId() : int {
        return $this->id;
    }
    public function setId(int $id) {
        $this->id = $id;
    }
    
    public function persist(EntityManager $em, string $locale) : array {
        $arr = [];
        if ($this->id == AbstractEntity::$INVALID_ID) {
            array_push(Message::danger("Internal error.", "Cannot persist invalid entity."));
            return $arr;
        }
        $res = $this->validate($arr, $locale) && $this->validateMore($arr, $locale, $em);
        if ($res) {
            try {
                $em->persist($this);
            }
            catch (\Throwable $e) {
                array_push($arr, Message::danger("Database error", $e->getMessage()));
            }
        }    
        else if (sizeof($arr) === 0) {
            array_push($arr, Message::danger("Validation error", "No errors details given during entity validate."));
        }
        return $arr;
    }
}