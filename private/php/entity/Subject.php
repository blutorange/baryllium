<?php

namespace Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use Ui\Message;
use Ui\PlaceholderTranslator;

/**
 * Description of Subject
 * 
 * @Entity
 * @Table(name="subject")
 * @author CaptainMalzbier
 */
class Subject extends AbstractEntity {

    /**
     * @Column(type="string", length=255, unique=false, nullable=false)
     * @var string Some arbitrary name of this forum.
     */
    protected $generic;
    protected $specific;
    private static $MAX_LENGTH_NAME = 255;

    /**
     * List of generics this subject contains.
     * @OneToMany(targetEntity="Subject", mappedBy="generic")
     */
    private $genericList;
    
    public function __construct() {
        $this->genericList = new ArrayCollection();
    }

    public function getGeneric() {
        return $this->generic;
    }

    public function setGeneric($generic) {
        $this->generic = $generic;
    }    

    public function getSpecific() {
        return $this->specific;
    }
    
    public function setSpecific($specific) {
        $this->specific = $specific;
    }

    public function getGenericList() {
        return $this->genericList;
    }

    public function setGenericList(ArrayCollection $genericList) {
        $this->genericList = $genericList;
//        foreach ($subSubjectList as $f) {
//            $f->parentSubject = $this;
//        }
    }

    public function addSubject(Subject $generic) {
        $this->getGenericList()->add($generic);
//        $subSubject->parentSubject = $this;
    }

    public function validate(array & $errMsg, PlaceholderTranslator $translator): bool {
        $valid = true;
        $valid = $valid && $this->validateNonEmptyStringLength($this->name,
                        self::$MAX_LENGTH_NAME, $errMsg, $translator,
                        'error.validation', 'error.subject.name.empty',
                        'error.subject.name.overlong');
        return $valid;
    }

}
