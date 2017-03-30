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

namespace Entity;

use Dao\UserDao;
use DateTime;
use Doctrine\DBAL\Types\ProtectedString;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Util\EncryptionUtil;
use Identicon\Generator\GdGenerator;
use Identicon\Generator\ImageMagickGenerator;
use Identicon\Identicon;
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

    public static $TABLE_NAME = "user";

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
     * @var strring User profile image, stored as a base64 string with <code>data:MIMETYPE;base64,</code> prefixed.
     */
    protected $avatar;

    /**
     * @Column(name="mail", type="text", unique=false, nullable=true)
     * @Assert\Email
     * @var string Alternative mail address of this user.
     */
    protected $mail;    

    /**
     * @ManyToOne(targetEntity="TutorialGroup", )
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

    public function __construct() {
        $this->sessout = 0;
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

    public function setIsFieldOfStudyAdmin(bool $isFieldOfStudyAdmin = null) {
        $this->isFieldOfStudyAdmin = $isFieldOfStudyAdmin ?? false;
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
    
    public function getMail() {
        return $this->mail;
    }

    public function setMail($mail) {
        $this->mail = $mail;
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
        if ($password->isEmpty() || EncryptionUtil::isWeakPwd($password)) {
            $this->pwdhash = null;
            return;
        }
        $this->setPwdHash(EncryptionUtil::hashPwd($password));
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
    public function generateIdenticon() {
        $string = Uuid::uuid4();
        $generator = extension_loaded('gd') ? new GdGenerator() : new ImageMagickGenerator();
        $identicon = new Identicon($generator);
        $imageData = $identicon->getImageDataUri($string);
        $this->setAvatar($imageData);
    }
    
    /**
     * @return TutorialGroup
     */
    public function getTutorialGroup() {
        return $this->tutorialGroup;
    }

    public function setTutorialGroup(TutorialGroup $tutorialGroup = null) {
        $this->tutorialGroup = $tutorialGroup;
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
}