<?php

namespace Dao;

/**
 * Methods for interacting with Post objects and the database.
 *
 * @author madgaksha
 */
class ExpireTokenDao extends AbstractDao {
    protected function getEntityName(): string {
        return "Entity\ExpireToken";
    }
    
    /**
      * Finds all tokens that are currently expired. 
     * @return array List of expired tokens.
      */
    public function findAllExpired() : array {
        $name = $this->getEntityName();
        $now = (new \DateTime)->getTimestamp();
        $query = $this->getEm()->createQuery("SELECT e FROM $name e WHERE e.lifeTime <= 0 OR e.creationDate + e.lifeTime <= $now");
        return $query ->getResult() ?? [];
    }
}