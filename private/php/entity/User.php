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
     * @Column(name="firstname", type="string", length=64, unique=true, nullable=true)
     * @var string
     * First name of this user.
     */
    protected $firstName;

    /**
     * @Column(name="lastname", type="string", length=64, unique=true, nullable=true)
     * @var string
     * Last name of this user.
     */
    protected $lastName;

    /**
     * @Column(name="username", type="string", length=64, unique=true, nullable=false)
     * @var string
     * User name of this user.
     */
    protected $userName;

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
     * @Column(name="regdate", type="date", unique=false, nullable=true)
     * @var string
     * Date when registered.
     */
    protected $regDate;

    /**
     * @Column(name="activatedate", type="date", unique=false, nullable=true)
     * @var string
     * Date when activated.
     */
    protected $activateDate;

    /**
     * @Column(name="activatetoken", type="string", length=255, unique=false, nullable=false)
     * @var string
     * Token for activation.
     */
    protected $activateToken;

    /**
     * @Column(name="isactivated", type="binary", unique=false, nullable=false)
     * @var bool
     * When the user is activated, we change the bool from FALSE 0 to TRUE 1.
     */
    protected $isActivated;
    
    /**
     * @Column(type="binary", unique=false, nullable=true)
     * @var bool
     * When the user is activated, we change the bool from FALSE 0 to TRUE 1.
     */
    protected $agb;

    /**
     * @Column(type="blob", unique=false, nullable=true)
     * @var blob
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

    public function setAGB(bool $agb = null) {
        $this->agb = $agb ?? false;
    }

    public function getAGB() {
        return $this->agb;
    }

    public function setFirstName(string $firstName = null) {
        $this->firstName = $firstName;
    }

    public function getFirstName() {
        return $this->firstName;
    }

    public function setLastName(string $lastName = null) {
        $this->lastName = $lastName;
    }

    public function getLastName() {
        return $this->lastName;
    }

    public function setUserName(string $userName) {
        $this->userName = $userName;
    }

    public function getUserName(): string {
        return $this->userName;
    }

    public function setMail(string $mail) {
        $this->mail = $mail;
    }

    public function getMail(): string {
        return $this->mail;
    }

    public function setRegDate(\DateTime $regdate = null) {
        $this->regDate = $regdate;
    }

    public function getRegDate() {
        return $this->regDate;
    }

    /**
     * Generates a unique activation token.
     */
    public function generateActivateToken() {
        $iPart1 = mt_rand(1000, 9999);
        $iPart2 = mt_rand(1000, 9999);
        $iPart3 = mt_rand(1000, 9999);
        $iPart4 = mt_rand(1000, 9999);
        $iPart5 = mt_rand(1000, 9999);

        $activateToken = "$iPart1-$iPart2-$iPart3-$iPart4-$iPart5";
        $this->setActivateToken($activateToken);
    }
    
    public function setActivateToken(string $activateToken) {
        $this->activateToken = $activateToken;
    }

    public function getActivateToken(): string {
        return $this->activateToken;
    }

    public function setActivateDate(\DateTime $activatedate = null) {
        $this->activateDate = $activatedate;
    }

    public function getActivateDate() {
        return $this->activateDate;
    }

    public function setAvatar($avatar = null)  {
        $this->avatar = $avatar;
    }

    public function getAvatar() {
        return $this->avatar;
    }

    public function setIsActivated(bool $isActivated) {
        $this->isActivated = $isActivated;
    }

    public function getIsActivated(): bool {
        return $this->isActivated;
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
        if (empty($password) || \EncryptionUtil::isWeakPwd($password)) {
            return;
        }
        $this->setPwdHash(\EncryptionUtil::hashPwd($password));
    }

    public function verifyPassword(string $password): bool {
        return \EncryptionUtil::verifyPwd($password, $this->pwdhash);
    }

    public function validate(array & $errMsg, Translator $translator): bool {
        $valid = true;
        if (empty($this->userName)) {
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
        if ($this->getDao($em)->existsMail($this->mail)) {
            array_push($errMsg, Message::dangerI18n('error.validation', 'register.mail.exists', $translator));
            $valid = false;
        }
        return $valid;
    }

    public function getDao(EntityManager $em): UserDao {
        return new UserDao($em);
    }

    public static function getAnon(): User {
        $user = new User();
        $user->setUserName("anon");
        $user->setId(AbstractEntity::$INVALID_ID);
        return $user;
    }

}
