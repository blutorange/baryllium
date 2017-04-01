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
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Ramsey\Uuid\Uuid;

/**
 * A token with a random UUID, a creatiion date and a lifetime. It cannot be
 * used anymore once it becomes invalid.
 * 
 * @Entity
 * @Table(name="expiretoken")
 * @author madgaksha
 */
class ExpireToken extends AbstractEntity {

    /**
     * @Column(name="acttoken", type="string", length=36, unique=false, nullable=false)
     * @var string The token's uuid.
     */
    protected $uuid;

    /**
     * @Column(name="creationdate", type="integer", nullable=false)
     * @var string Date when this token was created, UNIX timestamp in seconds.
     */
    protected $creationDate;

    /**
     * @Column(name="lifetime", type="integer", nullable=false)
     * @var string Time in seconds this token is valid. Defaults to 1 day.
     */
    protected $lifeTime;

    public function __construct(int $lifetime = null) {
        $lifetime = $lifetime ?? (24 * 60 * 60);
        $this->uuid = Uuid::uuid4()->toString();
        $this->lifeTime = $lifetime;
        $this->creationDate = (new DateTime())->getTimestamp();
    }

    public function getCreationTimestamp(): int {
        return $this->creationDate;
    }

    public function getLifeTime(): int {
        return $this->lifeTime;
    }

    /**
     * DO NOT USE THIS IF YOU WANT TO GET THE TOKEN. USE getToken DIRECTLY AND
     * CHECK FOR NULLNESS. This is because the token may still be valid when
     * this function was called, but might have become invalid once the getToken
     * is called.
     * @return bool Whether this token is currently valid.
     */
    public function isValid(): bool {
        if ($this->lifeTime <= 0) {
            return false;
        }
        $now = new DateTime();
        $diff = $now->getTimestamp() - $this->getCreationTimestamp();
        return $diff < $this->getLifeTime();
    }

    /**
     * @return string The token, iff it is valid, or null iff it is not valid.
     */
    public function fetch() {
        if ($this->isValid()) {
            return $this->uuid;
        }
        return null;
    }

        /**
     * @return string The token, iff it is valid, or null iff it is not valid. Calling this function again always returns null.
     */
    public function fetchOnce(EntityManager $em) {
        if ($this->isValid()) {
            $this->lifeTime = -1;
            $em->persist($this);
            return $this->uuid;
        }
        return null;
    }    
}