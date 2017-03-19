<?php

namespace Dao;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
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

    public function __construct(EntityManager $em) {
        $this->em = $em;
    }
    
    public final function getRepository() : EntityRepository {
        return $this->getEm()->getRepository($this->getEntityClass());
    }
    
    protected final function getEm() : EntityManager {
        return $this->em;                
    }
    
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
        $query = $this->getEm()->createQuery("SELECT partial u.{id} FROM $name u WHERE u.$fieldName = ?1");
        $query->setParameter(1, $fieldValue);
        return sizeof($query->getResult());
    }
    
    /**
     * @param string $fieldName
     * @param type $value
     * @return array
     */
    public final function findAllByField(string $fieldName, $value) : array {
        $critera = [];
        $critera[$fieldName] = $value;
        $list = $this->getRepository()->findBy($critera);
        return $list ?? [];
    }
    
    public final function findOneByField(string $fieldName, $value) {
        $critera = [];
        $critera[$fieldName] = $value;
        return $this->getRepository()->findOneBy($critera);
    }

    public function persist(AbstractEntity $entity, PlaceholderTranslator $translator, bool $flush = false) : array {
        $messages = [];
        if ($entity->getId() == AbstractEntity::$INVALID_ID) {
            array_push(Message::danger('error.validation', 'error.validation.invalid'));
            return $messages;
        }
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
            error_log("Failed to persist entity: " . $e);
            array_push($arr,
                    Message::dangerI18n('error.database', $e->getMessage(),
                            $translator));
        }
    }

    private function validateBeforePersist(AbstractEntity $entity, PlaceholderTranslator $translator, array & $messages) : bool {
        $violations = $this->getValidator($translator)->validate($entity);
        if ($violations->count() === 0) {
            return true;
        }
        $violations->get(0)->getMessage();
        foreach ($violations as $violation) {
            \array_push($messages, Message::danger('error.validation', $violation->getMessage()));
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

    public static function document(EntityManager $em) {
        return new DocumentDao($em);
    }

    public static function expireToken(EntityManager $em) {
        return new ExpireTokenDao($em);
    }
    
    public static function forum(EntityManager $em) {
        return new ForumDao($em);
    }
    
    public static function mail(EntityManager $em) {
        return new MailDao($em);
    }
    
    public static function post(EntityManager $em) {
        return new PostDao($em);
    }
    public static function tutorialGroup(EntityManager $em) {
        return new TutorialGroupDao($em);
    }
    
    public static function tag(EntityManager $em) {
        return new TagDao($em);
    }
    
    public static function thread(EntityManager $em) {
        return new ThreadDao($em);
    }
    
    public static function user(EntityManager $em) {
        return new UserDao($em);
    }

    public static function course(EntityManager $em) {
        return new CourseDao($em);
    }    

    protected abstract function getEntityClass() : string;
}
