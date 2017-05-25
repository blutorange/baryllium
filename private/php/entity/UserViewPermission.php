<?php

/* The 3-Clause BSD License
 * 
 * SPDX short identifier: BSD-3-Clause
 *
 * Note: This license has also been called the "New BSD License" or "Modified
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

use Phinx\Db\Table\Column;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Validator\Tests\Fixtures\Entity;

/**
 * Stores info on what data is visible to other users.
 *
 * @Entity
 * @Table(name="userviewpermission")
 * @author madgaksha
 */
class UserViewPermission extends AbstractEntity {
    
    const FIELDS_TO_DB = [
        'firstName' => true,
        'lastName' => true,
        'studentId' => true,
        'mail' => true,
        'tutorialGroup' => true,
        'regDate' => true,
        'activationDate' => true
    ];
    
    public function __construct() {
        $this->firstName = true;
        $this->lastName = true;
        $this->studentId = false;
        $this->mail = false;
        $this->tutorialGroup = true;
        $this->regDate = true;
        $this->activationDate = true;
    }
    
    /**
     * @Column(name="studentid", type="boolean")
     * @var bool
     */
    protected $studentId;

    /**
     * @Column(name="firstname", type="boolean")
     * @var bool
     */
    protected $firstName;
    
    /**
     * @Column(name="lastname", type="boolean")
     * @var bool
     */
    protected $lastName;
    
    /**
     * @Column(name="reg_date", type="boolean")
     * @var bool
     */
    protected $regDate;

    /**
     * @Column(name="act_date", type="boolean")
     * @var bool
     */
    protected $activationDate;

    /**
     * @Column(name="mail", type="boolean")
     * @var bool The user's alternative mail address (not from the BA).
     */
    protected $mail;
    
    /**
     * @Column(name="tutgroup_id", type="boolean")
     * @var bool
     */
    protected $tutorialGroup;

    public function getStudentId() : bool {
        return $this->studentId ?? false;
    }

    public function getFirstName() : bool {
        return $this->firstName ?? false;
    }

    public function getLastName() : bool {
        return $this->lastName ?? false;
    }

    public function getRegDate() : bool {
        return $this->regDate ?? false;
    }

    public function getActivationDate() : bool {
        return $this->activationDate ?? false;
    }

    public function getMail() : bool {
        return $this->mail ?? false;
    }

    public function getTutorialGroup() : bool {
        return $this->tutorialGroup ?? false;
    }

    public function setStudentId(bool $studentId = null) {
        $this->studentId = $studentId ?? false;
        return $this;
    }

    public function setFirstName(bool $firstName = null) {
        $this->firstName = $firstName ?? false;
        return $this;
    }

    public function setLastName(bool $lastName = null) {
        $this->lastName = $lastName ?? false;
        return $this;
    }

    public function setRegDate(bool $regDate = null) {
        $this->regDate = $regDate ?? false;
        return $this;
    }

    public function setActivationDate(bool $activationDate = null) {
        $this->activationDate = $activationDate ?? false;
        return $this;
    }

    public function setMail(bool $mail = null) {
        $this->mail = $mail ?? false;
        return $this;
    }

    public function setTutorialGroup(bool $tutorialGroup = null) {
        $this->tutorialGroup = $tutorialGroup ?? false;
        return $this;
    }
    
    public static function all() : UserViewPermission {
        return (new UserViewPermission())
                ->setActivationDate(true)
                ->setFirstName(true)
                ->setLastName(true)
                ->setMail(true)
                ->setRegDate(true)
                ->setStudentId(true)
                ->setTutorialGroup(true);
    }
    
    public static function none() : UserViewPermission {
        return (new UserViewPermission())
                ->setActivationDate(false)
                ->setFirstName(false)
                ->setLastName(false)
                ->setMail(false)
                ->setRegDate(false)
                ->setStudentId(false)
                ->setTutorialGroup(false);
    }
}