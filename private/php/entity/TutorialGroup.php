<?php

namespace Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\UniqueConstraint;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A tutorial group (Seminargruppe) to which student belong to.
 * Many tutorial groups may be assigned to each field field of study
 * @Entity
 * @Table(name="tutorialgroup", uniqueConstraints={@UniqueConstraint(name="unique_tut", columns={"university", "fieldofstudy_id", "year", "index"})})
 * @author CaptainMalzbier
 * @author Andre Wachsmuth
 */
class TutorialGroup extends AbstractEntity {
    const IDENTIFIER_LENGTH = 7;

    /**
     * @Column(name="university", type="integer", nullable=false)
     * @Assert\NotNull(message="tutorialgroup.university.empty")
     * @Assert\GreaterThanOrEqual(value=0, message="tutorialgroup.university.negative")
     * @var string University type, eg. 3 for BA Dresden.
     */
    protected $university;
    
    /**
     * 
     * @ManyToOne(targetEntity="FieldOfStudy")
     * @JoinColumn(name="fieldofstudy_id", referencedColumnName="id")
     * @Assert\NotNull(message="tutorialgroup.fieldofstudy.empty")
     * @Assert\Type("Entity\\FieldOfStudy")
     * @var FieldOfStudy Field of study to which this tutorial group belongs to.
     */
    protected $fieldOfStudy;
    
    /**
     * @Column(name="year", type="integer", nullable=false)
     * @Assert\NotNull(message="tutorialgroup.year.empty")
     * @Assert\GreaterThanOrEqual(value=0, message="tutorialgroup.year.negative")
     * @var string The year of this study group, eg 2015.
     */
    protected $year;

    /**
     * @Column(name="index", type="integer", nullable=false)
     * @Assert\NotNull(message="tutorialgroup.index.empty")
     * @Assert\GreaterThanOrEqual(value=0, message="tutorialgroup.index.negative")
     * @var string There may be several study groups per year, so this is their index. Eg 3.
     */
    protected $index;
   
    public function __construct(int $university = null, int $year = null, int $index = null) {
        $this->university = $university;
        $this->year = $year;
        $this->index = $index;
    }

    public function getUniversity() : int {
        return $this->university;
    }

    public function getYear() : int {
        return $this->year;
    }

    public function getIndex() : int {
        return $this->index;
    }

    public function setUniversity(int $university) {
        $this->university = $university;
    }

    public function setYear(int $year) {
        $this->year = $year;
    }

    public function setIndex(int $index) {
        $this->index = $index;
    }
    
    public function getFieldOfStudy(): FieldOfStudy {
        return $this->fieldOfStudy;
    }
    
    public function setFieldOfStudy(FieldOfStudy $fieldOfStudy) {
        $this->fieldOfStudy = $fieldOfStudy;
    }
       
    public static function valueOf(string $raw) {
        $data = trim($raw);
        $len = strlen($data);
        if ($len !== self::IDENTIFIER_LENGTH) {
            throw new InvalidArgumentException("Expected identifier $data to consist of exactly seven characters, but found $len.");
        }
        $rawUniversity = substr($data, 0, 1);
        if (!is_numeric($rawUniversity )) {
            throw new InvalidArgumentException("Expected university part of $data to be a number.");
        }
        $rawYear = substr($data, 3, 2);
        if (!is_numeric($rawYear)) {
            throw new InvalidArgumentException("Expected year part of $data to be a number.");
        }
        $rawIndex = substr($data, 6, 1);
        if (!is_numeric($rawIndex)) {
            throw new InvalidArgumentException("Expected index part of $data to be a number.");
        }
        return new TutorialGroup(intval($rawUniversity), intval($rawYear), intval($rawIndex));
    }    
}