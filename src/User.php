<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Entity;

/**
 * Entity for users that may register and use the system.
 *
 * @Entity
 * @Table(name="user")
 * 
 * @author madgaksha
 */
class User {
    
    /**
     * @Id
     * @Column(type="integer", length=32, unique=true, nullable=false)
     * @GeneratedValue(strategy="IDENTITY")
     * @var int
     */
    protected $id;

    /**
     * @Column(type="string", length=64, unique=false, nullable=false)
     * @var string
     * User name of this user.
     */
    protected $uname;
    
    /**
     * @Column(type="string", length=32, unique=false, nullable=false)
     * @var string
     * Hashed password of this user.
     */
    protected $pwdhash;
    
    public function getUsername() : string {
        return $this->uname;
    }
    public function getPasswordHash() : string {
        return $this->pwdhash;
    }
    public function setUsername(string $uname) {
        $this->uname = $uname;
    }
    public function setPasswordHash(string $pwdhash) {
        $this->pwdhash = $pwdhash;
    }
}
