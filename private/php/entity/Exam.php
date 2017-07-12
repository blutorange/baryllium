<?php
declare(strict_types=1);

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
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A course a student may visit.
 *
 * @Entity
 * @Table(name="exam")
 * @author madgaksha
 */
class Exam extends AbstractEntity {
    /**
     * @Column(name="title", type="string", length=128, unique=false, nullable=false)
     * @Assert\Length(max=128, maxMessage="exam.title.maxlength")
     * @var string The name of this exam.
     */
    protected $title;
    
    /**
     * @Column(name="exam_id", type="string", length=128, unique=false, nullable=false)
     * @Assert\Length(max=128, maxMessage="exam.examid.maxlength")
     * @Assert\NotNull(message="exam.examid.null")
     * @var string The ID for this exam, eg. <code>3MI-MGUPR-00</code>.
     */
    protected $examId;
    
    /**
     * @Column(name="is_subscribed", type="boolean", unique=false, nullable=true)
     * @var bool Whether the user subscribed to the exam.
     */
    protected $isSubscribed;
    
    /**
     * @Column(name="mark", type="integer", unique=false, nullable=true)
     * @Assert\Choice(choices={10,13,17,20,23,27,30,33,37,40,50}, strict=true, message="exam.mark.nochoice")
     * @var int Mark given for this exam, as an integer, eg. 10 for <code>1,0</code> etc.
     */
    protected $mark;
       
    /**
     * @Column(name="start", type="date", unique=false, nullable=true)
     * @var DateTime The date this exam was marked.
     */
    protected $marked;
    
    /**
     * @Column(name="announced", type="date", unique=false, nullable=true)
     * @var DateTime The date when the result of this exam was announced.
     */
    protected $announced;
    
    /**
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="user_id", referencedColumnName="id", unique=false, nullable=false)
     * @Assert\NotNull(message="exam.user.null")
     * @var User The user attempting this exam.
     */
    protected $user;
    
    public function __construct() {
    }
    
    public function getTitle() : string {
        return $this->title;
    }
    
    public function getExamId() : string {
        return $this->examId;
    }

    /** @return int */
    public function getMark() {
        return $this->mark;
    }
    
    /** @return User */
    public function getUser() : User {
        return $this->user;
    }

    /** @return DateTime|null */
    public function getMarked() {
        return $this->marked;
    }

    /** @return DateTime|null */
    public function getAnnounced() {
        return $this->announced;
    }

    /** @return bool */
    public function getIsSubscribed() : bool {
        return $this->isSubscribed ?? false;
    }

    /** @return Exam */
    public function setIsSubscribed(bool $isSubscribed = null) : Exam {
        $this->isSubscribed = $isSubscribed ?? false;
        return $this;
    }
    
    /** @return Exam */
    public function setTitle(string $title) : Exam {
        $this->title = $title;
        return $this;
    }
    
    /** @return Exam */
    public function setUser(User $user) : Exam {
        $this->user = $user;
        return $this;
    }
    
    /** @return Exam */
    public function setExamId(string $examId) : Exam {
        $this->examId = $examId;
        return $this;
    }

    /** @return Exam */
    public function setMark(int $mark = null) : Exam {
        $this->mark = $mark;
        return $this;
    }

    /** @return Exam */
    public function setMarked(DateTime $marked = null) {
        $this->marked = $marked;
        return $this;
    }

    /** @return Exam */
    public function setAnnounced(DateTime $announced = null) {
        $this->announced = $announced;
        return $this;
    }
        
    public static function make() : Exam {
        return new Exam();
    }

    public function setMarkString(string $mark) : Exam {
        $matches = [];
        if (\preg_match('/(1|2|3|4|5),(0|3|7)/', $mark, $matches) === 1) {
            return $this->setMark(\intval($matches[1])*10+\intval($matches[2]));
        }
        else {
            return $this->setMark(null);
        }
    }

    /* @return string */
    public function getMarkString() {
        $mark = $this->getMark();
        if ($mark === null) {
            return null;
        }
        $ten = \intdiv($mark, 10);
        $one = $mark % 10;
        return "$ten,$one";
    }

}