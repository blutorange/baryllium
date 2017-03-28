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

namespace Dao;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Entity\AbstractEntity;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Throwable;
use Ui\Message;
use Ui\PlaceholderTranslator;

/**
 * Bridge between the database and entities.
 *
 * @author madgaksha
 */
abstract class AbstractDao {
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


    public final function getRepository() : EntityRepository {
        return $this->getEm()->getRepository($this->getEntityClass());
    }
    
    public final function getEm() : EntityManager {
        return $this->em;                
    }
    
    /** @return AbstractEntity */
    public final function findOneById($id) {
        return $this->getEm()->find($this->getEntityClass(), $id);
    }
    
    public final function findAll() : array {
        $list = $this->getRepository()->findAll();
        return $list ?? [];
    }
    
    /**
     * Returns the number of objects found in the database. For performance,
     * this selects only the id attribute.
     * @param string $fieldName Name of the field (not database column) to check.
     * @param string $fieldValue Value to match.
     * @return bool The number of entities.
     */
    public final function countByField(string $fieldName, string $fieldValue) : int {
        $name = $this->getEntityClass();
        $query = $this->getEm()->createQuery("SELECT COUNT(u) FROM $name u WHERE u.$fieldName = ?1");
        $query->setParameter(1, $fieldValue);
        return $query->getSingleScalarResult();
    }
    
    /**
     * @param string $fieldName
     * @param type $value
     * @return array
     */
    public final function findAllByField(string $fieldName, $value,
            string $orderByField = null, bool $ascending = false,
            int $limit = null, int $offset = null) : array {
        return $this->findAllByMultipleFields([$fieldName => $value],
                        $orderByField, $ascending, $limit, $offset);
    }
    
    public function findAllByMultipleFields(array $fieldToValueMap,
            string $orderByField = null, bool $ascending = false,
            int $limit = null, int $offset = null) : array {
        $orderBy = $orderByField !== null ? [$orderByField => $ascending ? 'ASC'
                : 'DESC'] : [];
        $list = $this->getRepository()->findBy($fieldToValueMap, $orderBy,
                $limit, $offset);
        return $list ?? [];
    }

    public final function findOneByField(string $fieldName, $value) {
        $critera = [];
        $critera[$fieldName] = $value;
        return $this->getRepository()->findOneBy($critera);
    }
    
    public function findOneByMultipleFields(array $fieldToValueMap) {
        return $this->getRepository()->findOneBy($fieldToValueMap);
    }
    
    /**
     * Removes all entities from the database.
     * @param AbstractEntity[] $entities
     */
    public function removeAll(array & $entities) {
        foreach ($entities as $entity) {
            $this->remove ($entity);
        }
    }
    
    /**
     * @param AbstractEntity $entity The entity to be removed from the database.
     */
    public function remove(AbstractEntity $entity) {
        $this->getEm()->remove($entity);
    }
    
    /**
     * @param AbstractEntity $entity The entity to be persisted.
     * @param PlaceholderTranslator $translator Translator for generating error messages when validation fails.
     * @param bool $flush Whether to flush the entity manager. Should be false normally, the entity manager is flushed once at the end of each request.
     * @param array $messages Optional array of messages to be filled.
     * @return Message[] Array with one message for each validation error. When this array is empty, persist was successful.
     */
    public function persist(AbstractEntity $entity, PlaceholderTranslator $translator, bool $flush = false, array & $messages = []) : array {
        $res = $this->validateBeforePersist($entity, $translator, $messages);
        if ($res) {
            $this->doPersist($entity, $translator, $flush, $messages);
        }    
        else if (sizeof($messages) === 0) {
            array_push($messages, Message::dangerI18n('error.validation', 'error.validation.unknown', $translator));
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
            \error_log("Failed to persist entity: " . $e);
            \array_push($arr,
                    Message::dangerI18n('error.database', $e->getMessage(),
                            $translator));
        }
    }
    
    public function queue(AbstractEntity $entity) : AbstractDao {
        $this->getQueue()->add($entity);
        return $this;
    }
    
    public function persistQueue(PlaceholderTranslator $translator, bool $flush = false) : array {
        $messages = [];
        $queue = $this->getQueue();
        // Validate all entities, do not write anything to the database
        // when there are any violations.
        foreach ($queue as $entity) {
            $this->validateBeforePersist($entity, $translator, $messages);
        }
        if (sizeof($messages) > 0) {
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

    private function validateBeforePersist(AbstractEntity $entity, PlaceholderTranslator $translator, array & $messages) : bool {
        if ($entity->getId() == AbstractEntity::INVALID_ID) {
            \array_push($messages, Message::danger('error.validation', 'error.validation.invalid'));
            return false;
        }
        $violations = $this->getValidator($translator)->validate($entity);
        foreach ($violations as $violation) {
            \array_push($messages, Message::danger($translator->gettextVar('error.validation'), $violation->getMessage()));
        }
        if ($violations->count() > 0) {
            return false;
        }
        try {
            return $entity->validateMore($messages, $this->getEm(), $translator);
        }
        catch (Throwable $e) {
            \error_log("Failed to validate entity: " . $e);
            \array_push($messages, Message::dangerI18n('error.database', $e->getMessage(), $translator));
            return false;
        }
    }
    
    private static function getValidator(PlaceholderTranslator $translator) : ValidatorInterface {
        if (self::$VALIDATOR === null) {
            self::$VALIDATOR = Validation::createValidatorBuilder()->enableAnnotationMapping()->setTranslationDomain("validation")->setTranslator($translator)->getValidator();
        }
        return self::$VALIDATOR;
    }

    public static function document(EntityManager $em) : DocumentDao {
        return new DocumentDao($em);
    }

    /** @return ExpireTokenDao */
    public static function expireToken(EntityManager $em) : ExpireTokenDao {
        return new ExpireTokenDao($em);
    }
    
    public static function forum(EntityManager $em) : ForumDao {
        return new ForumDao($em);
    }
    
    public static function mail(EntityManager $em) : MailDao {
        return new MailDao($em);
    }
    
    /** @var PostDao */
    public static function post(EntityManager $em) : PostDao {
        return new PostDao($em);
    }
    public static function tutorialGroup(EntityManager $em) : TutorialGroupDao {
        return new TutorialGroupDao($em);
    }
    
    public static function tag(EntityManager $em) : TagDao {
        return new TagDao($em);
    }
    
    public static function thread(EntityManager $em) : ThreadDao {
        return new ThreadDao($em);
    }
    
    public static function user(EntityManager $em) : UserDao {
        return new UserDao($em);
    }
    
    public static function fieldOfStudy(EntityManager $em) : FieldOfStudyDao {
        return new FieldOfStudyDao($em);
    }

    public static function course(EntityManager $em) : CourseDao {
        return new CourseDao($em);
    }    
    
    public static function generic(EntityManager $em) : GenericDao {
        return new GenericDao($em);
    }   
    
    public static function scheduledEvent(EntityManager $em) : ScheduledEventDao{
        return new ScheduledEventDao($em);
    }
    
    public static function diningHallMeal(EntityManager $em) : DiningHallMealDao {
        return new DiningHallMealDao($em);
    }
    
    public static function diningHall(EntityManager $em) : DiningHallDao {
        return new DiningHallDao($em);
    }
    
    /**
     * @return QueryBuilder A new query builder for a custom query.
     */
    protected function qb() : QueryBuilder {
        return $this->getEm()->createQueryBuilder()->from($this->getEntityClass(), 'e');
    }

    protected abstract function getEntityClass() : string;
}
