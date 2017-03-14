<?php

namespace Entity;

use Gettext\Translator;
use Doctrine\Common\Collections\ArrayCollection;
use Entity\AbstractEntity;
use Entity\Mail;
use Doctrine\ORM\EntityManager;
use Ui\Message;
use Dao\UserDao;

/**
 * Entity for EMails they are sent to Users
 *
 * @Entity
 * @Table(name="mail")
 * 
 * @author madgaksha
 */
class Mail extends AbstractEntity {

    const TABLE_NAME = "mail";

    /**
     * @Column(name="userid", type="integer", unique=false, nullable=false)
     * @var int
     * ID from the user-table .
     */
    protected $userid;

    /**
     * @Column(name="reciveremail", type="string", length=255, unique=false, nullable=false)
     * @var string
     * The adress that we used to sent the email.
     */
    protected $reciveremail;

    /**
     * @Column(name="subject", type="string", length=255, unique=true, nullable=false)
     * @var string
     * The subject of the mail.
     */
    protected $subject;

    /**
     * @Column(type="string", length=25500, unique=true, nullable=false)
     * @var string
     * The content of the email.
     */
    protected $content;

    /**
     * @Column(name="sentdate", type="date", unique=false, nullable=true)
     * @var string
     * Date when the mail was sent.
     */
    protected $sentDate;

    /**
     * @Column(name="issent", type="binary", unique=false, nullable=false)
     * @var bool
     * When the Email was sent, we change the bool from FALSE 0 to TRUE 1.
     */
    protected $isSent;
    
    public function setIsSent(bool $isSent = null) {
        $this->isSent = $isSent ?? false;
    }

    public function getIsSent() {
        return $this->isSent;
    }

    public function setReciverEmail(string $reciveremail = null) {
        $this->reciveremail = $reciveremail;
    }

    public function getReciverEmail() {
        return $this->reciveremail;
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
