<?php

/* Note: This license has also been called the "New BSD License" or "Modified
 * BSD License". See also the 2-clause BSD License.
 * 
 * Copyright 2015 The Moose Team
 * 
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 * 
 * 1. Redistributions of source code must retain the above copyright notice,
 * this list of conditions and the following disclaimer.
 * 
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 * this list of conditions and the following disclaimer in the documentation
 * and/or other materials provided with the distribution.
 * 
 * 3. Neither the name of the copyright holder nor the names of its contributors
 * may be used to endorse or promote products derived from this software without
 * specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

namespace Moose\Dao;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Moose\Context\Context;
use Moose\Entity\AbstractEntity;
use Moose\Util\DebugUtil;
use Moose\Util\PlaceholderTranslator;
use Moose\ViewModel\Message;
use Moose\ViewModel\MessageInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Throwable;

/**
 * Bridge between the database and entities.
 *
 * @author madgaksha
 */
abstract class Dao {
    private $em;
    static $VALIDATOR;
    private $queue;

    public function __construct(EntityManager $em) {
        $this->em = $em;
    }
    
    public function getQueue() : ArrayCollection {
        if ($this->queue === null) {
            $this->queue = new ArrayCollection();
        }
        return $this->queue;
    }


    /**
     * @return ObjectRepository
     */
    public function getRepository() {
        return $this->getEm()->getRepository($this->getEntityClass());
    }
    
    public final function getEm() : EntityManager {
        return $this->em;                
    }
    
    /** @return AbstractEntity */
    public final function findOneById($id, array $associations = null) {
        if (empty($associations)) {
            return $this->getEm()->find($this->getEntityClass(), $id);            
        }
        $query = $this->qbFrom('e')
                ->select('e')
                ->where("e.id=?1")
                ->setParameter(1, $id)
                ->getQuery();
        $this->fetchMode($query, $associations);
        return $query->getOneOrNullResult();
    }
    
    protected function fetchMode(Query $query, array $associations) {
        foreach ($associations as $class => $value) {
            if (\is_integer($class)) {
                $class = $this->getEntityClass();
            }
            if (\is_array($value)) {
                $assocName = $value[0];
                $fetchMode = $value[1] ?? ClassMetadata::FETCH_EAGER;
            }
            else {
                $assocName = $value;
                $fetchMode = ClassMetadata::FETCH_EAGER;
            }
            $query->setFetchMode($class, $assocName, $fetchMode);
        }
    }
    
    public final function findOneByClassAndId($class, $id, array $associations = null) {
        $repo = $this->getEm()->getRepository($class);
        if (empty($associations)) {
            return $repo->find($id);
        }
        $query = $repo->createQueryBuilder('e')
                ->where("e.id=?1")
                ->setParameter(1, $id)
                ->getQuery();
        $this->fetchMode($query, $associations);
        return $query->getOneOrNullResult();

    }
    
    public final function findAll() : array {
        $list = $this->getRepository()->findAll();
        return $list ?? [];
    }
    
    public final function findN($orderByField = null, bool $ascending = null,
        int $limit = null, int $offset = null, array & $search = null) : array {
        $qb = $this->qbFrom('e');
        if (!empty($search)) {
            $qb->where($this->whereClause($qb, $search));
        }
        return $this->pagingClause($qb, $orderByField, $ascending, $limit, $offset)
                ->select('e')
                ->getQuery()
                ->getResult();
    }
    
    protected function pagingClause(QueryBuilder $qb, $orderByField = null, bool $ascending = null,
            int $limit = null, int $offset = null, $alias = null) : QueryBuilder {
        $alias = $alias ?? 'e';
        if ($orderByField !== null) {
            $qb->orderBy("$alias.$orderByField", $ascending ? 'ASC' : 'DESC');
        }
        if ($limit >= 0) {
            $qb->setMaxResults($limit)->setFirstResult($offset);
        }
        return $qb;
    }
    
