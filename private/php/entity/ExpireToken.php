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
use Doctrine\DBAL\Types\ProtectedString;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use LogicException;
use Moose\Context\Context;
use Moose\Dao\Dao;
use Moose\Util\EncryptionUtil;
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
     * @Column(name="challenge", type="string", length=384, unique=false, nullable=true)
     * @var string In addition to the UUID, a random token is generated and sent
     * to the user. We only store the hash of this token so that a database dump
     * is not sufficient to pass the challenge.
     */
    protected $challenge;

    /**
     * @Column(name="data", type="string", length=256, unique=false, nullable=true)
     * @var string Arbitrary data for the token. But see #setDataEntity.
     */
    protected $data;
    
    /**
     * @Column(name="creationdate", type="integer", nullable=false)
     * @var string Date when this token was created, UNIX timestamp in seconds.
     */
    protected $creationDate;

    /**
     * @Column(name="lifetime", type="integer", nullable=false)
     * @var $lifetime string Time in seconds this token is valid. Defaults to 1 day.
     */
    protected $lifeTime;
    
    private function __construct(int $lifetime = null) {
        $lifetime = $lifetime ?? (24 * 60 * 60);
        $this->uuid = Uuid::uuid4()->toString();
        $this->lifeTime = $lifetime;
        $this->creationDate = (new DateTime())->getTimestamp();
    }
    
    public function withChallenge() : ProtectedString {
        $raw = new ProtectedString(Context::getInstance()->getRandomLibFactory()->getMediumStrengthGenerator()->generate(24));
        $base64 = \base64_encode($raw->getString());
        if ($base64 === false) {
            $base64 = null;
            $raw = null;
            throw new LogicException('Could not generate challenge, base64encode failed.');
        }
        $this->challenge = EncryptionUtil::hashPwd($raw);
        return new ProtectedString($base64);
    }

    public function getCreationTimestamp(): int {
        return $this->creationDate;
    }

    public function getLifeTime(): int {
        return $this->lifeTime;
    }

    /**
     * DO NOT USE THIS IF YOU WANT TO GET THE TOKEN. USE getToken DIRECTLY AND
     * CHECK FOR NULLNESS.
     * @return bool Whether this token is currently valid.
     */
    public function isLegal(ProtectedString $token = null): bool {
        if ($this->lifeTime <= 0) {
            return false;
        }
        if ($this->challenge !== null) {
            if (ProtectedString::isEmpty($token)) {
                \error_log('Challenge verification failed, no token given.');
                return false;
            }
            $raw = \base64_decode($token->getString());
            if ($raw === false || empty($raw)) {
                $raw = null;
                \error_log('Challenge verification failed, invalid base64.');
                return false;
            }
            $ps = new ProtectedString($raw);
            $raw = null;
            if (!EncryptionUtil::verifyPwd($ps, $this->challenge)) {
                \error_log('Challenge verification failed, did not match stored hash.');
                return false;
            }
            $raw = null;
        }
        $now = new DateTime();
        $diff = $now->getTimestamp() - $this->getCreationTimestamp();
        return $diff < $this->getLifeTime();
    }

    /**
     * @return string The token, iff it is valid, or null iff it is not valid.
     */
    public function fetch(ProtectedString $challenge = null) {
        if ($this->isLegal($challenge)) {
            return $this->uuid;
        }
        return null;
    }

    /**
     * @return string The token, iff it is valid, or null iff it is not valid. Calling this function again always returns null.
     */
    public function fetchOnce(EntityManager $em, ProtectedString $challenge = null) {
        if ($this->isLegal($challenge)) {
            $this->lifeTime = -1;
            $em->persist($this);
            $em->flush($this);
            return $this->uuid;
        }
        return null;
    }
    
    public function checkAndInvalidate(EntityManager $em, ProtectedString $challenge = null) : bool {
        if ($this->isLegal($challenge)) {
            $this->lifeTime = -1;
            $em->persist($this);
            $em->flush($this);
            return true;
        }
        return false;
    }

    public function setData(string $data = null) : ExpireToken {
        $this->data = $data;
        return $this;
    }

    public function setDataEntity(AbstractEntity $entity, string $type = null) : ExpireToken {
        $this->setData(self::dataForEntity($entity, $type));
        return $this;
    }
    
    /**
     * @return string
     */
    public function getData() {
        return $this->data;
    }
    
    /**
     * @return AbstractEntity
     */
    public function getDataEntity(EntityManager $em, string $expectedClass = null) {
        $match = [];
        if (\preg_match('/([a-z_0-9\\\\]+)\\(([^:]*):(\d+)\)/i', $this->getData(), $match) !== 1) {
            return null;
        }
        $class = $match[1];
        $id = \intval($match[3]);
        $entity = Dao::generic($em)->findOneByClassAndId($class, $id);
        if ($expectedClass !== null && get_class($entity) !== $expectedClass) {
            return null;
        }
        return $entity;
    }
    
    /**
     * @param int $lifetime string Time in seconds this token is valid.
     * Defaults to 1 day.
     * @return \Moose\Entity\ExpireToken
     */
    public static function create(int $lifetime = null) : ExpireToken {
        return new ExpireToken($lifetime);
    }
    
    public static function dataForEntity(AbstractEntity $entity, string $type = null) {
        return \get_class($entity) . "(" . $type . ':' . $entity->getId() . ")";
    }
}