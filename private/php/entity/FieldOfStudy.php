<?php

namespace Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\Table;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Description of Subject
 * 
 * @Entity
 * @Table(name="fieldofstudy")
 * @author CaptainMalzbier
 * @author Andre Wachsmuth
 */
class FieldOfStudy extends AbstractEntity {
    /**
     * @Column(name="discipline", type="string", length=64, unique=false, nullable=false)
     * @Assert\NotBlank(message="fieldofstudy.discipline.blank")
     * @Assert\Length(max=64, maxMessage="fieldofstudy.discipline.maxlength")
     * @Assert\Type("string")
     * @var string Some arbitrary name of this forum.
     */
    protected $discipline;
    
    /**
     * @Column(name="subdiscipline", type="string", length=64, unique=false, nullable=false)
     * @Assert\NotBlank(message="fieldofstudy.subdiscipline.blank")
     * @Assert\Length(max=64, maxMessage="fieldofstudy.subdiscipline.maxlength")
     * @Assert\Type("string")
     * @var string Some arbitrary name of this forum.
     */
    protected $subDiscipline;
    
    /**
     * @Column(name="shortname", type="string", length=2, unique=true, nullable=false)
     * @Assert\NotBlank(message="fieldofstudy.shortname.blank")
     * @Assert\Length(min=2, max=2, exactMessage="fieldofstudy.shortname.length")
     * @Assert\Type("string")
     * @var string The short name of this field of study, eg. MI.
     */
    protected $shortName;
    
    /**
     * @ManyToMany(targetEntity="Course")
     * @JoinTable(name="fieldofstudy_course",
     *      joinColumns={@JoinColumn(name="course_id", referencedColumnName="id")},
     *      inverseJoinColumns={@JoinColumn(name="fieldofstudy_id", referencedColumnName="id")}
     *      )
     * @Assert\NotNull
     * @Assert\Type("Doctrine\Common\Collections\ArrayCollection")
     * @var ArrayCollection The courses this field of study contains.
     */
    protected $courseList;
   
    public function __construct() {
        $this->courseList = new ArrayCollection();
    }

    public function getDiscipline() {
        return $this->discipline;
    }

    public function setDiscipline(string $discipline) {
        $this->discipline = $discipline;
    }    

    public function getSubDiscipline() {
        return $this->subDiscipline;
    }
    
    public function setSubDiscipline(string $subDiscipline = null) {
        $this->subDiscipline = $subDiscipline;
    }
    
    public function getShortName() : string{
        return $this->shortName;
    }

    public function getCourseList(): ArrayCollection {
        return $this->courseList;
    }

    public function setShortName(string $shortName) {
        $this->shortName = $shortName;
    }

    public function clearCourseList() {
        $this->courseList->clear();
    }
    
    public function addCourse(Course $course) {
        $this->courseList->add($course);
    }
    
    public function removeCourse(Course $course) {
        $this->courseList->removeElement($course);
    }
}