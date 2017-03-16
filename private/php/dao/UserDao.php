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
        return $this->findAllByField('userName', $username);
    }
    
    public function existsUsername(string $username) : bool {
        return $this->countByField('userName', $username) > 0;
    }
    
    public function existsMail(string $mail) : bool {
        return $this->countByField('mail', $mail) > 0;
    }
}
