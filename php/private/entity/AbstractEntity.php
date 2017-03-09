<?php

namespace Entity;

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
     * 
     * @param type $errMsg
     * @return bool
     */
    public function validate(array & $errMsg, string $locale) : bool {
        return true;
    }
    
    public function getId() : int {
        return $this->id;
    }
    public function setId(int $id) {
        $this->id = $id;
    }
    
    public function persist(\Doctrine\ORM\EntityManager $em, string $locale) : array {
        $arr = [];
        if ($this->id == AbstractEntity::$INVALID_ID) {
            array_push($arr, "Cannot persist invalid entity.");
            return $arr;
        }
        $res = $this->validate($arr, $locale);
        if ($res) {
            try {
                $em->persist($this);
            }
            catch (Exception $e) {
                array_push($arr, "Error during database transaction: " . $e->getMessage());
            }
        }    
        else if (sizeof($arr) === 0) {
            array_push($arr, "Unspecified error during entity validate.");
        }
        return $arr;
    }
}