<?php

namespace Entity;

use Gettext\Translator;
use Doctrine\Common\Collections\ArrayCollection;
use Entity\AbstractEntity;
use Entity\UserGroup;
use Entity\User;
use Doctrine\ORM\EntityManager;
use Ui\Message;
use Dao\UserDao;

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
     * @Column(type="string", length=64, unique=true, nullable=false)
     * @var string
     * First name of this user.
     */
    protected $firstname;

    /**
     * @Column(type="string", length=64, unique=true, nullable=false)
     * @var string
     * Last name of this user.
     */
    protected $lastname;

    /**
     * @Column(type="string", length=64, unique=true, nullable=false)
     * @var string
     * User name of this user.
     */
    protected $username;

    /**
     * @Column(type="string", length=255, unique=true, nullable=false)
     * @var string
     * E-Mail of this user.
     */
    protected $mail;

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
     * @Column(type="date", unique=false, nullable=true)
     * @var string
     * Date when registered.
     */
    protected $regdate;

    /**
     * @Column(type="date", unique=false, nullable=true)
     * @var string
     * Date when activated.
     */
    protected $activatedate;

    /**
     * @Column(type="string", length=255, unique=false, nullable=true)
     * @var string
     * Token for activation.
     */
    protected $activatetoken;

    /**
     * @Column(type="binary", unique=false, nullable=true)
     * @var string
     * When the user is activated, we change the bool from FALSE 0 to TRUE 1.
     */
    protected $isactivated;

    /**
     * @Column(type="blob", unique=false, nullable=true)
     * @var bolb
     * User profilpicture
     */
    protected $avatar;

    /**
     * @var string NOT persisted. Only saved temporally for validation etc. Nullable.
     */
    private $password;

    public function __construct() {
        $this->groups = new \Doctrine\Common\Collections\ArrayCollection();
        $this->sessout = 0;
    }

    public function setFirstname(string $firstname) {
        $this->firstname = $firstname;
    }

    public function getFirstname(): string {
        return $this->firstname;
    }

    public function setLastname(string $lastname) {
        $this->lastname = $lastname;
    }

    public function getLastname(): string {
        return $this->Lastname;
    }

    public function setUsername(string $username) {
        $this->username = $username;
    }

    public function getUsername(): string {
        return $this->username;
    }

    public function setMail(string $mail) {
        $this->mail = $mail;
    }

    public function getMail(): string {
        return $this->mail;
    }

    public function getPwdHash(): string {
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

    public function verifyPassword(string $password): bool {
        return \EncryptionUtil::verifyPwd($password, $this->pwdhash);
    }

    public function validate(array & $errMsg, Translator $translator): bool {
        $valid = true;
        if (empty($this->username)) {
            array_push($errMsg, Message::dangerI18n('error.validation', 'error.user.empty', $translator));
            $valid = false;
        }
        if (empty($this->password)) {
            array_push($errMsg, Message::dangerI18n('error.validation', 'error.pass.empty', $translator));
            $valid = false;
        } else if (\EncryptionUtil::isWeakPwd($this->password)) {
            array_push($errMsg, Message::dangerI18n('error.security', 'error.pass.weak', $translator));
            $valid = false;
        } else if (empty($this->pwdhash)) {
            $this->setPassword($this->password);
        }
        return $valid;
    }

    public function validateMore(array & $errMsg, EntityManager $em, Translator $translator): bool {
        $valid = true;
        if ($this->existsUsername($em)) {
            array_push($errMsg, Message::dangerI18n('error.validation', 'register.user.exists', $translator));
            $valid = false;
        }
        return $valid;
    }

    public function existsUsername(EntityManager $em): bool {
        return (new \Dao\UserDao($em))->findOneByField('username', $this->username) != null;
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
