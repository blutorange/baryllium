<?php

/* Note: This license has also been called the "New BSD License" or "Modified
 * BSD License". See also the 2-clause BSD License.
 * 
 * Copyright 2015 The Moose Team
 * 
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 * 
 * 1. Redistributions of source code must retain the above copyright notice,
 * this list of conditions and the following disclaimer.
 * 
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 * this list of conditions and the following disclaimer in the documentation
 * and/or other materials provided with the distribution.
 * 
 * 3. Neither the name of the copyright holder nor the names of its contributors
 * may be used to endorse or promote products derived from this software without
 * specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

namespace Moose\Entity;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\UniqueConstraint;
use InvalidArgumentException;
use Moose\Dao\AbstractDao;
use Moose\Util\PlaceholderTranslator;
use Moose\ViewModel\Message;
use Symfony\Component\Validator\Constraints as Assert;
use function mb_strlen;
use function mb_substr;

/**
 * A tutorial group (Seminargruppe) to which student belong to.
 * Many tutorial groups may be assigned to each field field of study
 * @Entity
 * @Table(name="tutorialgroup", uniqueConstraints={@UniqueConstraint(name="unique_tut", columns={"university_id", "fieldofstudy_id", "year", "_index"})})
 * @author CaptainMalzbier
 * @author Andre Wachsmuth
 */
class TutorialGroup extends AbstractEntity {
    const IDENTIFIER_LENGTH = 7;
    
    /**
     * 
     * @ManyToOne(targetEntity="FieldOfStudy", fetch="EAGER")
     * @JoinColumn(name="fieldofstudy_id", referencedColumnName="id")
     * @Assert\NotNull(message="tutorialgroup.fieldofstudy.empty")
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
     * @Column(name="_index", type="integer", nullable=false)
     * @Assert\NotNull(message="tutorialgroup.index.empty")
     * @Assert\GreaterThanOrEqual(value=0, message="tutorialgroup.index.negative")
     * @var string There may be several study groups per year, so this is their index. Eg 3.
     */
    protected $index;

    /**
     * @ManyToOne(targetEntity="University", fetch="EAGER")
     * @JoinColumn(name="university_id", referencedColumnName="id", unique=false, nullable=false)
     * @var University
     */
    protected $university;

    /** @var int
     * Not persisted, used only when extracting data from Campus Dual.
     */
    protected $universityIdentfier;
    
    public function __construct(int $year = null, int $index = null) {
        $this->year = $year;
        $this->index = $index;
    }

    public function getUniversity() : University {
        return $this->university;
    }
    
    /** @return int */
    public function getUniversityIdentifier() {
        return $this->universityIdentfier;
    }

    public function getYear() : int {
        return $this->year;
    }

    public function getIndex() : int {
        return $this->index;
    }

    /** @return TutorialGroup */
    public function setUniversity(University $university) : TutorialGroup  {
        $this->universityIdentfier = null;
        $this->university = $university;
        return $this;
    }

    /** @return TutorialGroup */
    public function setYear(int $year) : TutorialGroup {
        $this->year = $year;
        return $this;
    }

    /** @return TutorialGroup */
    public function setIndex(int $index) : TutorialGroup {
        $this->index = $index;
        return $this;
    }
    
    public function getFieldOfStudy(): FieldOfStudy {
        return $this->fieldOfStudy;
    }
    
    /** @return TutorialGroup */
    public function setUniversityIdentifier(int $universityIdentfier) : TutorialGroup {
        $this->universityIdentfier = $universityIdentfier;
        return $this;
    }
    
    /** @return TutorialGroup */
    public function setFieldOfStudy(FieldOfStudy $fieldOfStudy) : TutorialGroup {
        $this->fieldOfStudy = $fieldOfStudy;
        return $this;
    }
       
    public static function shortName(string $raw) {
        $data = trim($raw);
        $len = mb_strlen($data);
        if ($len !== self::IDENTIFIER_LENGTH) {
            throw new InvalidArgumentException("Expected identifier $data to consist of exactly seven characters, but found $len.");
        }
        return substr($data, 1, 2);
    }
    
    public function getCompleteName() {
        $fos = $this->getFieldOfStudy();
        $university = $this->getUniversity();
        if ($fos=== null || $university === null) {
            \error_log("Cannot get complete name, field of study or university missing: $fos, $university");
            return null;
        }
        $shortname = $fos->getShortName();
        return $university->getIdentifier() . $shortname . ($this->year-2000) . "-" . $this->index;
    }
    
    public function __toString() {
        return $this->getCompleteName();
    }

    /**
     * When only the university identifier was set, checks whether the corresponding
     * university exists in the database and sets its.
     * @param array $errMsg
     * @param EntityManagerInterface $em
     * @param PlaceholderTranslator $translator
     */
    public function validateMore(array & $errMsg, EntityManagerInterface $em, PlaceholderTranslator $translator) : bool {
        if ($this->universityIdentfier !== null) {
            $university = AbstractDao::university($em)->findOneByIdentifier($this->universityIdentfier);
            if ($university === null) {
                $errMsg[] = Message::dangerI18n('error.validation', 'validation.tutgroup.university.missing', $translator);
                return false;
            }
            else {
                $this->setUniversity($university);
                return true;
            }
        }
        return true;
    }

    /**
     * @param string $raw
     * @return TutorialGroup
     * @throws InvalidArgumentException
     */
    public static function valueOf(string $raw) {
        $data = \trim($raw);
        $len = \mb_strlen($data);
        if ($len !== self::IDENTIFIER_LENGTH) {
            throw new InvalidArgumentException("Expected identifier $data to consist of exactly seven characters, but found $len.");
        }
        $rawUniversity = mb_substr($data, 0, 1);
        if (!\is_numeric($rawUniversity)) {
            throw new InvalidArgumentException("Expected university part of $data to be a number.");
        }
        $rawYear = mb_substr($data, 3, 2);
        if (!\is_numeric($rawYear)) {
            throw new InvalidArgumentException("Expected year part of $data to be a number.");
        }
        $rawIndex = mb_substr($data, 6, 1);
        if (!\is_numeric($rawIndex)) {
            throw new InvalidArgumentException("Expected index part of $data to be a number.");
        }
        $tutGroup = new TutorialGroup(\intval($rawYear)+2000, \intval($rawIndex));
        $tutGroup->universityIdentfier = \intval($rawUniversity);
        return $tutGroup;
    }

    public static function create() : TutorialGroup {
        return new TutorialGroup();
    }    
}