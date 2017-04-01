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

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Ui\PlaceholderTranslator;

/**
 * Base entity with an id.
 * 
 * @author madgaksha
 */
class AbstractEntity {    
    
    const INVALID_ID = -1;
    const INITIAL_ID = 0;

    /**
     * @Id
     * @Column(type="integer", length=32, unique=true, nullable=false)
     * @GeneratedValue
     * @var int
     */
    protected $id = 0;
  
    /**
     * Checks whether this entity validates within a context of other entities.
     * Usually not necessary, use this only in some rare cases when the database
     * cannot do the validation itself.
     * @param array $errMsg  Array with error messages to append to.
     * @param EntityManager  $em For checking with the database.
     * @param PlaceholderTranslator $translator Translator for translating validation constraint failure messages.
     * @return bool Whether this entity is valid.
     */
    public function validateMore(array & $errMsg, EntityManager $em, PlaceholderTranslator $translator) : bool {
        return true;
    }
    
    public function getId() : int {
        return $this->id;
    }
    public function setId(int $id) {
        $this->id = $id;
    }
    
    public function isValid() : bool {
        return $this->id !== self::INVALID_ID;
    }
}