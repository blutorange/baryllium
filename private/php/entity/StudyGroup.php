<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Entity;

use Doctrine\ORM\EntityManager;
use Gettext\Translator;
/**
 * Description of Thread
 * 
 * @Entity
 * @Table(name="studygroup")
 * @author CaptainMalzbier
 */
class StudyGroup extends AbstractEntity {

    /**
     * @Column(type="string", length=255, unique=true, nullable=false)
     * @var string
     * studygroup name of this studygroup.
     */
    protected $name;
    
    public function validate(array & $errMsg, Translator $translator): bool {
        return true;
    }

    public function validateMore(array & $errMsg, EntityManager $em, Translator $translator): bool {
        return true;
    }

    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
    }
}
