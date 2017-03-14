<?php

namespace Dao;

/**
 * Methods for interacting with user objects and the database.
 *
 * @author madgaksha
 */
class ThreadDao extends AbstractDao {
    protected function getEntityName(): string {
        return "Entity\Thread";
    }
}