    /**
     * @param QueryBuilder $qb Current query builder.
     * @param string[] $fields Fields to retrieve.
     * @param bool $partial Whether the entity is represented as a partial objects or as an array.
     * @param string $alias Alias for the entity.
     * @return QueryBuilder The query builder for chaining.
     */
    protected function selectClause(QueryBuilder $qb, array $fields = null, bool $partial = null, $alias = 'e') : QueryBuilder {
        if (empty($fields)) {
            $qb->select($alias);
        }
        else if ($partial) {
            $qb->select("partial $alias.{" . \implode(',', $fields) . '}');
        }
        else {
            $qb->select(\implode(',', \array_map(function(string $value, $index) use ($alias) {
                if (\is_numeric($index)) {
                    return "$alias.$value";
                }
                else {
                    return "$value($alias.$index) as $index";
                }
            }, $fields, \array_keys($fields))));
        }
        return $qb;
    }
    
    protected function whereClause(QueryBuilder $qb, array & $search = null, string $alias = null) : string {
        if (empty($search)) {
            return '';
        }
        $alias = $alias ?? 'e';
        $whereClause = [];
        foreach ($search as $field => $options) {
            $value = $options['val'];
            $operator = $options['op'];
            $whereClause []= "$alias.$field $operator :$field";
            $qb->setParameter($field, $operator === 'like' ? "%$value%" : $value);
        }
        return \implode(' and ', $whereClause);
    }

    /**
     * @return AbstractEntity Any entity of the DAO's type if there is one.
     */
    public final function findOne() {
        return $this->qbFrom()->select('e')->setMaxResults(1)->getQuery()->getOneOrNullResult();
    }
    
    /**
     * Returns the number of objects found in the database. For performance,
     * this selects only the id attribute.
     * @param string $fieldName Name of the field (not database column) to check. <b>MUST NOT BE UNTRUSTED INPUT.</b>
     * @param string $fieldValue Value to match.
     * @return bool The number of entities.
     */
    public final function countByField(string $fieldName, string $fieldValue) : int {
        $name = $this->getEntityClass();
        $query = $this->getEm()->createQuery("SELECT COUNT(u) FROM $name u WHERE u.$fieldName = ?1");
        $query->setParameter(1, $fieldValue);
        return $query->getSingleScalarResult();
    }
    
