<?php

namespace Entity;

use Dao\UserDao;
use DateTime;
use Doctrine\DBAL\Types\ProtectedString;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use EncryptionUtil;
use Identicon\Identicon;
use Symfony\Component\Validator\Constraints as Assert;

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
     * @Assert\NotBlank(message="user.username.empty")
     * @Assert\Length(max=64, maxMessage="user.username.maxlength")
     * @var string User name of this user.
     */
    protected $userName;

    /**
     * @Column(name="firstname", type="string", length=64, unique=false, nullable=true)
     * @Assert\Length(max=64, maxMessage="user.firstname.maxlength")
     * @var string Given name of this user.
     */
    protected $firstName;

    /**
     * @Column(name="lastname", type="string", length=64, unique=false, nullable=true)
     * @Assert\Length(max=64, maxMessage="user.lastname.maxlength")
     * @var string Family name of this user.
     */
    protected $lastName;

    /**
     * @Column(type="string", length=255, unique=false, nullable=false)
     * @Assert\NotBlank(message="user.pwdhash.blank")
     * @Assert\Length(max=128, maxMessage="user.pwdhash.maxlength")
     * @var string
     * Hashed password of this user.
     */
    protected $pwdhash;

    /**
     * @Column(name="reg_date", type="date", unique=false, nullable=true)
     * @var string
     * Date when registered.
     */
    protected $regDate;

    /**
     * @Column(name="act_date", type="date", unique=false, nullable=true)
     * @var string Date when this user account was activated.
     */
    protected $activationDate;

    /**
     * @Column(name="is_sadmin", type="boolean", unique=false, nullable=true)
     * @var string Whether this use has got all permissions of a site admin. Null is false.
     */
    protected $isSiteAdmin;
    
    /**
     * @Column(name="is_fosgadmin", type="boolean", unique=false, nullable=true)
     * @var string Whether this use has got all permissions of a study group admin. Null is false.
     */
    protected $isFieldOfStudyAdmin;

    /**
     * @Column(type="crypt_string", unique=false, nullable=true)
     * @var ProtectedString The password for CampusDual.
     */
    protected $passwordCampusDual;
    
    /**
     * @Column(name="is_activated", type="boolean", unique=false, nullable=false)
     * @var bool
     * When the user is activated, we change the bool from FALSE 0 to TRUE 1.
     */
    protected $isActivated;
    
    /**
     * @Column(name="avatar", type="text", unique=false, nullable=true)
     * @var blob User profile image, stored as a base64 string with <code>data:MIMETYPE;base64,</code> prefixed.
     */
    protected $avatar;
    
    
    /**
     * @Column(name="studentid", type="string", length=7, unique=false, nullable=true)
     * @Assert\Length(min=7, max=7, exactMessage="user.lastname.length")
     * @var string Student ID (Matrikelnummer).
     */
    protected $studentId;

    public function __construct() {
        $this->sessout = 0;
    }

    public function setUserName(string $userName) {
        $this->userName = $userName;
    }

    public function getUserName(): string {
        return $this->userName;
    }

    public function getPwdHash(): string {
        return $this->pwdhash;
    }

    public function setPwdHash(string $pwdhash) {
        $this->pwdhash = $pwdhash;
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

    public function setRegDate(DateTime $regdate = null) {
        $this->regDate = $regdate;
    }

    public function getRegDate() {
        return $this->regDate;
    }

    public function getIsSiteAdmin() : bool {
        return $this->isSiteAdmin ?? false;
    }

    public function setIsSiteAdmin(bool $isSiteAdmin = null) {
        $this->isSiteAdmin = $isSiteAdmin ?? false;
    }

    public function getIsFieldOfStudyAdmin() : bool {
        return $this->isFieldOfStudyAdmin === true;
    }

    public function setIsFieldOfStudyAdmin(bool $isSiteAdmin = null) {
        $this->isSiteAdmin = $isSiteAdmin ?? false;
    }

    public function setActivationDate(DateTime $activatedate = null) {
        $this->activationDate = $activatedate;
    }

    public function getActivationDate() {
        return $this->activationDate;
    }

    public function setAvatar(string $avatar = null)  {
        $this->avatar = $avatar;
    }

    public function getAvatar() {
        return $this->avatar;
    }
    
    public function getStudentId() {
        return $this->studentId;
    }

    public function setStudentId(string $studentId = null) {
        $this->studentId = $studentId;
    }

    public function setIsActivated(bool $isActivated) {
        $this->isActivated = $isActivated ?? false;
    }

    public function getIsActivated(): bool {
        return $this->isActivated ?? false;
    }

    public function getPasswordCampusDual() {
        return $this->passwordCampusDual;
    }
   
    public function setPasswordCampusDual(ProtectedString $passwordCampusDual) {
        $this->passwordCampusDual = $passwordCampusDual;
    }

    /**
     * Note that passwords are stored hashed with a salt.
     * @param string $password Password to set.
     */
    public function setPassword(ProtectedString $password) {
        if (empty($password->getString()) || EncryptionUtil::isWeakPwd($password->getString())) {
            $this->pwdhash = null;
            return;
        }
        $this->setPwdHash(EncryptionUtil::hashPwd($password));
    }

    public function verifyPassword(string $password): bool {
        return EncryptionUtil::verifyPwd($password, $this->pwdhash);
    }
    
    public function getDao(EntityManager $em): UserDao {
        return new UserDao($em);
    }

    /**
     * Generates a random identicon.
     * Does nothing when the student id is null.
     */    
    public function generateIdenticonId() {
        $string = \Ramsey\Uuid\Uuid::uuid4();
        $identicon = new Identicon();
        $imageData = $identicon->getImageDataUri($string);
        $this->setAvatar($imageData);
    }

    public static function getAnonymousUser(): User {
        $user = new User();
        $user->setUserName("anonymous");
        $user->setIsFieldOfStudyAdmin(false);
        $user->setIsSiteAdmin(false);
        $user->setId(AbstractEntity::$INVALID_ID);
        return $user;
    }
}