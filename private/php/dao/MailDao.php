<?php

namespace Dao;

/**
 * Methods for interacting with user objects and the database.
 *
 * @author david-dd
 */
class MailDao extends AbstractDao {
    protected function getEntityName(): string {
        return "Entity\Mail";
    }
}