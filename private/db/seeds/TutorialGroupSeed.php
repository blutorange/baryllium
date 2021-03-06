<?php

namespace Moose\Seed;

use Moose\Dao\Dao;
use Moose\Entity\TutorialGroup;
use Moose\Seed\DormantSeed;

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
 * @author madgaksha
 */
class TutorialGroupSeed extends DormantSeed {   
    public function seedDeterministic(int $count = 10) {
        $fosList = Dao::fieldOfStudy($this->em())->findAll();
        if (\sizeof($fosList) < 1) {
            \error_log('No fields of study present, creating some.');
            $fosList = (new FieldOfStudySeed($this->em()))->seedDeterministic(1);
        }
        $uniList = Dao::university($this->em())->findAll();
        if (sizeof($uniList) < 1) {
            throw new \LogicException('No universitities present, they should have been created during setup.');
        }
        $tutList = [];
        for ($i = 0; $i < $count; ++$i) {
            $this->em()->persist($tutList[] = TutorialGroup::create()
                    ->setIndex($i % 10)
                    ->setUniversity($uniList[$i%\sizeof($uniList)])
                    ->setYear(2000+(int)($i/10))
                    ->setFieldOfStudy($fosList[$i%\sizeof($fosList)])
            );
        }
    }
    
    /**
     * @param int $count
     * @return TutorialGroup[]
     */
    public function seedRandom(int $count = 10) : array {
        $fosList = Dao::fieldOfStudy($this->em())->findAll();
        if (sizeof($fosList) < 1) {
            \error_log('No fields of study present, creating some.');
            $fosList = (new FieldOfStudySeed($this->em()))->seedRandom(1);
        }
        $uniList = Dao::university($this->em())->findAll();
        if (sizeof($uniList) < 1) {
            throw new \LogicException('No universitities present, they should have been created during setup.');
        }
        $tutList = [];
        for ($i = 0; $i < $count; ++$i) {
            $this->em()->persist($tutList[] = TutorialGroup::create()
                    ->setIndex(rand(1, 9))
                    ->setUniversity($uniList[\array_rand($uniList)])
                    ->setYear(rand(2000, 2020))
                    ->setFieldOfStudy($fosList[\array_rand($fosList)])
            );
        }
        return $tutList;
    }
}