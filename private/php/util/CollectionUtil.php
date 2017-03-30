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

namespace Util;

use ArrayAccess;
use Closure;
use Collator;
use Doctrine\Common\Collections\AbstractLazyCollection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use ReflectionCache;

/**
 * Utility functions for working with collections.
 * 
 * <p>
 * Uses code from Doctrine\Common\Collections\Expr\ClosureExpressionVisitor
 * (getObjectFieldValue, sortByFieldInternal), http://www.doctrine-project.org,
 * licensed under the MIT license.
 * </p>
 * @author madgaksha
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 */
class CollectionUtil {  
    static function &getByReference($object, $property) {
        $value = & Closure::bind(function & () use ($property) {
            return $this->$property;
        }, $object, $object)->__invoke();

        return $value;
    }
    
    /**
     * @param mixed $objectCollection Either a Doctrine\Common\Collections\Selectable, Doctrine\Common\Collections\Collection, or an array.
     * @param string $orderByField
     * @param bool $ascending
     * @param string $locale For locale-aware sorting.
     * @return mixed Either a Doctrine\Common\Collections\Collection or an array.
     */
    public static function sortByField(& $objectCollection, string $orderByField, bool $ascending = true, string $locale = null) {
        $originalCollection = $objectCollection;
        if ($objectCollection instanceof AbstractLazyCollection) {
            ReflectionCache::getMethod(AbstractLazyCollection::class, 'initialize')->invoke($objectCollection);
            $objectCollection = ReflectionCache::getProperty(AbstractLazyCollection::class, 'collection')->getValue($objectCollection);
        }
        
        $collator = $locale !== null ? new Collator($locale) : null;
        
        if ($objectCollection instanceof ArrayCollection) {
            Closure::bind(function () use ($orderByField, $ascending, $collator) {
                \usort($this->elements, CollectionUtil::sortByFieldInternal($orderByField, $ascending ? 1 : -1, $collator));
            }, $objectCollection, $objectCollection)->__invoke();
            return $originalCollection;
        }
        
        if (is_array($objectCollection)) {
            \usort($objectCollection, static::sortByFieldInternal($orderByField, $ascending ? 1 : -1, $collator));
            return $originalCollection;
        }
        
        return $objectCollection->matching(Criteria::create()->orderBy([$orderByField => $ascending ? Criteria::ASC : Criteria::DESC]));
    }
    
        /**
     * Accesses the field of a given object. This field has to be public
     * directly or indirectly (through an accessor get*, is*, or a magic
     * method, __get, __call).
     *
     * @param object $object
     * @param string $field
     *
     * @return mixed
     */
    private static function getObjectFieldValue($object, $field)
    {
        if (is_array($object)) {
            return $object[$field];
        }

        $accessors = array('get', 'is');

        foreach ($accessors as $accessor) {
            $accessor .= $field;

            if ( ! method_exists($object, $accessor)) {
                continue;
            }

            return $object->$accessor();
        }

        // __call should be triggered for get.
        $accessor = $accessors[0] . $field;

        if (method_exists($object, '__call')) {
            return $object->$accessor();
        }

        if ($object instanceof ArrayAccess) {
            return $object[$field];
        }

        if (isset($object->$field)) {
            return $object->$field;
        }

        // camelcase field name to support different variable naming conventions
        $ccField   = preg_replace_callback('/_(.?)/', function($matches) { return strtoupper($matches[1]); }, $field);

        foreach ($accessors as $accessor) {
            $accessor .= $ccField;


            if ( ! method_exists($object, $accessor)) {
                continue;
            }

            return $object->$accessor();
        }

        return $object->$field;
    }

    /**
     * Helper for sorting arrays of objects based on multiple fields + orientations.
     *
     * @param string   $name
     * @param int      $orientation
     * @param string   $locale
     *
     * @return Closure
     */
    public static function sortByFieldInternal($name, $orientation = 1, Collator $collator = null)
    {       
        return function ($a, $b) use ($name, $collator, $orientation) {
            $aValue = static::getObjectFieldValue($a, $name);
            $bValue = static::getObjectFieldValue($b, $name);

            if ($aValue === $bValue) {
                return 0;
            }
            
            if ($collator !== null && \is_string($aValue) && \is_string($bValue)) {
                $result = $collator->compare($aValue, $bValue);
                if ($result !== false) {
                    return $result;
                }
            }

            return (($aValue > $bValue) ? 1 : -1) * $orientation;
        };
    }
}