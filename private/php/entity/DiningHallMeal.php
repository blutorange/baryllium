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

use DateTime;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Moose\Extension\DiningHall\DiningHallMealInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Throwable;

/**
 * @Entity
 * @Table(name="dininghallmeal")
 * @author madgaksha
 */
class DiningHallMeal extends AbstractEntity {
    
    /**
     * @Column(name="_name", type="string", length=255, unique=false, nullable=false)
     * @Assert\NotBlank(message="dininghallmeal.name.blank")
     * @Assert\Length(max=255, maxMessage="dininghallmeal.name.maxlength")
     * @var string
     */
    protected $name;
    
    /**
     * @Column(name="price", type="integer", unique=false, nullable=true)
     * @Assert\GreaterThanOrEqual(value=0, message="dininghallmeal.price.negative")
     * @var int
     */
    protected $price;

    /**
     * @Column(name="_date", type="date", unique=false, nullable=false)
     * @Assert\NotNull(message="dininghallmeal.date.null")
     * @var DateTime
     */
    protected $date;
    
    /**
     * @Column(name="flags", type="integer", unique=false, nullable=false)
     * @Assert\GreaterThanOrEqual(value=0, message="dininghallmeal.flags.negative")
     * @var int
     */
    protected $flags;
    
    /**
     * @Column(name="image", type="text", unique=false, nullable=true)
     * @var string Data URL (base 64) with this meal's image.
     */
    protected $image;
    
    /**
     * @ManyToOne(targetEntity="DiningHall", fetch="EAGER")
     * @JoinColumn(name="dininghall_id", referencedColumnName="id", nullable=false, unique=false)
     * @Assert\NotNull(message="dininghallmeal.dininghall.null")
     * @var DiningHall The dining hall which offers this meal.
     */
    protected $diningHall;
    
    public function __construct() {
        $this->flags = 0;
        $this->date = new DateTime();
    }
    
    public function getName() : string {
        return $this->name;
    }

    public function getPrice() {
        return $this->price;
    }

    public function getDate(): DateTime {
        return $this->date;
    }

    public function getFlags() : int {
        return $this->flags;
    }

    public function getImage() {
        return $this->image;
    }

    public function setName(string $name) : DiningHallMeal {
        $this->name = $name;
        return $this;
    }

    public function setPrice(int $price = null) : DiningHallMeal {
        $this->price = $price;
        return $this;
    }

    public function setDate(DateTime $date) : DiningHallMeal {
        $this->date = $date;
        return $this;
    }

    public function setFlags(int $flags) : DiningHallMeal {
        $this->flags = $flags;
        return $this;
    }

    public function setImage(string $image = null) : DiningHallMeal {
        $this->image = $image;
        return $this;
    }
    
    public function getDiningHall() : DiningHall {
        return $this->diningHall;
    }

    public function setDiningHall(DiningHall $diningHall) : DiningHallMeal {
        $this->diningHall = $diningHall;
        return $this;
    }
        
    /**
     * @param DiningHallMealInterface $meal
     * @param DiningHall $diningHall
     * @param bool $withImage Whether to set the image as well. Use null to try, but not throw any errors when the image cannot be retrieved.
     * @return DiningHallMeal
     */
    public static function fromMealInterface(DiningHallMealInterface $meal, DiningHall $diningHall, bool $withImage = null) : DiningHallMeal {
        $entity = new DiningHallMeal();
        $entity->setName($meal->getName());
        $entity->setPrice($meal->getPrice());
        $entity->setDate($meal->getDate());
        $entity->setFlags($meal->getFlags());
        if ($withImage === true) {
            $entity->setImage($meal->getImage());
        }
        else if ($withImage === null) {
            try {
                $image = $meal->getImage();
                $entity->setImage($image);
            }
            catch (Throwable $ignored) {
                \error_log("Failed to fetch image on a best-case basis, ignoring: $ignored");
            }
        }
        $entity->setDiningHall($diningHall);
        return $entity;
    }
    
    /** @return DiningHallMeal */
    public static function create() : DiningHallMeal {
        return new DiningHallMeal();
    }
}