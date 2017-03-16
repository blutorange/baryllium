<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Entity;

use Dao\StudyGroupDao;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Ui\Message;
use Ui\PlaceholderTranslator;

/**
 * Each user may belong to at most one study group (Seminargruppe).
 * 
 * @Entity
 * @Table(name="studygroup")
 * @author CaptainMalzbier
 */
class StudyGroup extends AbstractEntity {

    /**
     * @Column(type="string", length=255, unique=true, nullable=false)
     * @var string The name of this study group, eg. <code>3MI15-1</code>
     */
    protected $name;
    private static $MAX_LENGTH_NAME = 255;

    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function validate(array & $errMsg, PlaceholderTranslator $translator): bool {
        $valid = true;
        if (empty($this->name)) {
            array_push($errMsg,
                    Message::dangerI18n('error.validation',
                            'error.studygroup.name.empty', $translator));
            $valid = false;
        }
        else if (strlen($this->name) > self::$MAX_LENGTH_NAME) {
            array_push($errMsg,
                    Message::dangerI18n('error.validation',
                            'error.studygroup.name.overlong', $translator,
                            ['count' => self::$MAX_LENGTH_NAME]));
            $valid = false;
        }
        return $valid;
    }

    public function validateMore(array & $errMsg, EntityManager $em,
            PlaceholderTranslator $translator): bool {
        $valid = true;
        $dao = new StudyGroupDao($em);
        if ($dao->existsByName($this->name)) {
            array_push($errMsg,
                    Message::dangerI18n('error.validation',
                            'error.studygroup.name.exists', $translator,
                            ['name' => $this->name]));
            $valid = false;
        }
        return $valid;
    }

}
