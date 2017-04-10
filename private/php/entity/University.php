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

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A university (BA - Berufsakademie) at which student may be enrolled.
 * @Entity
 * @Table(name="university")
 * @author Andre Wachsmuth
 */
class University extends AbstractEntity {
    /**
     * @Column(name="identifier", type="integer", nullable=false, unique=true)
     * @Assert\NotNull(message="university.identifier.null")
     * @Assert\GreaterThanOrEqual(value=0, message="university.identifier.negative")
     * @var string University type, eg. 3 for BA Dresden.
     */
    protected $identifier;
    
    /**
     * @Column(name="_name", type="string", nullable=false, unique=false)
     * @Assert\NotEmpty(message="university.name.empty")
     * @var string Name of this university, eg. <code>BA Dresden</code>.
     */
    protected $name;
    
    public function __construct(int $university = null, int $year = null, int $index = null) {
        $this->university = $university;
        $this->year = $year;
        $this->index = $index;
    }

    public function setIdentifier(int $identifier) : University {
        $this->identifier = $identifier;
        return $this;
    }
    
    public function getIdentifier() : int {
        return $this->university;
    }
    
    public function getName() : string {
        return $this->name;
    }

    public function setName(string $name) : University {
        $this->name = $name;
        return $this;
    }
    
    public function __toString() {
        return "University($this->identifier,$this->name)";
    }
    
    /** @return University */
    public static function create() : University {
        return new University();
    }
}