    public final function countAll(array & $search = null) : int {
        $qb = $this->qbFrom()->select('count(e)');
        if (!empty($search)) {
            $qb->where($this->whereClause($qb, $search));
        }
        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param string $fieldName
     * @param mixed $value
     * @param string $orderByField
     * @param bool $ascending
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public final function findAllByField(string $fieldName, $value,
            string $orderByField = null, bool $ascending = false,
            int $limit = null, int $offset = null) : array {
        return $this->findAllByMultipleFields([$fieldName => $value],
                        $orderByField, $ascending, $limit, $offset) ?? [];
    }

    /**
     * @param array $fieldToValueMap
     * @param string|null $orderByField
     * @param bool $ascending
     * @param int|null $limit
     * @param int|null $offset
     * @return array
     */
    public function findAllByMultipleFields(array $fieldToValueMap,
                                            string $orderByField = null, bool $ascending = false,
                                            int $limit = null, int $offset = null) : array {
        $orderBy = $orderByField !== null ? [$orderByField => $ascending ? 'ASC'
                : 'DESC'] : [];
        $list = $this->getRepository()->findBy($fieldToValueMap, $orderBy,
                $limit, $offset);
        return $list ?? [];
    }

    /**
     * @param string $fieldName
     * @param $value
     * @return null|object
     */
    public final function findOneByField(string $fieldName, $value) {
        $critera = [];
        $critera[$fieldName] = $value;
        return $this->getRepository()->findOneBy($critera);
    }

    /**
     * @param array $fieldToValueMap
     * @return null|object
     */
    public function findOneByMultipleFields(array $fieldToValueMap) {
        return $this->getRepository()->findOneBy($fieldToValueMap);
    }
    
    /**
     * Removes all entities from the database.
     * @param AbstractEntity[] $entities
     */
    public function removeAll(array $entities) {
        foreach ($entities as $entity) {
            $this->remove($entity);
        }
    }
    
    /**
     * @param AbstractEntity $entity The entity to be removed from the database.
     */
    public function remove(AbstractEntity $entity) {
        $this->getEm()->remove($entity);
    }
    
    public function removeById(int $id) {
        $this->getEm()
                ->createQueryBuilder()
                ->delete($this->getEntityClass(), 'e')
                ->where('e.id = ?1')
                ->setParameter(1, $id)
                ->getQuery()
                ->execute();
    }
    
    /**
     * @param AbstractEntity $entity The entity to be persisted.
     * @param PlaceholderTranslator $translator Translator for generating error messages when validation fails.
     * @param bool $flush Whether to flush the entity manager. Should be false normally, the entity manager is flushed once at the end of each request.
     * @param array $messages Optional array of messages to be filled.
     * @return MessageInterface[] Array with one message for each validation error. When this array is empty, persist was successful.
     */
    public function persist(AbstractEntity $entity, PlaceholderTranslator $translator, bool $flush = false, array & $messages = []) : array {
        $res = $this->validateEntity($entity, $translator, $messages);
        if ($res) {
            $this->doPersist($entity, $translator, $flush, $messages);
        }    
        else if (\sizeof($messages) === 0) {
            \array_push($messages, Message::dangerI18n('error.validation', 'error.validation.unknown', $translator));
        }
        return $messages;
    }
       
    private function doPersist(AbstractEntity $entity,
            PlaceholderTranslator $translator, bool $flush, array & $arr) {
        try {
            $this->getEm()->persist($entity);
            if ($flush) {
                $this->getEm()->flush($entity);
            }
        }
        catch (Throwable $e) {
            Context::getInstance()->getLogger()->log("Failed to persist entity: " . $e);
            \array_push($arr,
                    Message::dangerI18n('error.database', $e->getMessage(),
                            $translator));
        }
    }

    /**
     * Puts an entity into the queue. You can call {@link AbstractDao::persistQueue} to
     * validate and persists all entities in the queue later. Note that Doctrine caches
     * entities internally as well, you need to call {@link EntityManager::flush} to write
     * all changes to the database. This method may be useful when you do not want to check
     * the result of {@link AbstractDao::persist} all the time for validation errors.
     * @param AbstractEntity $entity
     * @return Dao
     */
    public function queue(AbstractEntity $entity) : Dao {
        $this->getQueue()->add($entity);
        return $this;
    }
    
    /**
     * @param PlaceholderTranslator $translator
     * @param bool $flush
     * @return MessageInterface[] A list of Messages for each constraint violation.
     */
    public function persistQueue(PlaceholderTranslator $translator, bool $flush = false, array & $messages = null) : array {
        $messages = $messages ?? [];
        $queue = $this->getQueue();
        // Validate all entities, do not write anything to the database
        // when there are any violations.
        foreach ($queue as $entity) {
            $this->validateEntity($entity, $translator, $messages);
        }
        if (\sizeof($messages) > 0) {
            return $messages;
        }
        // All valid, now write all entities to the database.
        foreach ($queue as $entity) {
            $this->doPersist($entity, $translator, false, $messages);
        }
        if ($flush) {
            $this->getEm()->flush();
        }
        $this->getQueue()->clear();
        return $messages;
    }

    /**
     * @param AbstractEntity $entity
     * @param PlaceholderTranslator $translator
     * @param array $messages
     * @return bool
     */
    public function validateEntity(AbstractEntity $entity, PlaceholderTranslator $translator, array & $messages) : bool {
        if ($entity->getId() == AbstractEntity::INVALID_ID) {
            \array_push($messages, Message::danger('error.validation', 'error.validation.invalid'));
            return false;
        }
        $entity->sanitize();
        $violations = self::getValidator($translator)->validate($entity);
        foreach ($violations as $violation) {
            \array_push($messages, Message::danger($translator->gettext('error.validation'), $violation->getMessage()));
        }
        if ($violations->count() > 0) {
            return false;
        }
        try {
            return $entity->validateMore($messages, $this->getEm(), $translator);
        }
        catch (Throwable $e) {
            Context::getInstance()->getLogger()->log("Failed to validate entity: " . $e);
            \array_push($messages, Message::dangerI18n('error.database', $e->getMessage(), $translator));
            return false;
        }
    }

    /**
     * @param PlaceholderTranslator $translator
     * @return ValidatorInterface
     */
    private static function getValidator(PlaceholderTranslator $translator) : ValidatorInterface {
        if (self::$VALIDATOR === null) {
            self::$VALIDATOR = Validation::createValidatorBuilder()->enableAnnotationMapping()->setTranslationDomain("validation")->setTranslator($translator)->getValidator();
        }
        return self::$VALIDATOR;
    }

    /** @return DocumentDao */
    public static function document(EntityManager $em) : DocumentDao {
        return new DocumentDao($em);
    }
    
    /** @return LessonDao */
    public static function lesson(EntityManager $em) : LessonDao {
        return new LessonDao($em);
    }
    
    /** @return ExamDao */
    public static function exam(EntityManager $em) : ExamDao {
        return new ExamDao($em);
    }

    /** @return ExpireTokenDao */
    public static function expireToken(EntityManager $em) : ExpireTokenDao {
        return new ExpireTokenDao($em);
    }

    /** @return ForumDao */
    public static function forum(EntityManager $em) : ForumDao {
        return new ForumDao($em);
    }

    /** @return MailDao */
    public static function mail(EntityManager $em) : MailDao {
        return new MailDao($em);
    }
    
    /** @return PostDao */
    public static function post(EntityManager $em) : PostDao {
        return new PostDao($em);
    }

    /** @return TutorialGroupDao */
    public static function tutorialGroup(EntityManager $em) : TutorialGroupDao {
        return new TutorialGroupDao($em);
    }

    /** @return TagDao */
    public static function tag(EntityManager $em) : TagDao {
        return new TagDao($em);
    }

    /** @return ThreadDao */
    public static function thread(EntityManager $em) : ThreadDao {
        return new ThreadDao($em);
    }

    /** @return UserDao */
    public static function user(EntityManager $em) : UserDao {
        return new UserDao($em);
    }
    
    /** @return UserOptionDao */
    public static function userOption(EntityManager $em) : UserOptionDao {
        return new UserOptionDao($em);
    }

    /** @return FieldOfStudyDao */
    public static function fieldOfStudy(EntityManager $em) : FieldOfStudyDao {
        return new FieldOfStudyDao($em);
    }

    /** @return CourseDao */
    public static function course(EntityManager $em) : CourseDao {
        return new CourseDao($em);
    }

    /** @return GenericDao */
    public static function generic(EntityManager $em) : GenericDao {
        return new GenericDao($em);
    }

    /** @return ScheduledEventDao */
    public static function scheduledEvent(EntityManager $em) : ScheduledEventDao{
        return new ScheduledEventDao($em);
    }
    
    /** @return UniversityDao */
    public static function university($em) : UniversityDao {
        return new UniversityDao($em);
    }

    /** @return DiningHallDao */
    public static function diningHallMeal(EntityManager $em) : DiningHallMealDao {
        return new DiningHallMealDao($em);
    }

    /** @return DiningHallDao */
    public static function diningHall(EntityManager $em) : DiningHallDao {
        return new DiningHallDao($em);
    }
    
    /**
     * @return QueryBuilder A new query builder for a custom query with a from clause set already.
     */
    protected function qbFrom(string $alias = null) : QueryBuilder {
        return $this->getEm()->createQueryBuilder()->from($this->getEntityClass(), $alias ?? 'e');
    }
    
    /**
     * @return QueryBuilder A new query builder.
     */
    protected function qb() : QueryBuilder {
        return $this->getEm()->createQueryBuilder();
    }
    
    /**
     * @return QueryBuilder A new query builder for a custom query with a from delete set already.
     */
    protected function qbDelete(string $alias = null) : QueryBuilder {
        return $this->getEm()->createQueryBuilder()->delete($this->getEntityClass(), $alias ?? 'e');
    }

    protected abstract function getEntityClass() : string;
}
