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

namespace Moose\Util;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Cache\ApcuCache;
use Doctrine\Common\Cache\ArrayCache;
use Moose\Entity\Forum;
use Moose\Entity\Post;
use Moose\Entity\Thread;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;

/**
 * Description of ReflectionFieldList
 *
 * @author madgaksha
 */
class ReflectionCache
{
    private static $CLASS_CACHE = [];
    private static $PROPERTY_CACHE = [];
    private static $METHOD_CACHE = [];

    private static $ANNOTATION_READER;

    private function __construct()
    {
    }

    public static function getThreadForum(): ReflectionProperty
    {
        return self::getProperty(Thread::class, "forum");
    }

    public static function getForumCourse(): ReflectionProperty
    {
        return self::getProperty(Forum::class, "course");
    }

    public static function getPostThread()
    {
        return self::getProperty(Post::class, "thread");
    }

    /**
     * @param string $class
     * @param string $property
     * @return ReflectionProperty
     * @throws ReflectionException
     */
    public static function getProperty(string $class, string $property): ReflectionProperty
    {
        $rp = @self::getProperties($class)[$property];
        if ($rp === null) {
            throw new ReflectionException("No such property  $property for class $class.");
        }
        return $rp;
    }

    /**
     * @param string $class
     * @param string $method
     * @return ReflectionMethod
     * @throws ReflectionException
     */
    public static function getMethod(string $class, string $method): ReflectionMethod
    {
        $mp = @self::getMethods($class)[$method];
        if ($mp === null) {
            throw new ReflectionException("No such method $method for class $class.");
        }
        return $mp;
    }

    /**
     * @param string $class
     * @return ReflectionProperty[]
     */
    public static function getProperties(string $class): array
    {
        $rps = isset(self::$PROPERTY_CACHE[$class]) ? self::$PROPERTY_CACHE[$class] : null;
        if ($rps === null) {
            $rps = [];
            $rc = self::getClass($class);
            foreach ($rc->getProperties() as $rp) {
                $rp->setAccessible(true);
                $rps[$rp->getName()] = $rp;
            }
            self::$PROPERTY_CACHE[$class] = $rps;
        }
        return $rps;
    }

    /**
     * @param string $class
     * @return ReflectionMethod[]
     */
    public static function getMethods(string $class): array
    {
        $mps = isset(self::$METHOD_CACHE[$class]) ? self::$METHOD_CACHE[$class] : null;
        if ($mps === null) {
            $mps = [];
            $rc = self::getClass($class);
            foreach ($rc->getMethods() as $mp) {
                $mp->setAccessible(true);
                $mps[$mp->getName()] = $mp;
            }
            self::$METHOD_CACHE[$class] = $mps;
        }
        return $mps;
    }

    /**
     * @param string $class
     * @return ReflectionClass
     */
    public static function getClass(string $class): ReflectionClass
    {
        $rc = isset(self::$CLASS_CACHE[$class]) ? self::$CLASS_CACHE[$class] : null;
        if ($rc === null) {
            self::$CLASS_CACHE[$class] = $rc = new ReflectionClass($class);
        }
        return $rc;
    }

    /**
     * @param string $class
     * @param string $property
     * @return object[]
     */
    public static function getPropertyAnnotations(string $class, string $property): array
    {
        return self::getAnnotationReader()->getPropertyAnnotations(self::getProperty($class, $property));
    }

    public static function getPropertiesAnnotations(string $class): array
    {
        $reader = self::getAnnotationReader();
        return array_map(function (ReflectionProperty $rp) use ($reader) {
            return $reader->getPropertyAnnotations($rp);
        }, self::getProperties($class));
    }

    public static function getPropertyAnnoationsFor(ReflectionProperty $rp)
    {
        return self::getAnnotationReader()->getPropertyAnnotations($rp);
    }

    /**
     * @return CachedReader
     */
    public static function getAnnotationReader(): CachedReader
    {
        if (self::$ANNOTATION_READER === null) {
            $cache = function_exists('apcu_fetch') ? new ApcuCache() : new ArrayCache();
            self::$ANNOTATION_READER = new CachedReader(new AnnotationReader(), $cache);
        }
        return self::$ANNOTATION_READER;
    }
}