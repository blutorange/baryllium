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

use Moose\FormModel\SiteSettingsDatabaseModel;
use Moose\Util\CmnCnst;
use Moose\ViewModel\Message;
use Moose\Web\HttpRequest;
use Moose\Web\HttpRequestInterface;
use Moose\Web\HttpResponseInterface;
use Throwable;

/**
 * @author David Heik
 */
class SiteSettingsDatabaseController extends AbstractConfigController { 
    public function doGet(HttpResponseInterface $response, HttpRequestInterface $request) {
        $model = SiteSettingsDatabaseModel::fromConfig($request, $this->getTranslator(), $this->getContext()->getConfiguration());
        $this->renderTemplate('t_sitesettings_database', [
            'form' => $model->getAll()
        ]);
    }

    public function doPost(HttpResponseInterface $response, HttpRequestInterface $request) {
        $this->routeFromSubmitButton($response, $request);
    }
    
    protected function postSave(HttpResponseInterface $response, HttpRequestInterface $request) {
        if (!$this->modifyConfig($response, $request) || !$this->testDb($response)) {
            $this->renderTemplate('t_sitesettings_database', [
                'form' => $request->getAllParams(HttpRequest::PARAM_FORM)
            ]);
            return;
        }
        $errors = $this->saveConfiguration();
        if (\sizeof($errors) > 0) {
            $response->addMessages($errors);
            $this->renderTemplate('t_sitesettings_database', [
                'form' => $request->getAllParams(HttpRequest::PARAM_FORM)
            ]);
            return;
        }
        $response->addMessage(Message::successI18n('settings.db.save.success',
            'settings.db.save.success.details', $this->getTranslator()));
        $this->doGet($response, $request);
    }
    
    protected function postTest(HttpResponseInterface $response, HttpRequestInterface $request) {
        if ($this->modifyConfig($response, $request) && $this->testDb($response)) {
            $response->addMessage(Message::successI18n('settings.db.test.success',
            'settings.db.test.success.details', $this->getTranslator()));
        }
        $this->renderTemplate('t_sitesettings_database', [
            'form' => $request->getAllParams(HttpRequest::PARAM_FORM)
        ]);
    }
    
    private function modifyConfig(HttpResponseInterface $response, HttpRequestInterface $request) {
        $model = SiteSettingsDatabaseModel::fromRequest($request, $this->getTranslator());
        $errors = $model->validate();
        if (!empty($errors)) {
            $response->addMessages($errors);
            $this->renderTemplate('t_sitesettings_mail', [
                'form' => $request->getAllParams()
            ]);
            return false;
        }
        $conf = $this->getContext()->getConfiguration();
        $conf->getCurrentEnvironment()->getDatabaseOptions()
                ->setCollation($model->getCollation())
                ->setDatabaseName($model->getDatabaseName())
                ->setDatabaseType($model->getDatabaseType())
                ->setEncoding($model->getEncoding())
                ->setHost($model->getHost())
                ->setPassword($model->getPassword())
                ->setPort($model->getPort())
                ->setUsername($model->getUsername());
        return true;
    }
    
    protected function getRequiresLogin() : int {
        return self::REQUIRE_LOGIN_SADMIN;
    }

    public function testDb(HttpResponseInterface $response) {
        try {
            $this->getContext()->closeEm();
            $em = $this->getContext()->getEm();
        }
        catch (Throwable $e) {
            $response->addMessage(Message::warningI18n('settings.db.test.failure', $e->getMessage(), $this->getTranslator()));
            return false;
        }
        try {
            $em->getConnection()->connect();
        }
        catch (Throwable $e) {
            $this->getContext()->getLogger()->error($e, "Could not connect to the database");
            $response->addMessage(Message::warningI18n('settings.db.test.failure', $e->getMessage(), $this->getTranslator()));
            return false;
        }
        finally {
            try {
                $this->getContext()->closeEm();
            }
            catch (Throwable $e) {
                $response->addMessage(Message::warningI18n('settings.db.test.failure', $e->getMessage(), $this->getTranslator()));
                return false;
            }
        }
        return true;
    }
}