<?php

use Doctrine\Common\Collections\ArrayCollection;
use Entity\AbstractEntity;
use Entity\UserGroup;
namespace Entity;

/**
 * Entity for users that may register and use the system.
 *
 * @Entity
 * @Table(name="user")
 * 
 * @author madgaksha
 */
class User extends AbstractEntity {
    const TABLE_NAME = "user";
    
    /**
     * @Column(type="string", length=64, unique=false, nullable=false)
     * @var string
     * User name of this user.
     */
    protected $username;
       
    /**
     * @Column(type="string", length=32, unique=false, nullable=false)
     * @var string
     * Hashed password of this user.
     */
    protected $pwdhash;
    
    /**
     * @ManyToMany(targetEntity="UserGroup")
     * @JoinTable(name="users_groups",
     *   joinColumns={@JoinColumn(name="user_id", referencedColumnName="id")},
     *   inverseJoinColumns={@JoinColumn(name="group_id", referencedColumnName="id")}
     * )
     * @var ArrayCollection All groups this user belongs to.
     */
    protected $groups;
    
    public function __construct() {
        $this->groups = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    
    public function setUsername(string $username) {
        $this->username = $username;
    }
    public function getUsername() : string {
        return $this->username;
    }

    public function getPwdHash() : string {
        return $this->pwdhash;
    }
    public function setPwdHash(string $pwdhash) {
        $this->pwdhash = $pwdhash;
    }

    public function getGroups() {
        return $this->groups;
    }
    public function setGroups(\Doctrine\Common\Collections\ArrayCollection $groups) {
        $groups->
        $this->groups = $groups;
    }
    
    public function addToGroup(UserGroup $group) {
        if ($group != null) {
            if ($this->groups == null) {
                $this->groups = new \Doctrine\Common\Collections\ArrayCollection();
            }
            $this->groups->add($group);
        }
    }
}