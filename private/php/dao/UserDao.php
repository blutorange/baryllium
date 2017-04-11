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
    public function findNByFieldOfStudy(FieldOfStudy $fos, int $offset = 0, int $count = null) : array {
        return $this->findNByFieldOfStudyId($fos->getId(), $offset, $count);
    }

    /**
     * @param int $fosId
     * @return User[]
     */
    public function findNByFieldOfStudyId(int $fosId, int $offset = 0, int $count = null) : array {
        $name = $this->getEntityClass();
        return $this->getEm()
                ->createQuery("select u,t,f from $name u join u.tutorialGroup t join t.fieldOfStudy f where f.id=?1")
                ->setFirstResult($offset)
                ->setMaxResults($count ?? CmnCnst::MIN_PAGINABLE_COUNT)
                ->setParameter(1, $fosId)
                ->getResult();
    }
    
    public function countByFieldOfStudy(FieldOfStudy $fos) : int {
        return $this->countByFieldOfStudyId($fos->getId());
    }
    
    public function countByFieldOfStudyId(int $fosId) : int {
        $name = $this->getEntityClass();
        return $this->getEm()
                ->createQuery("select COUNT(u) from $name u join u.tutorialGroup t join t.fieldOfStudy f where f.id=?1")
                ->setParameter(1, $fosId)
                ->getSingleScalarResult();
    }
}