<?php

namespace Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Description of Module
 * 
 * @Entity
 * @Table(name="module")
 * @author CaptainMalzbier
 */
class Course extends AbstractEntity {
  
    /**
     * @Column(name="_name", type="string", length=64, unique=false, nullable=false)
     * @Assert\NotNull(message="course.name.empty")
     * @Assert\Length(max=64, maxMessage="course.name.maxlength")
     * @Assert\Type(type="string")
     * @var string The name of this course.
     */
    protected $name;
    
    /**
     * @Column(name="description", name="description", unique=false, nullable=true)
     * @Assert\Type("string")
     * @var string Some arbitrary description of this course.
     */
    protected $description;
    
    /**
     * @Column(name="credits", type="integer", unique=false, nullable=true)
     * @Assert\GreaterThanOrEqual(value=0, message="course.credits.negative")
     * @Assert\Type("int")
     * @var string Credits to be obtained by completing this course.
     */
    protected $credits;

    /**
     * @OneToOne(targetEntity="Forum")
     * @JoinColumn(name="forum_id", referencedColumnName="id", nullable=false)
     * @Assert\NotNull(message?"course.forum-empty")
     * @Assert\Type("Entity\\Forum")
     * @var Forum The forum associated with this course.
     */
    protected $forum;

    public function __construct() {
        $this->moduleList = new ArrayCollection();
        $this->subjectList = new ArrayCollection();
    }

    public function getName() : string {
        return $this->name;
    }

    /**
     * @return string or null 
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * @return int or null
     */
    public function getCredits() {
        return $this->credits;
    }

    public function getForum(): Forum {
        return $this->forum;
    }

    public function setName(string $name) {
        $this->name = $name;
    }

    public function setDescription(string $description = null) {
        $this->description = $description;
    }

    public function setCredits(int $credits = null) {
        $this->credits = $credits;
    }

    public function setForum(Forum $forum) {
        $this->forum = $forum;
    }
}