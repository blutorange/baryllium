<?php

namespace Entity;

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

    public function __construct(int $lifetime = 24 * 60 * 60) {
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