<?php

namespace Dao;

/**
 * Methods for interacting with Post objects and the database.
 *
 * @author madgaksha
 */
class TagDao extends AbstractDao {
    protected function getEntityName(): string {
        return "Entity\Tag";
    }
}