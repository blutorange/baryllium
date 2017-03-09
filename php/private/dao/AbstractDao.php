<?php

namespace Dao;

use Doctrine\ORM\EntityRepository;
use \Doctrine\ORM\EntityManager;

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

    protected abstract function getEntityName() : string;
}
