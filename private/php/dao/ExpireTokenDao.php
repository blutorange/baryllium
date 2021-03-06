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

namespace Moose\Dao;

use DateTime;
use Moose\Entity\AbstractEntity;
use Moose\Entity\ExpireToken;

/**
 * Methods for interacting with Post objects and the database.
 *
 * @author madgaksha
 */
class ExpireTokenDao extends Dao {
    protected function getEntityClass(): string {
        return ExpireToken::class;
    }
    
    /**
     * Finds all tokens that are currently expired. 
     * @return ExpireToken[] List of expired tokens.
     */
    public function findAllExpired() : array {
        $name = $this->getEntityClass();
        $now = (new DateTime)->getTimestamp();
        $query = $this->getEm()->createQuery("SELECT e FROM $name e WHERE e.lifeTime <= 0 OR e.creationDate + e.lifeTime <= $now");
        return $query ->getResult() ?? [];
    }

    /**
     * @param string $token
     * @return ExpireToken Or null when not found.
     */
    public function findOneByToken(string $token){
        return $this->findOneByField('uuid', $token);
    }
    
    public function findAllByEntity(AbstractEntity $entity, string $type) : array {
        return $this->findAllByField('data', ExpireToken::dataForEntity($entity, $type));
    }    
}