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

use Doctrine\ORM\Mapping\Column;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A tag. Each entity may be tagged with several tags.
 * @Entity
 * @Table(name="tag")
 * @author madgaksha
 */
class Tag extends AbstractEntity {

    /**
     * @Column(name="_name", type="string", length=32, unique=false, nullable=false)
     * @Assert\NotBlank(message="tag.name.empty")
     * @Assert\Length(max=32, message="tag.name.maxlength")
     * @var string The name of this tag, eg. <code>maths</code>.
     */
    protected $name;
    
    /**
     * @Column(name="creationdate", type="date", unique=false, nullable=false)
     * @Assert\NotNull(message="tag.creationdate.empty")
     * @Assert\Length(max=32, message="tag.name.maxlength")
     * @var \DateTime When this tag was created.
     */
    protected $creationDate;
    
    public function getName() : string {
        return $this->name;
    }

    public function getCreationDate(): \DateTime {
        return $this->creationDate;
    }

    public function setName(string $name) {
        $this->name = $name;
    }

    public function setCreationDate(\DateTime $creationDate) {
        $this->creationDate = $creationDate;
    }
}
