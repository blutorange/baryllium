<?php

/* The 3-Clause BSD License
 * 
 * SPDX short identifier: BSD-3-Clause
 *
 * Note: This license has also been called the "New BSD License" or "Modified
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
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Moose\Extension\DiningHall\DiningHallLoaderInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Entity
 * @Table(name="dininghall")
 * @author madgaksha
 */
class DiningHall extends AbstractEntity {
    
    /**
     * @Column(name="_name", type="string", length=255, unique=true, nullable=false)
     * @Assert\NotBlank(message="dininghallmeal.name.blank")
     * @Assert\Length(max=255, maxMessage="dininghallmeal.name.maxlength")
     * @var string
     */
    protected $name;
    
    /**
     * @ManyToOne(targetEntity="University")
     * @JoinColumn(name="university_id", referencedColumnName="id", unique=false, nullable=false)
     * @var University
     */
    protected $university;
    
    /**
     * @Column(name="longitude", type="float", unique=false, nullable=false)
     * @Assert\NotNull(message="dininghall.longitude.null")
     * @Assert\Range(min=-180, max=180, minMessage="dininghall.longitude.min", minMessage="dininghall.longitude.max")
     * @var int
     */
    protected $longitude;

    /**
     * @Column(name="latitude", type="float", unique=false, nullable=false)
     * @Assert\NotNull(message="dininghall.latitude.null")
     * @Assert\Range(min=-90, max=90, minMessage="dininghall.latitude.min", minMessage="dininghall.latitude.max")
     * @var int
     */
    protected $latitude;
        
    
    public function __construct() {
    }
    
    public function getName() : string {
        return $this->name;
    }

    public function setName(string $name) {
        $this->name = $name;
    }
    
    public function getLongitude() : float {
        return $this->longitude;
    }

    public function getLatitude() : float {
        return $this->latitude;
    }

    public function setLongitude(float $longitude) {
        $this->longitude = $longitude;
    }

    public function setLatitude(float $latitude) {
        $this->latitude = $latitude;
    }
    
    function getUniversity(): University {
        return $this->university;
    }

    function setUniversity(University $university) : DiningHall {
        $this->university = $university;
        return $this;
    }
    
    public function __toString() {
        return "DiningHall($this->name,$this->latitude,$this->longitude)";
    }

    public static function fromLoader(DiningHallLoaderInterface $loader) : DiningHall {
        $entity = new DiningHall();
        $entity->setName($loader->getName());
        $entity->setLatitude($loader->getLocation()->getLatitude());
        $entity->setLongitude($loader->getLocation()->getLongitude());
        return $entity;
    }

    /** @var DiningHall */
    public static function create() : DiningHall {
        return new DiningHall();
    }
}