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

namespace Tasks;

use Context;
use Dao\AbstractDao;
use Dao\DiningHallDao;
use Dao\DiningHallMealDao;
use Dao\GenericDao;
use DateTime;
use Doctrine\ORM\EntityManager;
use Entity\DiningHall;
use Entity\DiningHallMeal;
use Entity\ScheduledEvent;
use Extension\DiningHall\DiningHallLoaderInterface;
use Extension\DiningHall\DiningHallMealInterface;
use InvalidArgumentException;
use Throwable;
use Ui\PlaceholderTranslator;


/**
 * Updates all meals from the configured dining halls.
 * @author madgaksha
 */
class DiningHallLoadEvent implements EventInterface {
    /** @var GenericDao */
    private $dao;
    /** @var DiningHallMealDao */
    private $mealDao;
    /** @var DiningHallDao */
    private $hallDao;
    
    public function run(Context $context, array &$options = null) {
        $em = $context->getEm();
        $events = AbstractDao::scheduledEvent($em)
                ->findAllByCategory(ScheduledEvent::CATEGORY_DININGHALL,
                        ScheduledEvent::SUBCATEGORY_DININGHALL_LOAD);
        $translator = $context->getSessionHandler()->getTranslatorFor('en');
        foreach ($events as $event) {
            $this->processEvent($event, $em, $translator);
        }
    }
    
    private function processEvent(ScheduledEvent $event, EntityManager $em, PlaceholderTranslator $translator) {
        $this->dao = AbstractDao::generic($em);
        $this->mealDao = AbstractDao::diningHallMeal($em);
        $this->hallDao = AbstractDao::diningHall($em);
        try {
            $loader = $this->getLoader($event->getClass());
            $hall = $this->getDiningHall($loader);
            $meals = $this->fetchMeals($loader);
            $this->updateMeals($meals, $hall);
            $this->dao->persistQueue($translator);
        }
        catch (Throwable $e) {
            $this->handleError($event, $em, $e);
        }
        finally {
            $this->handleFinally($em);
        }
    }

    /**
     * Instantiate the dining hall loader and check for sanity.
     * @param string $class
     * @return DiningHallLoaderInterface
     * @throws InvalidArgumentException When the class is not a DiningHallLoaderInterface.
     */
    private function getLoader(string $class) : DiningHallLoaderInterface {
        $implements = class_implements($class);
        if (!is_array($implements) || !array_key_exists(
                DiningHallLoaderInterface::class, $implements)) {
            throw new InvalidArgumentException("Class $class does not implemented DiningHallLoaderInterface.");
        }
        return new $class;
    }

    /**
     * Retrieve all meals offered at the dining hall.
     * @param DiningHallLoaderInterface $loader
     * @return DiningHallMeal[]
     */
    private function fetchMeals(DiningHallLoaderInterface $loader) : array {
        $from = new DateTime();
        $to = new DateTime();
        $from->modify('-1 week');
        $to->modify('+1 week');
        return $loader->fetchMenu($from, $to);
    }

    /**
     * Get the dining hall, create it in case it does not exist in the database
     * yet.
     * @param DiningHallLoaderInterface $loader
     * @return DiningHall
     */
    private function getDiningHall(DiningHallLoaderInterface $loader) : DiningHall {
        $name = $loader->getName();
        $hall = $this->hallDao->findOneByName($name);
        if ($hall === null) {
            $hall = DiningHall::fromLoader($loader);
            $this->dao->queue($hall);
        }
        return $hall;
    }

    /**
     * @param DiningHallMealInterface[] $meals
     * @param DiningHall $hall
     */
    private function updateMeals(array & $meals, DiningHall $hall) {
        // Most likely something went wrong when downloading the meals,
        // so let us keep the existing data.
        if (sizeof($meals) === 0) {
            return;
        }
        // Insert all meals as a new entity.
        foreach ($meals as $meal) {
            $newMeal = DiningHallMeal::fromMealInterface($meal, $hall);
            $this->dao->queue($newMeal);
        }
        // Remove all existing meal entities for this dining hall.
        $existingMeals = $this->mealDao->findAllByDiningHall($hall);
        $this->dao->removeAll($existingMeals);
    }

    /**
     * Try and rollback all changes to the database.
     * @param ScheduledEvent $event
     * @param EntityManager $em
     * @param Throwable $e
     */
    private function handleError(ScheduledEvent $event, EntityManager $em, Throwable $e) {
        $class = $event->getClass();
        \error_log("Failed to load dining hall meals for $class: $e");
        try {
            if ($em->isOpen()) {
                $em->rollback();
            }
        }
        catch (Throwable $e) {
            \error_log("Could not rollback em: $e");
        }
    }

    /**
     * Flush and close the EntityManager.
     * @param EntityManager $em
     */
    private function handleFinally(EntityManager $em) {
        try {
            if ($em->isOpen()) {
                $em->flush();
            }
        }
        catch (Throwable $e) {
            \error_log("Could not flush em: $e");
        }
        try {
            if ($em->isOpen()) {
                $em->close();
            }
        }
        catch (Throwable $e) {
            \error_log("Could not close em: $e");
        }
    }
}