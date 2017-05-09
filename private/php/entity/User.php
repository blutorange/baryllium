<?php

/* Note: This license has also been called the "New BSD License" or "Modified
 * BSD License". See also the 2-clause BSD License.
 * 
 * Copyright 2015 The Moose Team
 * 
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 * 
 * 1. Redistributions of source code must retain the above copyright notice,
 * this list of conditions and the following disclaimer.
 * 
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 * this list of conditions and the following disclaimer in the documentation
 * and/or other materials provided with the distribution.
 * 
 * 3. Neither the name of the copyright holder nor the names of its contributors
 * may be used to endorse or promote products derived from this software without
 * specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

namespace Moose\Entity;

use DateTime;
use Doctrine\DBAL\Types\ProtectedString;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Identicon\Generator\GdGenerator;
use Identicon\Generator\ImageMagickGenerator;
use Identicon\Identicon;
use Moose\Dao\UserDao;
use Moose\Util\EncryptionUtil;
use Ramsey\Uuid\Uuid;
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
     * @Column(name="is_fosadmin", type="boolean", unique=false, nullable=true)
     * @var string Whether this use has got all permissions of a study group admin. Null is false.
     */
    protected $isFieldOfStudyAdmin;

    /**
     * @Column(name="pass_cdual", type="crypt_string", unique=false, nullable=true)
     * @var ProtectedString The password for CampusDual.
     */
    protected $passwordCampusDual;
    
    /**
     * @Column(name="is_activated", type="boolean", unique=false, nullable=false)
     * @var bool Whether this user is activated.
     * When the user is activated, we change the bool from FALSE 0 to TRUE 1.
     */
    protected $isActivated;
    
    /**
     * @Column(name="avatar", type="text", unique=false, nullable=true)
     * @Assert\Regex(message="user.avatar.invalid", pattern="/^data:image\/(png|jpg|jpeg|gif);base64,[a-zA-Z0-9+\/]+={0,3}$/")
     * @Assert\Length(max=500000, maxMessage="user.avatar.maxlength")
     * @var string User profile image, stored as a base64 string with <code>data:MIMETYPE;base64,</code> prefixed.
     */
    protected $avatar;

    /**
     * @Column(name="mail", type="string", length=255, unique=false, nullable=true)
     * @Assert\Email(message="user.mail.invalid")
     * @Assert\Length(max=255, maxMessage="user.mail.maxlength")
     * @var string Alternative mail address of this user.
     */
    protected $mail;    

    /**
     * @ManyToOne(targetEntity="TutorialGroup")
     * @JoinColumn(name="tutgroup_id", referencedColumnName="id")
     * @var TutorialGroup
     */
    protected $tutorialGroup;
    
    /**
     * @Column(name="studentid", type="string", length=7, unique=true, nullable=true)
     * @Assert\Length(min=7, max=7, exactMessage="user.studentid.length")
     * @var string Student ID (Matrikelnummer).
     */
    protected $studentId;

    /** @var int Time in milliseconds until the user's session times out. <= 0 or null for immediate timeout. */
    protected $sessout;

    public function __construct() {
        $this->sessout = 0;
        $this->isActivated = false;
    }

    public function getPwdHash(): string {
        return $this->pwdhash;
    }

    public function setPwdHash(string $pwdhash) : User {
        $this->pwdhash = $pwdhash;
        return $this;
    }

    public function setFirstName(string $firstName = null) : User {
        $this->firstName = $firstName;
        return $this;
    }

    public function getFirstName() {
        return $this->firstName;
    }

    public function setLastName(string $lastName = null) : User {
        $this->lastName = $lastName;
        return $this;
    }

    public function getLastName() {
        return $this->lastName;
    }

    public function setRegDate(DateTime $regdate = null) : User {
        $this->regDate = $regdate;
        return $this;
    }

    public function getRegDate() {
        return $this->regDate;
    }

    public function getIsSiteAdmin() : bool {
        return $this->isSiteAdmin ?? false;
    }
    
    public function setIsSiteAdmin(bool $isSiteAdmin = null) : User {
        $this->isSiteAdmin = $isSiteAdmin ?? false;
        return $this;
    }

    public function getIsFieldOfStudyAdmin() : bool {
        return $this->isFieldOfStudyAdmin === true;
    }

    public function setIsFieldOfStudyAdmin(bool $isFieldOfStudyAdmin = null) : User{
        $this->isFieldOfStudyAdmin = $isFieldOfStudyAdmin ?? false;
        return $this;
    }

    public function setActivationDate(DateTime $activatedate = null) : User {
        $this->activationDate = $activatedate;
        return $this;
    }

    public function getActivationDate() {
        return $this->activationDate;
    }

    public function setAvatar(string $avatar = null) : User {
        $this->avatar = $avatar;
        return $this;
    }

    public function getAvatar() {
        return $this->avatar;
    }
    
    public function getStudentId() {
        return $this->studentId;
    }

    public function setStudentId(string $studentId = null) : User {
        $this->studentId = $studentId;
        return $this;
    }

    public function setIsActivated(bool $isActivated) : User {
        $this->isActivated = $isActivated ?? false;
        return $this;
    }

    public function getIsActivated(): bool {
        return $this->isActivated ?? false;
    }
    
    public function getMail() {
        return $this->mail;
    }

    public function setMail($mail) : User {
        $this->mail = empty($mail) ? null : $mail;
        return $this;
    }

    /** @return ProtectedString */
    public function getPasswordCampusDual() {
        return $this->passwordCampusDual;
    }
   
    /**
     * 
     * @param ProtectedString|string $passwordCampusDual
     * @return \Moose\Entity\User
     */
    public function setPasswordCampusDual($passwordCampusDual = null) : User{
        if (\is_string($passwordCampusDual)) {
            $passwordCampusDual = new ProtectedString($passwordCampusDual);
        }
        $this->passwordCampusDual = $passwordCampusDual;
        return $this;
    }

    /**
     * Note that passwords are stored hashed with a salt.
     * @param ProtectedString $password Password to set.
     */
    public function setPassword(ProtectedString $password) : User {
        if ($password->isEmpty() || EncryptionUtil::isWeakPwd($password)) {
            $this->pwdhash = null;
            return $this;
        }
        $this->setPwdHash(EncryptionUtil::hashPwd($password));
        return $this;
    }

    public function verifyPassword(ProtectedString $password): bool {
        return EncryptionUtil::verifyPwd($password, $this->pwdhash);
    }
    
    public function getDao(EntityManager $em): UserDao {
        return new UserDao($em);
    }

    /**
     * Generates a random identicon.
     * Does nothing when the student id is null.
     */    
    public function generateIdenticon(string $seed = null) : User {
        $string = $seed ?? Uuid::uuid4();
        $generator = extension_loaded('gd') ? new GdGenerator() : new ImageMagickGenerator();
        $identicon = new Identicon($generator);
        $imageData = $identicon->getImageDataUri($string);
        $this->setAvatar($imageData);
        return $this;
    }
    
    /**
     * @return TutorialGroup
     */
    public function getTutorialGroup() {
        return $this->tutorialGroup;
    }

    public function setTutorialGroup(TutorialGroup $tutorialGroup = null) : User {
        $this->tutorialGroup = $tutorialGroup;
        return $this;
    }

    public static function getAnonymousUser(): User {
        $user = new User();
        $user->setFirstName("anonymous");
        $user->setLastName("anonymous");
        $user->setIsFieldOfStudyAdmin(false);
        $user->setIsSiteAdmin(false);
        $user->setId(AbstractEntity::INVALID_ID);
        return $user;
    }
    
    public static function create() : User {
        return new User();
    }

    /**
     * @return string The custom mail, or the student mail, or null,
     * in that order of precedence.
     */
    public function getOtherOrStudentMail() {
        $mail = $this->getMail();
        if ($mail !== null) return $mail;
        $mail = $this->getStudentMail();
        if ($mail !== null) return $mail;       
        return null;
    }
    
    public function getStudentMail() {
        if ($this->studentId !== null) {
           $tutGroup = $this->getTutorialGroup();
           if ($tutGroup !== null) {
                return $tutGroup->getUniversity()->getMailAddressForStudentId($this->studentId);               
           }
        }
        return null;
    }

    public function hasCampusDualCredentials() : bool {
        return $this->getStudentId() !== null && $this->getPasswordCampusDual() !== null;
    }

}