<?php

namespace Dao;

use Gettext\Translator;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManager;
use Entity\AbstractEntity;
use Ui\Message;

/**
 * Bridge between the database and entities.
 *
 * @author madgaksha
 */
abstract class AbstractDao {
    private $em;
    public function __construct(EntityManager $em) {
        $this->em = $em;
    }
    
    public final function getRepository() : EntityRepository {
        return $this->getEm()->getRepository($this->getEntityName());
    }
    
    protected final function getEm() : EntityManager {
        return $this->em;                
    }
    
    public final function findOneById($id) {
        return $this->getEm()->find($this->getEntityName(), $id);
    }
    
    public final function findAll() : array {
        $list = $this->getRepository()->findAll();
        return $list ?? [];
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

    public function persist(AbstractEntity $entity, Translator $translator = null, bool $flush = false) : array {
        $arr = [];
        if ($entity->getId() == AbstractEntity::$INVALID_ID) {
            array_push(Message::danger('error.validation', 'error.validation.invalid'));
            return $arr;
        }
        $res = $this->validateBeforePersist($entity, $translator, $arr);
        if ($res) {
            $this->doPersist($entity, $flush, $arr, $translator);
        }    
        else if (sizeof($arr) === 0) {
            array_push($arr, Message::dangerI18n('error.validation', 'error.validation.unknown', $translator));
        }
        return $arr;
    }
       
    private function doPersist(AbstractEntity $entity, bool $flush, array & $arr, Translator $translator) {
        try {
            $this->getEm()->persist($entity);
            if ($flush) {
                $this->getEm()->flush($entity);
            }
        }
        catch (\Throwable $e) {
            error_log("Failed to persist entity: " . $e);
            array_push($arr, Message::dangerI18n('error.database', $e->getMessage(), $translator));
        }
    }
    
    private function validateBeforePersist(AbstractEntity $entity, Translator $translator, array & $arr) : bool {
        $res = $entity->validate($arr, $translator);
        if ($res) {
            try {
                $res = $entity->validateMore($arr, $this->getEm(), $translator);
            }
            catch (\Throwable $e) {
                error_log("Failed to validate entity: " . $e);
                array_push($arr, Message::dangerI18n('error.database', $e->getMessage(), $translator));
                $res = false;
            }
        }
        return $res;
    }


    protected abstract function getEntityName() : string;
}