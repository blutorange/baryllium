<?php

namespace Dao;

use phpDocumentor\Reflection\DocBlock\Tag;

/**
 * Methods for interacting with Post objects and the database.
 *
 * @author madgaksha
 */
class TagDao extends AbstractDao {
    protected function getEntityClass(): string {
        return Tag::class;
    }
}