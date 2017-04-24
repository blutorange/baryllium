<?php

namespace Moose\Seed;

use DateTime;
use Doctrine\DBAL\Query\QueryBuilder as DBALQueryBuilder;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder as ORMQueryBuilder;
use Identicon\Generator\GdGenerator;
use Identicon\Generator\ImageMagickGenerator;
use Identicon\Identicon;
use InvalidArgumentException;
use joshtronic\LoremIpsum;
use Moose\Context\AnnotationEntityManagerFactory;
use Moose\Context\Context;
use Moose\Context\EntityManagerFactoryInterface;
use Moose\Context\EntityManagerProviderInterface;
use Moose\Context\MooseConfig;
use Moose\Util\MathUtil;
use Nubs\RandomNameGenerator\Alliteration;
use Nubs\RandomNameGenerator\Generator;
use ReflectionClass;
use function mb_strpos;
use function mb_substr;

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

/**
 * Description of AbstractClass
 *
 * @author madgaksha
 */
abstract class DormantSeed {
    /** @var EntityManagerProviderInterface */
    private $em;
    
    /** @var LoremIpsum */
    private $loremIpsumGenerator;
    
    /** @var Generator */
    private $nameGenerator;
    
    public function __construct($entityManagerOrProvider) {
        $this->em = $entityManagerOrProvider ?? Context::getInstance();
        $this->loremIpsumGenerator = new LoremIpsum();
        $this->nameGenerator = new Alliteration();
    }
    
    public final function runAll(bool $flush = true) {
        $this->em = $this->em instanceof EntityManagerInterface ? $this->em : $this->em->getEm();
        try {
            $this->seedAll();
            if ($flush) {
                $this->em->flush();
            }
        }
        finally {
            if ($this->em->isOpen()) {
                $this->em->close();
            }
        }
    }
    
    protected function seedSeed(int $seed = 0) {
        mt_srand($seed);
        srand($seed);
    }
    
    public final function germinate(string $method, array & $arguments = null, bool $flush = true, bool $close = false) {
        $this->em = $this->em instanceof EntityManagerInterface ? $this->em : $this->em->getEm();
        try {
            $class = new ReflectionClass(get_class($this));
            if (!$class->hasMethod($method)) {
                $method = "seed$method";
            }
            $method = (new ReflectionClass(get_class($this)))->getMethod($method);
            $method->setAccessible(true);
            $method->invokeArgs($this, $arguments ?? []);
            if ($flush) {
                $this->em->flush();
            }
        }
        finally {
            if ($close && $this->em->isOpen()) {
                $this->em->close();
            }
        }
    }
    
    protected function dbName(string $class) : string {
        return $this->em->getClassMetadata($class)->getTableName();
    }

    protected function qbOrm() : ORMQueryBuilder {
        return $this->em->createQueryBuilder();
    }
    
    protected function qbDbal() : DBALQueryBuilder {
        return $this->em->getConnection()->createQueryBuilder();
    }
    
    protected function em() : EntityManagerInterface {
        return $this->em;
    }
    
    protected function time(int $year = null, int $month = null, int $day = null, int $hour = null, int $minute = null, int $second = null) : DateTime {
        $now = new \DateTime();
        $now->setDate($year ?? $now->format('Y'), $month ?? $now->format('m'), $day ?? $now->format('d'));
        $now->setTime($hour ?? $now->format('H'), $minute ?? $now->format('i'), $second ?? $now->format('s'));
        return $now;
    }
    
    protected function sentences(int $amount = 1) : string {
        return $this->loremIpsumGenerator->sentences($amount);
    }
    
    protected function paragraphs(int $amount = 1) : string {
        return $this->loremIpsumGenerator->paragraphs($amount);
    }
    
    protected function words(int $amount = 1) : string {
        return $this->loremIpsumGenerator->words($amount);
    }
    
    protected function name() : string {
        return $this->nameGenerator->getName();
    }
    
    protected static function cfg() : MooseConfig {
        return Context::getInstance()->getConfiguration();
    }
    
    protected static function ctx() : Context {
        return Context::getInstance();
    }

    protected function imageDataUri($seed, int $size = null) : string {
        $generator = extension_loaded('gd') ? new GdGenerator() : new ImageMagickGenerator();
        $identicon = new Identicon($generator);
        return $identicon->getImageDataUri($seed, MathUtil::max($size ?? 64, 8));
    }
    
    /**
     * @param array $seeds
     * @param EntityManagerFactoryInterface|EntityManagerInterface $emf
     * @throws InvalidArgumentException
     */
    public static function grow(array $seeds, $emf = null) {
        /* @var $em EntityManager */
        $emf = $emf ?? new AnnotationEntityManagerFactory();
        // Grow all seeds.
        $namespace = (new ReflectionClass(DormantSeed::class))->getNamespaceName();
        if ($emf instanceof EntityManagerInterface) {
            $em = $emf;
        }
        else {
            $em = $emf->makeEm(self::cfg()->getCurrentEnvironment(), self::ctx()->getFilePath("/private/php/entity"), self::cfg()->isNotEnvironment(MooseConfig::ENVIRONMENT_PRODUCTION));
        }
        foreach ($seeds as $className => $methods) {
            if (($pos = mb_strpos($className, ':')) > 0) {
                $className = mb_substr($className, 0, $pos);
            }
            if (!class_exists($className)) {
                $className = "$namespace\\$className";
            }
            if (!class_exists($className)) {
                $className = "$className" . "Seed";
            }
            $class = new ReflectionClass($className);
            if (!$class->isSubclassOf(DormantSeed::class))
                throw new InvalidArgumentException("Thing $className is not a dormant seed and cannot be grown.");
            $instance = $class->newInstance($em);
            $em->transactional(function(EntityManagerInterface $tem) use ($methods, $instance) {
                foreach ($methods as $x => $y) {
                    if (is_numeric($x)) {
                        $methodName = $y;
                        $arguments = [];
                    }
                    else {
                        $methodName = $x;
                        $arguments = $y ?? [];
                    }
                    $instance->germinate($methodName, $arguments, false, false);
                }
            });
            $em->flush();
        }            
    }
}