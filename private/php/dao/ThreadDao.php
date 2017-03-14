<?php

namespace Dao;

/**
 * Methods for interacting with Thread objects and the database.
 *
 * @author madgaksha
 */
class ThreadDao extends AbstractDao {
    protected function getEntityName(): string {
        return "Entity\Thread";
    }
}