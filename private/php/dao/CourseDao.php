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

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Moose\Entity\Course;
use Moose\Entity\FieldOfStudy;
use Moose\Entity\Forum;

/**
 * Methods for interacting with Course objects and the database.
 *
 * @author madgaksha
 */
class CourseDao extends Dao {
    protected function getEntityClass(): string {
        return Course::class;
    }

    /**
     * @param FieldOfStudy $fieldOfStudy
     * @param string $courseName
     * @return Course Or null when no course was found.
     */
    public function findOneByFieldOfStudyWithName(FieldOfStudy $fieldOfStudy, string $courseName) {
        $fieldOfStudyList = $this->oneByFieldOfStudyWithName($fieldOfStudy, $courseName)
                ->select('f,c')
                ->getQuery()
                ->getResult();
        if (sizeof($fieldOfStudyList) < 1) {
            return null;
        }
        $courseList = $fieldOfStudyList[0]->getCourseList();
        if ($courseList->count() < 1) {
            return null;
        }
        return $courseList->get(0);
    }
    
    /**
     * @param FieldOfStudy $fieldOfStudy
     * @param string $courseName
     * @return bool Whether such an entity exists.
     */
    public function existsByFieldOfStudyWithName(FieldOfStudy $fieldOfStudy, string $courseName) : bool {
        return 0 < $this
                ->oneByFieldOfStudyWithName($fieldOfStudy, $courseName)
                ->select('count(f)')
                ->getQuery()
                ->getSingleScalarResult();
    }

    private function oneByFieldOfStudyWithName(FieldOfStudy $fieldOfStudy, string $courseName) : QueryBuilder {
        return $this->getEm()
            ->createQueryBuilder()
            ->from(FieldOfStudy::class, 'f')
            ->innerJoin('f.courseList', 'c', Join::WITH, 'f.id = ?1 AND c.name = ?2')
            ->setParameter(1, $fieldOfStudy->getId())
            ->setParameter(2, $courseName);
    }

    /**
     * @param int $forumId
     * @return Forum Or null when not found.
     */
    public function findOneByForumId(int $forumId) {
        return $this->findOneByField('forum', $forumId);
    }
    
    /**
     * @param Forum $forum
     * @return Forum Or null when not found.
     */
    public function findOneByForum(Forum $forum) {
        return $this->findOneByForumId($forum->getId());
    }

}