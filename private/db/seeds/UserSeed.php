<?php

namespace Moose\Seed;

use DateTime;
use Doctrine\DBAL\Types\ProtectedString;
use Moose\Dao\AbstractDao;
use Moose\Entity\User;
use Moose\Seed\DormantSeed;
use Moose\Util\MathUtil;
use Moose\Util\RandomUtil;
use Nubs\RandomNameGenerator\Vgng;
use function mb_strpos;
use function mb_substr;


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

/**
 * <pre>
 * DormantSeed::grow([
 *   'User' => [
 *       'RandomUsers' => [20, 'myPassword']
 *   ]
 * ]);
 * </pre>
 * <pre>
 * DormantSeed::grow([
 *   'Admin',
 *   'RandomUsers'
 * ]);
 * </pre>
 *
 * @author madgaksha
 */
class UserSeed extends DormantSeed {
    /**
     * @return User
     */
    public function seedAdmin() : User {
        $this->em()->persist($admin = User::create()
                ->setFirstName('Andre')->setLastName('Wachsmuth')
                ->setRegDate($this->time(2017,5,22,11,53,42))
                ->setMail('s1234567@ba-dresden.de')
                ->setActivationDate($this->time(2017,5,22,11,53,42))
                ->setIsSiteAdmin(true)
                ->setPassword(new ProtectedString('sadmin'))
                ->generateIdenticon('Andre Wachsmuth')
        );
        return $admin;
    }

    /**
     * 
     * @param int $count
     * @param bool $addToTutorialGroup
     * @param string $passPrefix
     * @param int $passIndexStart
     * @return User[]
     */
    public function seedRandom(int $count = 1, string $passPrefix = 'password', bool $addToTutorialGroup = true, int $passIndexStart = -1) : array {
        $count = MathUtil::max(1, $count);
        $lastYear = intval((new DateTime())->format('Y'))-1;
        $nameGenerator = new Vgng();
        $userList = [];
        $tutList = $addToTutorialGroup ? AbstractDao::tutorialGroup($this->em())->findAll() : [];
        if ($addToTutorialGroup && \sizeof($tutList) === 0) {
            $tutList = (new TutorialGroupSeed(($this->em())))->seedRandom(10);
        }
        for ($i = 0; $i < $count; ++$i) {
            $reg = $this->time(rand(2000, $lastYear), rand(1, 12), rand(1,28), rand(0,23), rand(0,59), rand(0,59));
            $act = clone $reg;
            $act = rand(1,2) === 1 ? $act->modify('+1 day') : null;
            $name = $nameGenerator->getName();
            $pos = mb_strpos($name, ' ');
            if ($pos < 2) {
                $firstName = $name;
                $lastName = $nameGenerator->getName();
            }
            else {
                $firstName = mb_substr($name, 0, $pos);
                $lastName = mb_substr($name, $pos+1);
            }
            $pass = $passIndexStart >= 0 ? ($passPrefix . ($i+$passIndexStart)) : $passPrefix;
            $tutGroup = \sizeof($tutList) > 0 ? $tutList[\array_rand($tutList)] : null;
            $this->em()->persist($userList[] = User::create()
                ->setFirstName($firstName)->setLastName($lastName)
                ->setRegDate($reg)
                ->setActivationDate($act)
                ->setIsActivated($act !== null)
                ->setStudentId(RandomUtil::randomCharSequence(7, RandomUtil::CHAR_SEQUENCE_DIGITS))
                ->setIsSiteAdmin(false)->setIsFieldOfStudyAdmin(false)
                ->setPassword(new ProtectedString($pass))
                ->generateIdenticon($name)
                ->setTutorialGroup($tutGroup)
            );
        }
        return $userList;
    }
    
    public function seedDeterministic(int $count = 1, string $passPrefix = 'password', bool $addToTutorialGroup = true, int $passIndexStart = -1) : array {
        $count = MathUtil::max(1, $count);
        $userList = [];
        $tutList = $addToTutorialGroup ? AbstractDao::tutorialGroup($this->em())->findAll() : [];
        if ($addToTutorialGroup && \sizeof($tutList) === 0) {
            $tutList = (new TutorialGroupSeed(($this->em())))->seedDeterministic();
        }
        for ($i = 0; $i < $count; ++$i) {
            $reg = $this->time(2000+$i%20, 1+$i%12, 1+$i%28, $i%23, $i%59, $i%59);
            $act = clone $reg;
            $act = $i%2 === 1 ? $act->modify('+1 day') : null;
            $pass = $passIndexStart >= 0 ? ($passPrefix . ($i+$passIndexStart)) : $passPrefix;
            $tutGroup = \sizeof($tutList) > 0 ? $tutList[$i%\sizeof($tutList)] : null;
            $this->em()->persist($userList[] = User::create()
                ->setFirstName("FirstName$i")
                ->setLastName("LastName$i")
                ->setRegDate($reg)
                ->setActivationDate($act)
                ->setIsActivated($act !== null)
                ->setStudentId(str_pad((string)$i, 7, '0'))
                ->setIsSiteAdmin(false)->setIsFieldOfStudyAdmin(false)
                ->setPassword(new ProtectedString($pass))
                ->generateIdenticon($name)
                ->setTutorialGroup($tutGroup)
            );
        }
        return $userList;
    }

    public function seedRandomTutorialGroup() {
        /* @var $userList User[] */
        $tutList = AbstractDao::tutorialGroup($this->em())->findAll();
        $userList = AbstractDao::tutorialGroup($this->em())->findAll();
        if (\sizeof($tutList) === 0) {
            $tutList = (new TutorialGroupSeed(($this->em())))->seedRandom(10);
        }
        $count = \sizeof($userList);
        for ($i = 0; $i < $count; ++$i) {
            $userList[$i]->setTutorialGroup($tutList[array_rand($tutList)]);
        }
    }
}