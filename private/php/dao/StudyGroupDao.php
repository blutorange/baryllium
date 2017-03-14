<?php

namespace Dao;

/**
 * Methods for interacting with StudyGroup objects and the database.
 *
 * @author madgaksha
 */
class StudyGroupDao extends AbstractDao {
    protected function getEntityName(): string {
        return "Entity\StudyGroup";
    }
    
    public function existsByName($studyGroupName) : bool {
        return $this->findOneByField('name', $studyGroupName) != null;
    }
}