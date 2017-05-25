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

namespace Moose\ViewModel;

use DateTime;
use Moose\Entity\TutorialGroup;
use Moose\Entity\User;

/**
 * Description of UserView
 *
 * @author madgaksha
 */
class UserPermissionFacet {
    
    private $id;
    private $avatar;
    private $firstName;
    private $lastName;
    private $tutorialGroup;
    private $studentId;
    private $activationDate;
    private $regDate;
    
    public function __construct(User $user, User $currentUser = null) {
        $this->id = $user->getId();
        $this->avatar = $user->getAvatar();
        $this->filter($user, $currentUser);
    }
    
    private function filter(User $user, User $currentUser = null) {
        $vp = $user->getUserOption();
        $same = $currentUser !== null && $user->isSame($currentUser);
        $this->activationDate = $same || $vp->getIsPublicActivationDate() ? $user->getActivationDate() : null;
        $this->regDate = $same || $vp->getIsPublicRegistrationDate() ? $user->getRegDate() : null;
        $this->firstName = $same || $vp->getIsPublicFirstName() ? $user->getFirstName() : null;
        $this->lastName = $same || $vp->getIsPublicLastName() ? $user->getLastName() : null;
        $this->studentId = $same || $vp->getIsPublicStudentId() ? $user->getStudentId() : null;
        $this->mail = $same || $vp->getIsPublicMail() ? $user->getMail() : null;
        $this->tutorialGroup = $same || $vp->getIsPublicTutorialGroup() ? $user->getTutorialGroup() : null;
    }

    /** @return int */
    public function getId() {
        return $this->id;
    }

    /** @return string|null */
    public function getFirstName() {
        return $this->firstName;
    }

    /** @return string|null */
    public function getLastName() {
        return $this->lastName;
    }

    /** @return TutorialGroup|null */
    public function getTutorialGroup() {
        return $this->tutorialGroup;
    }

    /** @return string|null */
    public function getStudentId() {
        return $this->studentId;
    }

    /** @return DateTime|null */
    public function getActivationDate() {
        return $this->activationDate;
    }

    /** @return DateTime|null */
    public function getRegistrationDate() {
        return $this->regDate;
    }
    
    public function getAvatar() {
        return $this->avatar;
    }
}
