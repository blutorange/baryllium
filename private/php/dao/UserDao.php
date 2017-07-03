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
use Moose\Entity\FieldOfStudy;
use Moose\Entity\TutorialGroup;
use Moose\Entity\User;
use Moose\Entity\UserOption;
use Moose\Util\DebugUtil;

/**
 * Methods for interacting with User objects and the database.
 *
 * @author madgaksha
 */
class UserDao extends Dao {
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
    public function findNByFieldOfStudy(FieldOfStudy $fos, string $orderByField = null, bool $ascending = null, int $offset = 0, int $count = null, array & $search = null, User $currentUser = null) : array {
        return $this->findNByFieldOfStudyId($fos->getId(), $orderByField, $ascending, $offset, $count, $search, $currentUser);
    }

    /**
     * @param int $fosId
     * @param int $offset
     * @param int $count
     * @return User[]
     */
    public function findNByFieldOfStudyId(int $fosId, string $orderByField = null,
            bool $ascending = null, int $offset = 0, int $count = null,
            array & $search = null, User $currentUser = null) : array {
        $qb = $this->qbFrom('u')
                ->select('u,t,f')
                ->join('u.tutorialGroup', 't')
                ->join('t.fieldOfStudy', 'f')
                ->setParameter(1, $fosId);
        $whereClause = $this->whereClause($qb, $search, 'u');       
        if ($currentUser !== null) {
            $this->filterClause($qb, $orderByField, $search, $currentUser->getId(), 'u', 'uo');
        }
        if (empty($whereClause)) {
            $qb->where('f.id=?1');
        }
        else {
            $qb->where("f.id=?1 and $whereClause");
        }
        $this->pagingClause($qb, $orderByField, $ascending, $count, $offset, 'u');
        DebugUtil::log($qb->getDQL());
        return $qb->getQuery()
            ->getResult();        
    }
       
    public function countByFieldOfStudy(FieldOfStudy $fos, string $orderByField = null, array & $search = null, User $currentUser = null) : int {
        return $this->countByFieldOfStudyId($fos->getId(), $orderByField, $search, $currentUser);
    }
    
    public function countByFieldOfStudyId(int $fosId, string $orderByField = null, array & $search = null, User $currentUser = null) : int {
        $qb = $this->qbFrom('u')
                ->select('count(u)')
                ->join('u.tutorialGroup', 't')
                ->join('t.fieldOfStudy', 'f')
                ->where('f.id=?1')
                ->setParameter(1, $fosId);
        if ($currentUser !== null) {
            $this->filterClause($qb, $orderByField, $search, $currentUser->getId(), 'u', 'uo');
        }        
        if (!empty($search)) {
            $qb->andWhere($this->whereClause($qb, $search, 'u'));
        }
        return $qb->getQuery()->getSingleScalarResult();
    }
    
    /** @return User[] */
    public function findAllActiveWithCampusDualLogin(array $requestedFields = null, bool $partial = null) : array {
        return $this->selectClause($this->qbFrom('u'), $requestedFields, $partial, 'u')
                ->where('u.passwordCampusDual is not null and u.studentId is not null and u.isActivated = true')
                ->getQuery()
                ->getResult();
    }
    
    /** @return User|null */
    public function findOneActiveWithCampusDualLoginForTutorialGroup(TutorialGroup $tutorialGroup) {
        return $this->qbFrom('u')
                ->select('u,t')
                ->join('u.tutorialGroup', 't')
                ->where('u.passwordCampusDual is not null and u.studentId is not null and u.isActivated = true and u.tutorialGroup = ?1')
                ->setParameter(1, $tutorialGroup->getId())
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();
    }

    private function filterClause(QueryBuilder $qb, string $orderByField = null,
            array & $search = null, int $currentUserId = -1,
            string $alias = 'u', string $aliasJoin = 'uo', bool $selectJoin = false) {
        $whereClause = [];
        foreach ($search ?? [] as $field => $options) {
            $uoname = UserOption::FIELDS_PUBLIC_ACCESS[$field] ?? false;
            if ($uoname) {
                $whereClause []= "$aliasJoin.$uoname=true";
            }
        }
        if ($orderByField !== null && (UserOption::FIELDS_PUBLIC_ACCESS[$orderByField] ?? false)) {
            $uoname = UserOption::FIELDS_PUBLIC_ACCESS[$orderByField];
            $whereClause []= "$aliasJoin.$uoname=true";
        }
        if (!empty($whereClause)) {
            $qb->join("$alias.userOption", $aliasJoin, Join::WITH, \implode(' and ', $whereClause) . " or $alias.id=:cuid");
            $qb->setParameter('cuid', $currentUserId);
            if ($selectJoin) {
                $qb->addSelect($aliasJoin);
            }
        }
        return $qb;
    }
}