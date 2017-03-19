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