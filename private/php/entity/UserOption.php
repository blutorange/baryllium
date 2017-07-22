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

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Moose\Util\ReflectionCache;
use Moose\Util\UiUtil;
use ReflectionException;
use const MB_CASE_TITLE;
use function mb_strtolower;

/**
 * Stores info on what data is visible to other users.
 *
 * @Entity
 * @Table(name="useroption")
 * @author madgaksha
 */
class UserOption extends AbstractEntity {
    
    const FIELDS = [
        'isPublicFirstName',
        'isPublicLastName',
        'isPublicStudentId',
        'isPublicMail',
        'isPublicTutorialGroup',
        'isPublicRegistrationDate',
        'isPublicActivationDate',
        'preferredDiningHall'
    ];
    
    const FIELDS_PUBLIC_ACCESS = [
        'firstName' => 'isPublicFirstName',
        'lastName' => 'isPublicLastName',
        'studentId' => 'isPublicStudentId',
        'mail' => 'isPublicMail',
        'tutorialGroup' => 'isPublicTutorialGroup',
        'regDate' => 'isPublicRegistrationDate',
        'activationDate' => 'isPublicActivationDate'
    ];
       
    /**
     * @Column(name="pub_student_id", type="boolean")
     * @var bool
     */
    protected $isPublicStudentId;

    /**
     * @Column(name="pub_first_name", type="boolean")
     * @var bool
     */
    protected $isPublicFirstName;
    
    /**
     * @Column(name="pub_last_name", type="boolean")
     * @var bool
     */
    protected $isPublicLastName;
    
    /**
     * @Column(name="pub_reg_date", type="boolean")
     * @var bool
     */
    protected $isPublicRegistrationDate;

    /**
     * @Column(name="pub_act_date", type="boolean")
     * @var bool
     */
    protected $isPublicActivationDate;

    /**
     * @Column(name="pub_mail", type="boolean")
     * @var bool The user's alternative mail address (not from the BA).
     */
    protected $isPublicMail;
    
    /**
     * @Column(name="pref_dhall", length=128, nullable=true)
     * @var string Preferred dining hall.
     */
    protected $preferredDiningHall;
    
    /**
     * @Column(name="pub_tutgroup", type="boolean")
     * @var bool
     */
    protected $isPublicTutorialGroup;

    public function __construct() {
        $this->isPublicStudentId = false;
        $this->isPublicMail = false;
        $this->isPublicFirstName = true;
        $this->isPublicLastName = true;
        $this->isPublicTutorialGroup = true;
        $this->isPublicRegistrationDate = true;
        $this->isPublicActivationDate = true;
    }
    
    public function getIsPublicStudentId() : bool {
        return $this->isPublicStudentId ?? false;
    }

    public function getIsPublicFirstName() : bool {
        return $this->isPublicFirstName ?? false;
    }

    public function getIsPublicLastName() : bool {
        return $this->isPublicLastName ?? false;
    }

    public function getIsPublicRegistrationDate() : bool {
        return $this->isPublicRegistrationDate ?? false;
    }

    public function getIsPublicActivationDate() : bool {
        return $this->isPublicActivationDate ?? false;
    }

    public function getIsPublicMail() : bool {
        return $this->isPublicMail ?? false;
    }

    public function getIsPublicTutorialGroup() : bool {
        return $this->isPublicTutorialGroup ?? false;
    }
    
    /**
     * @return string
     */
    public function getPreferredDiningHall() {
        return $this->preferredDiningHall;
    }

    public function setPreferredDiningHall(string $preferredDiningHall = null) : UserOption {
        $this->preferredDiningHall = $preferredDiningHall;
        return $this;
    }

    /**
     * @param bool|string $isPublicStudentId
     * @return $this For chaining.
     */
    public function setIsPublicStudentId($isPublicStudentId = null) {
        $this->isPublicStudentId = $this->asBool($isPublicStudentId);
        return $this;
    }

    public function setIsPublicFirstName($isPublicFirstName = null) {
        $this->isPublicFirstName = $this->asBool($isPublicFirstName);
        return $this;
    }

    public function setIsPublicLastName($isPublicLastName = null) {
        $this->isPublicLastName = $this->asBool($isPublicLastName);
        return $this;
    }

    public function setIsPublicRegistrationDate($isPublicRegDate = null) {
        $this->isPublicRegistrationDate = $this->asBool($isPublicRegDate);
        return $this;
    }

    public function setIsPublicActivationDate($isPublicActivationDate = null) {
        $this->isPublicActivationDate = $this->asBool($isPublicActivationDate);
        return $this;
    }

    public function setIsPublicMail($isPublicMail = null) {
        $this->isPublicMail = $this->asBool($isPublicMail);
        return $this;
    }

    public function setIsPublicTutorialGroup($isPublicTutorialGroup = null) {
        $this->isPublicTutorialGroup = $this->asBool($isPublicTutorialGroup);
        return $this;
    }
    
    /**
     * @param string $name
     * @param mixed|null $value
     * @throws ReflectionException
     */
    public function setOption(string $name, $value = null) {
        $methodName = "set" . UiUtil::firstToUpcase($name);
        ReflectionCache::getMethod(\get_class($this), $methodName)->invoke($this, $value);
        return $this;
    }
    
    public function hasOption(string $name) : bool {
        try {
            return \key_exists($name, ReflectionCache::getProperties(\get_class($this)));
        }
        catch (ReflectionException $ignored) {
            return false;
        }
    }
    
    /**
     * @param string $name
     * @return mixed
     * @throws ReflectionException
     */
    public function getOption(string $name) {
        $methodName = "get" . UiUtil::firstToUpcase($name, MB_CASE_TITLE);
        return ReflectionCache::getMethod(\get_class($this), $methodName)->invoke($this);
    }
    
    public static function create() : UserOption {
        return new UserOption();
    }
    
    public static function defaultConfig() : UserOption {
        return UserOption::create();
    }

    public function asBool($value = null) {
        if ($value === null) {
            return false;
        }
        if (\is_bool($value)) {
            return $value;
        }
        $string = mb_strtolower((string)$value);
        if ($string === "true" || $string === "1") {
            return true;            
        }
        return false;
    }
}