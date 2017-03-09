<?php
namespace Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Entity\AbstractEntity;
use Entity\UserGroup;
use Entity\User;

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
     * @Column(type="string", length=255, unique=false, nullable=false)
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
    
    /**
     * @var string NOT persisted. Only saved temporally for validation etc. Nullable.
     */
    private $password;

    public function __construct() {
        $this->groups = new \Doctrine\Common\Collections\ArrayCollection();
        $this->sessout = 0;
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
    
    /**
     * Note that passwords are stored hashed with a salt.
     * @param string $password Password to set.
     */
    public function setPassword(string $password) {
        $this->password = $password;
        if (empty($password) || \EncryptionUtil::isWeakPwd($password))
            return;
        $this->setPwdHash(\EncryptionUtil::hashPwd($password));
    }
    
    public function verifyPassword(string $password) : bool {
        return \EncryptionUtil::verifyPwd($password, $this->pwdhash);
    }
    
    public function validate(array & $errMsg, string $locale) : bool {
        $valid = true;
        if (empty($this->username)) {
            array_push($errMsg, "Username must not be empty.");
            $valid = false;
        }
        if (empty($this->password)) {
            array_push($errMsg, "Password must not be empty.");
            $valid = false;
        }
        else if (\EncryptionUtil::isWeakPwd($this->password)) {
            array_push($errMsg, "Password is too weak.");
            $valid = false;
        }
        else if (empty($this->pwdhash)) {
            $this->setPassword($this->password);
        }
        return $valid;
    }
    
    public static function getAnon() : User {
        $user = new User();
        $user->setUsername("anon");
        return $user;
    }
}