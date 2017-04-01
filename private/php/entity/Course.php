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
 * @Table(name="course")
 * @author CaptainMalzbier
 */
class Course extends AbstractEntity {
  
    /**
     * @Column(name="_name", type="string", length=128, unique=false, nullable=false)
     * @Assert\NotNull(message="course.name.empty")
     * @Assert\Length(max=128, maxMessage="course.name.maxlength")
     * @var string The name of this course.
     */
    protected $name;
    
    /**
     * @Column(name="description", name="description", unique=false, nullable=true)
     * @var string Some arbitrary description of this course.
     */
    protected $description;
    
    /**
     * @Column(name="credits", type="integer", unique=false, nullable=true)
     * @Assert\GreaterThanOrEqual(value=0, message="course.credits.negative")
     * @var string Credits to be obtained by completing this course.
     */
    protected $credits;

    /**
     * @OneToOne(targetEntity="Forum")
     * @JoinColumn(name="forum_id", referencedColumnName="id", unique=true, nullable=false)
     * @Assert\NotNull(message="course.forum.empty")
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
    
    public function setForum(Forum $forum): Course {
        $this->forum = $forum;
        return $this;
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
    
    public function __toString() {
        return "Course($this->name,$this->credits CP)";
    }
}