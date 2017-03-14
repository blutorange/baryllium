<?php

namespace Dao;

/**
 * Methods for interacting with User objects and the database.
 *
 * @author madgaksha
 */
class UserDao extends AbstractDao {
    protected function getEntityName(): string {
        return "Entity\User";
    }
    
    public function findAllByUsername(string $username) : array {
        return $this->findAllByField('username', $username);
    }
}