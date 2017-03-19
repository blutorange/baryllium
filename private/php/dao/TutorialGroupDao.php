<?php

namespace Dao;

use Entity\TutorialGroup;

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
}