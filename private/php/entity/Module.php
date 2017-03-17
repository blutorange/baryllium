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
 * Description of Module
 * 
 * @Entity
 * @Table(name="module")
 * @author CaptainMalzbier
 */
class Module extends AbstractEntity {

    /**
     * @Column(type="string", length=255, unique=false, nullable=false)
     * @var string Some arbitrary name of this forum.
     */
    protected $name;
    private static $MAX_LENGTH_NAME = 255;

    /**
     * List of forums this forum contains.
     * @OneToMany(targetEntity="Module", mappedBy="parentModule")
     */
    private $moduleList;

    /**
     * Many Users have Many Groups.
     * @ManyToMany(targetEntity="Subject", inversedBy="module")
     * @JoinTable(name="module_subject")
     */
    private $subjectList;

    public function __construct() {
        $this->moduleList = new ArrayCollection();
        $this->subjectList = new ArrayCollection();
    }

    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
    }

//    public function getParentModule() {
//        return $this->parentModule;
//    }

//    public function setParentModule(Module $parentModule = null) {
//        $this->parentModule = $parentModule;
//        if ($parentModule !== null) {
//            $parentModule->subModuleList->add($this);
//        }
//    }

    public function getModuleList() {
        return $this->subModuleList;
    }

    public function setModuleList(ArrayCollection $subModuleList) {
        $this->moduleList = $moduleList;
//        foreach ($moduleList as $f) {
//            $f->parentModule = $this;
//        }
    }

    public function addModule(Module $module) {
        $this->getModuleList()->add($module);
//        $subModule->parentModule = $this;
    }

    public function validate(array & $errMsg, PlaceholderTranslator $translator): bool {
        $valid = true;
        $valid = $valid && $this->validateNonEmptyStringLength($this->name,
                        self::$MAX_LENGTH_NAME, $errMsg, $translator,
                        'error.validation', 'error.forum.name.empty',
                        'error.forum.name.overlong');
        return $valid;
    }

}
