<?php

namespace Dao;

use Entity\Course;

/**
 * Methods for interacting with Course objects and the database.
 *
 * @author madgaksha
 */
class CourseDao extends AbstractDao {
    protected function getEntityClass(): string {
        return Course::class;
    }
}