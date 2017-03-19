<?php

namespace Dao;

use Entity\Thread;

/**
 * Methods for interacting with Thread objects and the database.
 *
 * @author madgaksha
 */
class ThreadDao extends AbstractDao {
    protected function getEntityClass(): string {
        return Thread::class;
    }
}