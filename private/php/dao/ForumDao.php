<?php

namespace Dao;

use Entity\Forum;

/**
 * Methods for interacting with user objects and the database.
 *
 * @author madgaksha
 */
class ForumDao extends AbstractDao {
    protected function getEntityClass(): string {
        return Forum::class;
    }
}