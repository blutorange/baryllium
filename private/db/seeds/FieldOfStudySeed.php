<?php

namespace Moose\Seed;

use Moose\Dao\AbstractDao;
use Moose\Entity\Course;
use Moose\Entity\FieldOfStudy;
use Moose\Seed\DormantSeed;
use Moose\Util\MathUtil;
use Moose\Util\RandomUtil;


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
class FieldOfStudySeed extends DormantSeed {
    public function seedMedieninformatik() {
        $this->em()->persist(FieldOfStudy::create()
                ->setDiscipline('Medieninformatik')
                ->setSubDiscipline('Medieninformatik')
                ->setShortName('MI')
        );
    }
    
    public function seedInformationstechnologie() {
        $this->em()->persist(FieldOfStudy::create()
                ->setDiscipline('Informationstechnologie')
                ->setSubDiscipline('Informationstechnologie')
                ->setShortName('IT')
        );
    }
    
    /**
     * 
     * @param int $count
     * @return FieldOfStudy[]
     */
    public function & seedRandom(int $count = 1) : array {
        $count = MathUtil::max(1, $count);
        $fosList = [];
        for ($i = 0; $i < $count; ++$i) {
            $this->em()->persist($fosList[] = FieldOfStudy::create()
                    ->setDiscipline($this->name())
                    ->setSubDiscipline($this->name())
                    ->setShortName(RandomUtil::randomCharSequence(2))
            );
        }
        return $fosList;
    }
    
    public function seedAddRandomCourses(float $connectRate = 1) {
        /* @var $fos FieldOfStudy */
        /* @var $course Course */
        $connectRate = MathUtil::clamp($connectRate, 0, 10);
        $fosList = AbstractDao::fieldOfStudy($this->em())->findAll();
        $courseList = AbstractDao::course($this->em())->findAll();
        $count = \sizeof($fosList)*\sizeof($courseList)*$connectRate;
        for ($i = 0; $i < $count; ++$i) {
            $fosKey = \array_rand($fosList);
            $courseKey = \array_rand($courseList);
            if (!$fosList[$fosKey]->getCourseList()->contains($courseList[$courseKey])) {
                $fosList[$fosKey]->addCourse($courseList[$courseKey]);
            }
        }
    }
    
    public function seedAddDeterministicCourses(float $connectRate = 1) {
        /* @var $fos FieldOfStudy */
        /* @var $course Course */
        $connectRate = MathUtil::clamp($connectRate, 0, 10);
        $fosList = AbstractDao::fieldOfStudy($this->em())->findAll();
        $courseList = AbstractDao::course($this->em())->findAll();
        $count = \sizeof($fosList)*\sizeof($courseList)*$connectRate;
        for ($i = 0; $i < $count; ++$i) {
            $fosKey = $i%\sizeof($fosList);
            $courseKey = $i%\sizeof($courseList);
            if (!$fosList[$fosKey]->getCourseList()->contains($courseList[$courseKey])) {
                $fosList[$fosKey]->addCourse($courseList[$courseKey]);
            }
        }
    }
}
