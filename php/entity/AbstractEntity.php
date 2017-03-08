<?php

namespace Entity;

/**
 * Base entity with an id.
 * 
 * @author madgaksha
 */
class AbstractEntity {    
    /**
     * @Id
     * @Column(type="integer", length=32, unique=true, nullable=false)
     * @GeneratedValue(strategy="IDENTITY")
     * @var int
     */
    protected $id;

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