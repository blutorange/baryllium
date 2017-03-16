<?php

namespace Entity;

use Dao\UserDao;
use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use EncryptionUtil;
use Entity\AbstractEntity;
use Entity\User;
use Identicon\Identicon;
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

    public static $ROLE_STUDENT = 'student';
    public static $ROLE_LECTURER = 'lecturer';   
    public static $TABLE_NAME = "user";

    /**
     * @Column(name="username", type="string", length=64, unique=true, nullable=false)
     * @var string User name of this user.
     */
    protected $userName;
    private static $MAX_LENGTH_USERNAME = 64;
    private static $PATTERN_USERNAME = '/^[0-9a-zA-Z_-]+$/';

    /**
     * @Column(name="firstname", type="string", length=64, unique=false, nullable=true)
     * @var string Given name of this user.
     */
    protected $firstName;
    private static $MAX_LENGTH_FIRSTNAME = 64;

    /**
     * @Column(name="lastname", type="string", length=64, unique=false, nullable=true)
     * @var string Family name of this user.
     */
    protected $lastName;
    private static $MAX_LENGTH_LASTNAME = 64;

    /**
     * @Column(name="mail", type="string", length=255, unique=false, nullable=false)
     * @var string Email address of this user.
     */
    protected $mail;
    private static $MAX_LENGTH_MAIL = 255;

    /**
     * @Column(type="string", length=255, unique=false, nullable=false)
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
     * @var string NOT persisted. Only saved temporally for validation etc. Nullable.
     */
    protected $password;

    /**
     * @Column(name="is_sadmin", type="boolean", unique=false, nullable=true)
     * @var string Whether this use has got all permissions of a site admin. Null is false.
     */
    protected $isSiteAdmin;
    
    /**
     * @Column(name="is_sgadmin", type="boolean", unique=false, nullable=true)
     * @var string Whether this use has got all permissions of a study group admin. Null is false.
     */
    protected $isStudyGroupAdmin;

    /**
     * @Column(name="role", type="string", length=255, unique=false, nullable=true)
     * @var string The role of this user. May not have any role.
     * @Enum({"student", "lecturer"}) 
     */
    protected $role;

//    /**
//     * @OneToOne(targetEntity="ExpireToken", cascade={"persist"}))
//     * @JoinColumn(name="act_token", referencedColumnName="id", nullable=true)
//     * @var string Token for activation.
//     */
//    protected $activationToken;
    
    /**
     * @Column(name="is_activated", type="binary", unique=false, nullable=false)
     * @var bool
     * When the user is activated, we change the bool from FALSE 0 to TRUE 1.
     */
    protected $isActivated;
    
    /**
     * @Column(name="avatar", type="text", unique=false, nullable=true)
     * @var blob User profile image, stored as a base64 string with <code>data:MIMETYPE;base64,</code> prefixed.
     */
    protected $avatar;

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

    public function setMail(string $mail) {
        $this->mail = $mail;
    }

    public function getMail(): string {
        return $this->mail;
    }

    public function setRegDate(DateTime $regdate = null) {
        $this->regDate = $regdate;
    }

    public function getRegDate() {
        return $this->regDate;
    }

    public function setRole(string $role) {
        $this->role = $role;
    }

    public function setRoleStudent() {
        $this->setRole(self::$ROLE_STUDENT);
    }

    public function setRoleLecturer() {
        $this->setRole(self::$ROLE_LECTURER);
    }

    public function isRoleStudent(): bool {
        return $this->role === self::$ROLE_STUDENT;
    }
    
    public function isRoleLecturer(): bool {
        return $this->role === self::$ROLE_LECTURER;
    }

//    /**
//     * Generates a unique activation token.
//     */
//    public function generateActivationToken() {
//        $this->setActivationToken(new ExpireToken());
//    }
    
    public function getIsSiteAdmin() : bool {
        return $this->isSiteAdmin === true;
    }

    public function getIsStudyGroupAdmin() : bool {
        return $this->isStudyGroupAdmin === true;
    }

    public function setIsSiteAdmin(bool $isSiteAdmin = null) {
        $this->isSiteAdmin = $isSiteAdmin ?? false;
    }

    public function setIsStudyGroupAdmin(bool $isStudyGroupAdmin = null) {
        $this->isStudyGroupAdmin = $isStudyGroupAdmin ?? false;
    }

