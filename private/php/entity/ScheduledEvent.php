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
use Doctrine\ORM\Mapping\Table;
use ReflectionClass;
use Symfony\Component\Validator\Constraints as Assert;
use function mb_substr;

/**
 * @Entity
 * @Table(name="scheduledevent")
 * @author madgaksha
 */
class ScheduledEvent extends AbstractEntity {
    const CATEGORY_DININGHALL = "dhall";
    const CATEGORY_CLEANUP = "clnup";
    const CATEGORY_MAIL = "mail";
    
    const SUBCATEGORY_DININGHALL_LOAD = "load";
    const SUBCATEGORY_CLEANUP_EXPIRETOKEN = "etoken";
    const SUBCATEGORY_MAIL_SEND = "resend";
       
    private static $CATEGORIES;
    private static $SUBCATEGORIES;
    
    /**
     * @Column(name="parameter", type="string", length=255, unique=false, nullable=true)
     * @Assert\Length(max=255, maxMessage="scheduledevent.parameter.maxlength")
     * @var string The name of the executing class.
     */
    protected $parameter;
    
    /**
     * @Column(name="_name", type="string", length=64, unique=false, nullable=false)
     * @Assert\NotBlank(message = "scheduledevent.name.empty")
     * @Assert\Length(max=64, maxMessage="scheduledevent.name.maxlength")
     * @var string A description of this task.
     */
    protected $name;
    
    /**
     * @Column(name="configuration", type="text", unique=false, nullable=true)
     * @Assert\Json(type="object", syntaxMessage="scheduleevent.configuration.syntax", schemaMessage="scheduleevent.configuration.schema")
     * @var string Additional configuration options.
     */
    protected $configuration;
    
    /**
     * @Column(name="category", type="string", length=8, unique=false, nullable=false)
     * @Assert\NotNull(message="scheduledevent.category.blank")
     * @Assert\Choice(callback="getCategories", message="scheduledevent.category.choice", strict="true")
     * @var string The category of this task.
     */
    protected $category;
    
    /**
     * @Column(name="subcategory", type="string", length=8, unique=false, nullable=false)
     * @Assert\NotNull(message="scheduledevent.subcategory.blank")
     * @Assert\Choice(callback="getSubCategories", message="scheduledevent.subcategory.choice", strict="true")
     * @var string The category of this task.
     */
    protected $subCategory;
    
    /**
     * @Column(name="is_active", type="boolean", nullable=true)
     * @var bool
     */
    protected $isActive;
    
    /** @return string */
    public function getParameter() {
        return $this->parameter;
    }

    /** @return string */
    public function getName() : string {
        return $this->name;
    }

    /** @return array */
    public function getConfiguration() : array {
        return $this->configuration ?? [];
    }

    /** @return bool */
    public function getIsActive() : bool {
        return $this->isActive ?? false;
    }

    public function setParameter(string $class = null) : ScheduledEvent {
        $this->parameter = $class;
        return $this;
    }

    public function setName(string $name) : ScheduledEvent {
        $this->name = $name;
        return $this;
    }

    public function setConfiguration(array $configuration = null) : ScheduledEvent {
        $this->configuration = $configuration ?? [];
        return $this;
    }

    public function setIsActive(bool $isActive = null) : ScheduledEvent {
        $this->isActive = $isActive ?? false;
        return $this;
    }
    
    public function getCategory() : string {
        return $this->category;
    }

    public function getSubCategory() : string {
        return $this->subCategory;
    }

    public function setCategory(string $category) : ScheduledEvent {
        $this->category = $category;
        return $this;
    }

    public function setSubCategory(string $subCategory) : ScheduledEvent {
        $this->subCategory = $subCategory;
        return $this;
    }
    
    public static function getCategories() : array {
        if (self::$CATEGORIES === null) {
            self::makeCategories();
        }
        return self::$CATEGORIES;
    }
    
    public static function getSubCategories() : array {
        if (self::$SUBCATEGORIES=== null) {
            self::makeCategories();
        }
        return self::$SUBCATEGORIES;
    }
    
    private static function makeCategories() {
        $rfl = new ReflectionClass(ScheduledEvent::class);
        self::$CATEGORIES = [];
        self::$SUBCATEGORIES = [];
        foreach ($rfl->getConstants() as $name => $value) {
            if (mb_substr($name, 0, 9) === 'CATEGORY_') {
                array_push(self::$CATEGORIES, $value);
            }
            else if (mb_substr($name, 0, 12) === 'SUBCATEGORY_') {
                array_push(self::$SUBCATEGORIES, $value);
            }
        }
    }

    /** @return ScheduledEvent */
    public static function create() : ScheduledEvent {
        return new ScheduledEvent();
    }

}