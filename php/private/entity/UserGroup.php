<?php
namespace Entity;

use Entity\AbstractEntity;

/**
 * Entity for users that may register and use the system.
 *
 * @Entity
 * @Table(name="usergroup")
 * 
 * @author madgaksha
 */
class UserGroup extends AbstractEntity {

    public function __construct() {
    }
    
    /**
     * @Column(type="string", length=64, unique=false, nullable=false)
     * @var string Name of this user group.
     */
    protected $name;
          
    public function getName() : string {
        return $this->name;
    }
    public function setName(string $name) {
        $this->name = $name;
    }
}