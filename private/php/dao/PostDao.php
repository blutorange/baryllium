<?php

namespace Dao;

use Entity\Post;

/**
 * Methods for interacting with Post objects and the database.
 *
 * @author madgaksha
 */
class PostDao extends AbstractDao {
    protected function getEntityClass(): string {
        return Post::class;
    }
}