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

namespace Moose\Dao;

use Moose\Entity\FieldOfStudy;
use Moose\Entity\TutorialGroup;

/**
 * Methods for interacting with TutorialGroup objects and the database.
 *
 * @author madgaksha
 */
class TutorialGroupDao extends AbstractDao {
    protected function getEntityClass(): string {
        return TutorialGroup::class;
    }
    
    public function existsByName($studyGroupName) : bool {
        return $this->findOneByField('name', $studyGroupName) != null;
    }

    public function findMatchingSelf(TutorialGroup $tutorialGroup) {
        return $this->findByAll($tutorialGroup->getUniversity()->getId(), $tutorialGroup->getYear(), $tutorialGroup->getIndex(), $tutorialGroup->getFieldOfStudy());
    }

    public function findByAll(int $university, int $year, int $index, FieldOfStudy $fieldOfStudy) {
        return $this->findOneByMultipleFields([
            'university' => $university,
            'year' => $year,
            'index' => $index,
            "fieldOfStudy" => $fieldOfStudy
        ]);
    }

    public function findAllByFieldOfStudy(FieldOfStudy $fos) : array {
        return $this->findAllByFieldOfStudyId($fos->getId());
    }
    
    /**
     * @param int $fosId
     * @return TutorialGroup[]
     */
    public function findAllByFieldOfStudyId(int $fosId) : array {
        return $this->findAllByField('fieldOfStudy', $fosId);
    }
}