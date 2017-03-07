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

    
    public function getId() : int {
        return $this->id;
    }
    public function setId(int $id) {
        $this->id = $id;
    }
}