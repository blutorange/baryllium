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

use Gedmo\Tree\Entity\Repository\ClosureTreeRepository;
use Moose\Entity\Course;
use Moose\Entity\Document;
use Moose\Entity\FieldOfStudy;

/**
 * Methods for interacting with Post objects and the database.
 *
 * @author madgaksha
 */
class DocumentDao extends Dao {
    protected function getEntityClass(): string {
        return Document::class;
    }
    
    /**
     * @return ClosureTreeRepository
     */
    public function getRepository() {
        return parent::getRepository();
    }

    /**
     * @param FieldOfStudy $fieldOfStudy
     * @return Document[]
     */
    public function findAllByRootAndFieldOfStudy(FieldOfStudy $fieldOfStudy) : array {
        return $this->findAllByRootAndFieldOfStudyId($fieldOfStudy->getId());        
    }
    
    /**
     * @param Document The document.
     */
    public function findOneByIdWithCourse(int $documentId) {
        return $query = $this->qbFrom('d')->select('d,c')
                ->join('d.course', 'c')
                ->where('d.id=?1')
                ->setParameter(1, $documentId)
                ->getQuery()
                ->getOneOrNullResult();
    }
    
    /**
     * @param int $fieldOfStudyId
     * @return Document[]
     */
    public function findAllByRootAndFieldOfStudyId(int $fieldOfStudyId ) : array {
        $dql = $this->qb()
                ->select('cl.id')
                ->from(FieldOfStudy::class, 'f')
                ->join('f.courseList', 'cl')
                ->where("f.id = $fieldOfStudyId")
                ->getDQL();
        $query = $this->qbFrom('d')
                ->select('d')
                ->join('d.course', 'c')
                ->where("d.level = 1 and c.id in ($dql)")
                ->getQuery();
        return $query->getResult();
    }    
    
    /**
     * @param Course $course
     * @return Document|null
     */
    public function findOneByRootAndCourse(Course $course) {
        return $this->findOneByRootAndCourseId($course->getId());
    }
    
    /**
     * @param int $courseId
     * @return Document|null
     */
    public function findOneByRootAndCourseId(int $courseId) {
        return $this->qbFrom('d')
                ->select('d')
                ->where("d.course = $courseId and d.parent is null")
                ->getQuery()
                ->getOneOrNullResult();
    }

    /**
     * @return Document[]
     */
    public function findAllByRoot() : array {
        return $this->qbFrom('d')
                ->select('d')
                ->where("d.parent is null")
                ->getQuery()
                ->getResult();
    }

}