<?php

namespace Dao;

use Entity\Mail;

/**
 * Methods for interacting with user objects and the database.
 *
 * @author david-dd
 */
class MailDao extends AbstractDao {
    protected function getEntityClass(): string {
        return Mail::class;
    }
}