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
use Moose\Entity\User;
use Moose\Util\CmnCnst;

/**
 * Methods for interacting with User objects and the database.
 *
 * @author madgaksha
 */
class UserDao extends AbstractDao {
    protected function getEntityClass(): string {
        return User::class;
    }
    
    public function existsMail(string $mail) : bool {
        return $this->countByField('mail', $mail) > 0;
    }
    
    public function existsStudentId(string $studentId) : bool {
        return $this->countByField('studentId', $studentId) > 0;
    }

    /**
     * @param string $studentId
     * @return User
     */
    public function findOneByStudentId(string $studentId) {
        return $this->findOneByField('studentId', $studentId);
    }
    
    /**
     * @return User The site administrator.
     */
    public function findOneSiteAdmin() {
        return $this->findOneByField('isSiteAdmin', true);
    }
    
    /**
     * @param FieldOfStudy $fos
     * @return User[]
     */
    public function findNByFieldOfStudy(FieldOfStudy $fos, string $orderByField = null, bool $ascending = null, int $offset = 0, int $count = null, array & $search = null) : array {
        return $this->findNByFieldOfStudyId($fos->getId(), $orderByField, $ascending, $offset, $count, $search);
    }

    /**
     * @param int $fosId
     * @param int $offset
     * @param int $count
     * @return User[]
     */
    public function findNByFieldOfStudyId(int $fosId, string $orderByField = null, bool $ascending = null, int $offset = 0, int $count = null, array & $search = null) : array {
        $qb = $this->qb('u')
                ->select('u,t,f')
                ->join('u.tutorialGroup', 't')
                ->join('t.fieldOfStudy', 'f')
                ->setParameter(1, $fosId);
        $whereClause = $this->whereClause($qb, $search, 'u');
        error_log($whereClause);
        if (empty($whereClause)) {
            $qb->where('f.id=?1');
        }
        else {
            $qb->where("f.id=?1 and $whereClause");
        }
        $this->pagingClause($qb, $orderByField, $ascending, $count, $offset, 'u');
        return $qb->getQuery()
            ->getResult();        
    }
       
    public function countByFieldOfStudy(FieldOfStudy $fos, array & $search = null) : int {
        return $this->countByFieldOfStudyId($fos->getId(), $search);
    }
    
    public function countByFieldOfStudyId(int $fosId, array & $search = null) : int {
        $qb = $this->qb('u')
                ->select('count(u)')
                ->join('u.tutorialGroup', 't')
                ->join('t.fieldOfStudy', 'f')
                ->where('f.id=?1')
                ->setParameter(1, $fosId);
        if (!empty($search)) {
            $qb->andWhere($this->whereClause($qb, $search, 'u'));
        }
        return $qb->getQuery()->getSingleScalarResult();
    }
}