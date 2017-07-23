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

use Moose\Context\MooseEnvironment;
use Moose\Context\MoosePhpMailOptions;
use Moose\Context\MooseSmtpOptions;
use Moose\FormModel\SiteSettingsMailFormModel;
use Moose\FormModel\SiteSettingsMailTestFormModel;
use Moose\ViewModel\Message;
use Moose\Web\HttpRequestInterface;
use Moose\Web\HttpResponseInterface;
use Nette\Mail\Message as Email;
use Throwable;

/**
 * @author David Heik
 */
class SiteSettingsMailController extends AbstractConfigController { 
    public function doGet(HttpResponseInterface $response, HttpRequestInterface $request) {
        $model = SiteSettingsMailTestFormModel::fromConfig($request, $this->getTranslator(), $this->getContext()->getConfiguration());
        $this->renderTemplate('t_sitesettings_mail', [
            'form' => $model->getAll(),
        ]);
    }

    public function doPost(HttpResponseInterface $response, HttpRequestInterface $request) {
        $this->routeFromSubmitButton($response, $request);
    }

    protected function postTest(HttpResponseInterface $response, HttpRequestInterface $request) {
        $model = SiteSettingsMailTestFormModel::fromRequest($request, $this->getTranslator());
        if (!$this->modifyConfig($response, $request, $model)) {
            return;
        }
        $conf = $this->getContext()->getConfiguration();
        $email = new Email();
        $email->addTo($model->getTestMailAddress());
        $email->setFrom($conf->getSystemMailAddress());
        $email->setSubject('Moose test mail');
        $email->setBody('Moose test mail');
        try {
            $this->getContext()->getMailer(true)->send($email);
            $response->addMessage(Message::successI18n(
                    'settings.mail.test.success',
                    'settings.mail.test.success.details',
                    $this->getTranslator(), [
                        'address' => $model->getTestMailAddress()
                    ]));
        }
        catch (Throwable $e) {
            $response->addMessage(Message::successI18n('settings.mail.test.failure', $e->getMessage(), $this->getTranslator(), [
                'address' => $model->getTestMailAddress()
            ]));
        }
        $this->doGet($response, $request);
    }

    private function modifyConfig(HttpResponseInterface $response,
            HttpRequestInterface $request, SiteSettingsMailFormModel $model) {
        $errors = $model->validate();
        if (!empty($errors)) {
            $response->addMessages($errors);
            $this->renderTemplate('t_sitesettings_mail', [
                'form' => $request->getAllParams()
            ]);
            return null;
        }
        $conf = $this->getContext()->getConfiguration();
        $env = $conf->getCurrentEnvironment();
        $conf->setSystemMailAddress($model->getSystemMailAddress());
        if ($model->isMailTypeSmtp()) {
            $this->setSmtpOpts($model, $env);
        }
        else {
            $env->setMailTypePhp(new MoosePhpMailOptions());
        }
        return $model;
    }
    
    protected  function postSave(HttpResponseInterface $response, HttpRequestInterface $request) {
        $model = SiteSettingsMailFormModel::fromRequest($request, $this->getTranslator());
        if (!$this->modifyConfig($response, $request, $model)) {
            return;
        }
        $errors = $this->saveConfiguration();
        if (!empty($errors)) {
            $response->addMessages($errors);
            $this->renderTemplate('t_sitesettings_mail', [
                'form' => $request->getAllParams()
            ]);
            return;
        }        
        $response->addMessage(Message::successI18n('settings.config.save.success', 'settings.config.save.success.details', $this->getTranslator()));
        $this->doGet($response, $request);
    }
    
    private function setSmtpOpts(SiteSettingsMailFormModel $model, MooseEnvironment $env) {
        $opts = new MooseSmtpOptions([
            MooseSmtpOptions::KEY_BIND_TO => $model->getSmtpBindTo(),
            MooseSmtpOptions::KEY_CONNECTION_TIMEOUT => $model->getSmtpConnectionTimeout(),
            MooseSmtpOptions::KEY_HOST => $model->getSmtpHost(),
            MooseSmtpOptions::KEY_IS_PERSISTENT => $model->getSmtpPersistentConnection(),
            MooseSmtpOptions::KEY_IS_SECURE => $model->isSecuritySsl() ? true : false,
            MooseSmtpOptions::KEY_PASSWORD=> $model->getSmtpPass(),
            MooseSmtpOptions::KEY_PORT=> $model->getSmtpPort(),
            MooseSmtpOptions::KEY_USERNAME=> $model->getSmtpUser()
        ]);
        $env->setMailTypeSmtp($opts);
    }
    
    protected function getRequiresLogin() : int {
        return self::REQUIRE_LOGIN_SADMIN;
    }    
}