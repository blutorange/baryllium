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

use DateTime;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Moose\Util\UiUtil;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A course a student may visit.
 *
 * @Entity
 * @Table(name="lesson")
 * @author madgaksha
 */
class Lesson extends AbstractEntity {
    /**
     * @Column(name="title", type="string", length=128, unique=false, nullable=false)
     * @Assert\Length(max=128, maxMessage="lesson.title.maxlength")
     * @var string The name of this lesson.
     */
    protected $title;
    
    /**
     * @Column(name="description", type="text", unique=false, nullable=true)
     * @var string Details for this lesson.
     */
    protected $description;
    
    /**
     * @Column(name="remarks", type="text", unique=false, nullable=true)
     * @var string More details and remarks for this lesson.
     */
    protected $remarks;
    
    /**
     * @Column(name="start", type="datetime", unique=false, nullable=false)
     * @Assert\NotNull(message="lesson.start.null")
     * @var \DateTime Start time of this lesson.
     */
    protected $start;
    
    /**
     * @Column(name="end", type="datetime", unique=false, nullable=false)
     * @Assert\NotNull(message="lesson.end.null")
     * @var \DateTime End time of this lesson.
     */
    protected $end;

    /**
     * @Column(name="room", type="string", length=32, unique=false, nullable=true)
     * @Assert\Length(max=32, maxMessage="lesson.room.maxlength")
     * @var string The room where this lesson takes place.
     */
    protected $room;
    
    /**
     * @Column(name="second_room", type="string", length=32, unique=false, nullable=true)
     * @Assert\Length(max=32, maxMessage="lesson.room.maxlength")
     * @var string The second room where this lesson takes place.
     */
    protected $secondRoom;

    /**
     * @Column(name="instructor", type="string", length=128, unique=false, nullable=true)
     * @Assert\Length(max=128, maxMessage="lesson.instructor.maxlength")
     * @var string The instructor of this lesson.
     */
    protected $instructor;
    
    /**
     * @Column(name="second_instructor", type="string", length=128, unique=false, nullable=true)
     * @Assert\Length(max=128, maxMessage="lesson.secondinstructor.maxlength")
     * @var string The second instructor of this lesson.
     */
    protected $secondInstructor;
    
    /**
     * @ManyToOne(targetEntity="TutorialGroup")
     * @JoinColumn(name="tutgroup_id", referencedColumnName="id", unique=false, nullable=false)
     * @Assert\NotNull(message="exam.tutorialgroup.null")
     * @var TutorialGroup The tutorial group to which this lesson belongs to.
     */
    protected $tutorialGroup;
    
    public function __construct() {
    }
    
    /** @return string */
    public function getTitle() : string {
        return $this->title;
    }

    /** @return string */
    public function getDescription() {
        return $this->description;
    }

    /** @return TutorialGroup */
    public function getTutorialGroup() : TutorialGroup {
        return $this->tutorialGroup;
    }
    
    /** @return DateTime */
    public function getStart(): \DateTime {
        return $this->start;
    }

    /** @return DateTime */
    public function getEnd(): \DateTime {
        return $this->end;
    }

    /** @return string */
    public function getRemarks() {
        return $this->remarks;
    }
       
    /** @return string */
    public function getRoom() {
        return $this->room;
    }

    /** @return string */
    public function getSecondRoom() {
        return $this->secondRoom;
    }

    /** @return string */
    public function getInstructor() {
        return $this->instructor;
    }

    /** @return string */
    public function getSecondInstructor() {
        return $this->secondInstructor;
    }

    /** @return Lesson */
    public function setTitle(string $title) : Lesson {
        $this->title = $title;
        return $this;
    }
    
    /** @return Lesson */
    public function setTutorialGroup(TutorialGroup $tutorialGroup) : Lesson {
        $this->tutorialGroup = $tutorialGroup;
        return $this;
    }

    /** @return Lesson */
    public function setDescription(string $description = null) : Lesson {
        $this->description = $description;
        return $this;
    }

    /** @return Lesson */
    public function setStart(\DateTime $start) : Lesson {
        $this->start = $start;
        return $this;
    }

    /** @return Lesson */
    public function setEnd(\DateTime $end) : Lesson {
        $this->end = $end;
        return $this;
    }

    /** @return Lesson */
    public function setRemarks(string $remarks = null) : Lesson {
        $this->remarks = $remarks;
        return $this;
    }
    
    /** @return Lesson */
    public function setRoom(string $room = null) : Lesson {
        $this->room = $room;
        return $this;
    }

    /** @return Lesson */
    public function setSecondRoom(string $secondRoom = null) : Lesson {
        $this->secondRoom = $secondRoom;
        return $this;
    }

    /** @return Lesson */
    public function setInstructor(string $instructor = null) : Lesson {
        $this->instructor = $instructor;
        return $this;
    }

    /** @return Lesson */
    public function setSecondInstructor(string $secondInstructor = null) : Lesson {
        $this->secondInstructor = $secondInstructor;
        return $this;
    }
    
    public static function make() : Lesson {
        return new Lesson();
    }
    
    /**
     * 
     * @param object $jsonObject An object representing a JSON object for a
     * lesson obtained from Campus Dual.
     * @return Lesson
     */
    public static function fromCampusDualJson($jsonObject) : Lesson {
        $lesson = self::make()
                ->setTitle(\trim($jsonObject->title))
                ->setStart(UiUtil::timestampToDate($jsonObject->start))
                ->setEnd(UiUtil::timestampToDate($jsonObject->end))
                ->setDescription(self::nullWhenEmpty($jsonObject->description ?? null))
                ->setRemarks(self::nullWhenEmpty($jsonObject->remarks ?? null))
                ->setRoom(self::nullWhenEmpty($jsonObject->room ?? null))
                ->setSecondRoom(self::nullWhenEmpty($jsonObject->sroom ?? null))
                ->setInstructor(self::nullWhenEmpty($jsonObject->instructor ?? null))
                ->setSecondInstructor(self::nullWhenEmpty($jsonObject->sinstructor ?? null));
        if ($lesson->getSecondRoom() === $lesson->getRoom()) {
            $lesson->setSecondRoom(null);
        }
        if ($lesson->getSecondInstructor() === $lesson->getInstructor()) {
            $lesson->setSecondInstructor(null);
        }
        return $lesson;
    }
    
    private static function nullWhenEmpty(string $string = null) {
        if ($string === null) {
            return null;
        }
        $s = \trim($string);
        return empty($s) ? null : $s;
    }
}