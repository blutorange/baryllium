<?php

namespace Entity;

use Dao\UserDao;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Table;
use EncryptionUtil;
use Entity\AbstractEntity;
use Entity\User;
use Ui\Message;
use Ui\PlaceholderTranslator;

/**
 * Entity for users that may register and use the system.
 *
 * @Entity
 * @Table(name="user")
 * 
 * @author madgaksha
 */
class User extends AbstractEntity {

    public static $TABLE_NAME = "user";

    /**
     * @Column(name="username", type="string", length=64, unique=true, nullable=false)
     * @var string User name of this user.
     */
    protected $userName;    

        
    /**
     * @Column(name="firstName", type="string", length=255, unique=false, nullable=true)
     * @var string Given name of this user.
     */
    protected $firstName;  
    
    /**
     * @Column(name="lastname", type="string", length=255, unique=false, nullable=true)
     * @var string Family name of this user.
     */
    protected $lastName;
    
    /**
     * @Column(type="string", length=255, unique=false, nullable=false)
     * @var string
     * Hashed password of this user.
     */
    protected $pwdhash;


    /**
     * @var string NOT persisted. Only saved temporally for validation etc. Nullable.
     */
    protected $password;

    /**
     * @Column(name="role", type="string", length=255, unique=false, nullable=false)
     * @var string The role of this user.
     */
    protected $role;
    
    /**
     * @Column(name="mail", type="string", length=255, unique=false, nullable=false)
     * @var string Email address of this user.
     */
    private $mail;
       
    public function __construct() {
        $this->sessout = 0;
    }

    public function setUsername(string $username) {
        $this->userName = $username;
    }

    public function getUsername(): string {
        return $this->userName;
    }

    public function getPwdHash(): string {
        return $this->pwdhash;
    }

    public function setPwdHash(string $pwdhash) {
        $this->pwdhash = $pwdhash;
    }

    public function setMail(string $mail) {
        $this->mail = $mail;
    }

    public function getMail(): string {
        return $this->mail;
    }

    public function setRole(string $role) {
        $this->role = $role;
    }

    public function getRole(): string {
        return $this->role;
    }

    public function setFirstName(string $firstName) {
        $this->firstName = $firstName;
    }

    public function getFirstName(): string {
        return $this->firstName;
    }

    public function setLastName(string $lastName) {
        $this->lastName = $lastName;
    }

    public function getLastName(): string {
        return $this->lastName;
    }

    /**
     * Note that passwords are stored hashed with a salt.
     * @param string $password Password to set.
     */
    public function setPassword(string $password) {
        $this->password = $password;
        if (empty($password) || EncryptionUtil::isWeakPwd($password))
            return;
        $this->setPwdHash(EncryptionUtil::hashPwd($password));
    }

    public function verifyPassword(string $password): bool {
        return EncryptionUtil::verifyPwd($password, $this->pwdhash);
    }
    
    public function validate(array & $errMsg, PlaceholderTranslator $translator) : bool {
        $valid = true;
        if (empty($this->userName)) {
            array_push($errMsg, Message::dangerI18n('error.validation', 'error.user.empty', $translator));
            $valid = false;
        }
        if (empty($this->password)) {
            array_push($errMsg, Message::dangerI18n('error.validation', 'error.pass.empty', $translator));
            $valid = false;
        } else if (EncryptionUtil::isWeakPwd($this->password)) {
            array_push($errMsg, Message::dangerI18n('error.security', 'error.pass.weak', $translator));
            $valid = false;
        } else if (empty($this->pwdhash)) {
            $this->setPassword($this->password);
        }
        return $valid;
    }
    
    public function validateMore(array & $errMsg, EntityManager $em, PlaceholderTranslator $translator) : bool {
        $valid = true;
        if ($this->existsUsername($em)) {
            array_push($errMsg, Message::dangerI18n('error.validation', 'register.user.exists', $translator));
            $valid = false;
        }
        return $valid;
    }

    public function existsUsername(EntityManager $em): bool {
        return (new \Dao\UserDao($em))->findOneByField('username', $this->userName) != null;
    }

    public function getDao(EntityManager $em): UserDao {
        return new UserDao($em);
    }

    public static function getAnon(): User {
        $user = new User();
        $user->setUsername("anon");
        $user->setId(AbstractEntity::$INVALID_ID);
        return $user;
    }
}