//    public function setActivationToken(ExpireToken $activateToken = null) {
//        $this->activationToken = $activateToken;
//    }
//    
//    public function clearActivationToken() {
//        $this->setActivationToken(null);
//    }
//
//    public function getActivationToken() {
//        return $this->activationToken;
//    }

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

    public function setIsActivated(bool $isActivated) {
        $this->isActivated = $isActivated;
    }

    public function getIsActivated(): bool {
        return $this->isActivated;
    }

    /**
     * Note that passwords are stored hashed with a salt.
     * @param string $password Password to set.
     */
    public function setPassword(string $password) {
        $this->password = $password;
        if (empty($password) || EncryptionUtil::isWeakPwd($password)) {
            return;
        }
        $this->setPwdHash(EncryptionUtil::hashPwd($password));
    }

    public function verifyPassword(string $password): bool {
        return EncryptionUtil::verifyPwd($password, $this->pwdhash);
    }
    
    public function validate(array & $errMsg, PlaceholderTranslator $translator) : bool {
        $valid = true;
        
        $valid = $valid && $this->validateNonEmptyStringLength($this->userName,
                        self::$MAX_LENGTH_USERNAME, $errMsg, $translator,
                        'error.validation', 'error.user.username.empty',
                        'error.user.username.overlong');
        if (preg_match(self::$PATTERN_USERNAME, $this->userName) !== 1) {
            array_push($errMsg, Message::dangerI18n('error.validation', 'error.user.username.illegal', $translator));
            $valid = false;            
        }

        $valid = $valid && $this->validateNonEmptyStringLength($this->mail,
                        self::$MAX_LENGTH_MAIL, $errMsg, $translator,
                        'error.validation', 'error.user.mail.empty',
                        'error.user.mail.overlong');        
        $valid = $valid && $this->validateStringLength($this->firstName,
                        self::$MAX_LENGTH_FIRSTNAME, $errMsg, $translator,
                        'error.validation', 'error.user.firstname.overlong');
        $valid = $valid && $this->validateStringLength($this->lastName,
                        self::$MAX_LENGTH_LASTNAME, $errMsg, $translator,
                        'error.validation', 'error.user.lastname.overlong');
        
        if (empty($this->password)) {
            array_push($errMsg, Message::dangerI18n('error.validation', 'error.pass.empty', $translator));
            $valid = false;
        } else if (EncryptionUtil::isWeakPwd($this->password)) {
            array_push($errMsg, Message::dangerI18n('error.security', 'error.pass.weak', $translator));
            $valid = false;
        } else if (empty($this->pwdhash)) {
            $this->setPassword($this->password);
        }
        
        if (!empty($this->role) && $this->role !== self::$ROLE_STUDENT && $this->role !== self::$ROLE_LECTURER) {
            array_push($errMsg, Message::dangerI18n('error.validation', 'error.user.role.unknown', $translator));
            $valid = false;
        }
        return $valid;
    }
    
    public function validateMore(array & $errMsg, EntityManager $em, PlaceholderTranslator $translator) : bool {
        $valid = true;
        if ($this->getDao($em)->existsMail($this->mail)) {
            array_push($errMsg, Message::dangerI18n('error.validation', 'register.mail.exists', $translator));
            $valid = false;
        }
        if ($this->getDao($em)->existsUsername($this->mail)) {
            array_push($errMsg, Message::dangerI18n('error.validation', 'register.user.exists', $translator));
            $valid = false;
        }
        return $valid;
    }

    public function getDao(EntityManager $em): UserDao {
        return new UserDao($em);
    }

    /**
     * Generates and sets the identicon based on the currently set username.
     * Does nothing when the username is null.
     */    
    public function generateIdenticonFromUsername() {
        if ($this->userName === null)
            return;
        $identicon = new Identicon();
        $imageData = $identicon->getImageDataUri($this->userName);
        $this->setAvatar($imageData);
    }

    public static function getAnonymousUser(): User {
        $user = new User();
        $user->setUserName("anonymous");
        $user->setIsSiteAdmin(false);
        $user->setIsStudyGroupAdmin(false);
        $this->setMail("anonymous@example.com");
        $user->setId(AbstractEntity::$INVALID_ID);
        return $user;
    }
}