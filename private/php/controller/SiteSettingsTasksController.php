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

namespace Moose\Controller;

use DateTime;
use Moose\Dao\Dao;
use Moose\Entity\DiningHall;
use Moose\Extension\DiningHall\DiningHallLoaderInterface;
use Moose\FormModel\SiteSettingsTasksModel;
use Moose\ViewModel\Message;
use Moose\ViewModel\TasksDiningHallModel;
use Moose\Web\HttpRequestInterface;
use Moose\Web\HttpResponseInterface;
use Throwable;

/**
 * @author David Heik
 */
class SiteSettingsTasksController extends AbstractConfigController { 
    public function doGet(HttpResponseInterface $response, HttpRequestInterface $request) {
        $model = SiteSettingsTasksModel::fromConfig($request, $this->getTranslator(), $this->getContext()->getConfiguration());
            $this->renderTemplate('t_sitesettings_tasks', [
            'form' => $model->getAll()
        ]);
    }

    public function doPost(HttpResponseInterface $response, HttpRequestInterface $request) {
        $this->routeFromSubmitButton($response, $request);
    }
    
    protected function postTestDiningHall(HttpResponseInterface $response,
            HttpRequestInterface $request, string $class) {
        $model = SiteSettingsTasksModel::fromRequest($request, $this->getTranslator());
        $implements = class_implements($class);
        if (!\in_array(DiningHallLoaderInterface::class, $implements)) {
            $response->addMessage(Message::warningI18n(
                'settings.tasks.dininghall.test.badclass',
                'settings.tasks.dininghall.test.badclass.details',
                $this->getTranslator(), [
                    'class' => $class
                ]
            ));
            $this->renderTemplate('t_sitesettings_tasks', [
                'form' => $model->getAll()
            ]);            
            return;
        }
        /* @var $loader DiningHallLoaderInterface */
        $loader = new $class();
        $from = new DateTime();
        $to = new DateTime();
        $from->modify('-1 week');
        $to->modify('+1 week');
        $name = $class::getLocalizedName($this->getContext()->getSessionHandler()->getLang());
        try {
            $count = sizeof($loader->fetchMenu($from, $to));
        }
        catch (Throwable $e) {
            $this->getContext()->getLogger()->error($e, "Error while testing dining hall $class");
            $response->addMessage(Message::dangerI18n(
                'settings.tasks.dininghall.test.failure',
                $e->getMessage(),
                $this->getTranslator(), [
                    'class' => $class,
                    'name' => $name
                ]
            ));
            $this->renderTemplate('t_sitesettings_tasks', [
                'form' => $model->getAll()
            ]);            
            return;
        }
        
        if ($count === 0) {
            $response->addMessage(Message::warningI18n(
                'settings.tasks.dininghall.test.warning',
                'settings.tasks.dininghall.test.warning.details',
                $this->getTranslator(), [
                    'class' => $class,
                    'name' => $name
                ]
            ));        
        }
        else {
            $response->addMessage(Message::successI18n(
                'settings.tasks.dininghall.test.success',
                'settings.tasks.dininghall.test.success.details',
                $this->getTranslator(), [
                    'class' => $class,
                    'name' => $name,
                    'count' => $count
                ]
            ));
        }
        
        $this->renderTemplate('t_sitesettings_tasks', [
            'form' => $model->getAll()
        ]);
    }
    
    protected function postSaveDiningHall(HttpResponseInterface $response, HttpRequestInterface $request) {
        $model = SiteSettingsTasksModel::fromRequest($request, $this->getTranslator());
        $errors = $model->validate();
        if (!empty($errors)) {
            $response->addMessages($errors);
            $this->renderTemplate('t_sitesettings_tasks', [
                'form' => $request->getAllParams()
            ]);
            return;
        }
        $tasks = $this->getContext()->getConfiguration()->getTasks();
        foreach ($model->getHalls() as $hall) {
            /* @var $hall TasksDiningHallModel[] */
            $tasks->setDiningHallSchedule($hall->getClass(), $hall->getSchedule());
            $tasks->setDiningHallIsActivated($hall->getClass(), $hall->getIsActivated());
            if ($hall->getIsActivated()) {
                $errors = $this->createDiningHall($hall->getClass());
                if (!empty($errors)) {
                    $response->addMessages($errors);
                    $this->renderTemplate('t_sitesettings_tasks', [
                        'form' => $request->getAllParams()
                    ]);
                    return;
                }                
            }
        }
        $errors = $this->saveConfiguration();
        if (!empty($errors)) {
            $response->addMessages($errors);
            $this->renderTemplate('t_sitesettings_tasks', [
                'form' => $request->getAllParams()
            ]);
            return;
        }
        $response->addMessage(Message::successI18n(
            'settings.tasks.dininghall.success',
            'settings.tasks.dininghall.success.details',
                $this->getTranslator()
        ));
        $this->doGet($response, $request);
    }
           
    protected function getRequiresLogin() : int {
        return self::REQUIRE_LOGIN_SADMIN;
    }

    public function createDiningHall(string $class) : array{
        $dao = Dao::diningHall($this->getEm());
        $hall = $dao->findOneByName($class::getName());
        if ($hall === null) {
            return $dao->persist(DiningHall::fromLoader($class), $this->getTranslator());
        }
        return [];
    }
}