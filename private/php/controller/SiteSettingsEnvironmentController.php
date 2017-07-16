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

use Moose\FormModel\SiteSettingsEnvironmentModel;
use Moose\Util\CmnCnst;
use Moose\ViewModel\Message;
use Moose\Web\HttpRequest;
use Moose\Web\HttpRequestInterface;
use Moose\Web\HttpResponseInterface;
use Throwable;

/**
 * @author David Heik
 */
class SiteSettingsEnvironmentController extends AbstractConfigController { 
    public function doGet(HttpResponseInterface $response, HttpRequestInterface $request) {
        $model = SiteSettingsEnvironmentModel::fromConfig($request, $this->getTranslator(), $this->getContext()->getConfiguration());
        $this->renderTemplate('t_sitesettings_environment', [
            'form' => $model->getAll()
        ]);
    }

    public function doPost(HttpResponseInterface $response, HttpRequestInterface $request) {
        $this->routeFromSubmitButton($response, $request);
    }
    
    protected function postSave(HttpResponseInterface $response, HttpRequestInterface $request) {
        $model = SiteSettingsEnvironmentModel::fromRequest($request, $this->getTranslator());
        $errors = $model->validate();
        if (!empty($errors)) {
            $response->addMessages($errors);
            $this->renderTemplate('t_sitesettings_environment', [
                'form' => $request->getAllParams()
            ]);
            return;
        }
        $errors = $this->modifyConfig($model);
        if (!empty($errors)) {
            $response->addMessages($errors);
            $this->renderTemplate('t_sitesettings_environment', [
                'form' => $request->getAllParams()
            ]);
            return;
        }
        $errors = $this->saveConfiguration();
        if (!empty($errors)) {
            $response->addMessages($errors);
            $this->renderTemplate('t_sitesettings_environment', [
                'form' => $request->getAllParams()
            ]);
            return;
        }
        $this->getContext()->getCache()->delete(CmnCnst::CACHE_MOOSE_CONFIGURATION);
        $response->addMessage(Message::successI18n('settings.config.save.success', 'settings.config.save.success.details', $this->getTranslator()));        
        $this->doGet($response, $request);
    }

    protected function postClear(HttpResponseInterface $response, HttpRequestInterface $request) {
        if ($this->getContext()->getCache()->deleteAll() === true) {
            $response->addRedirectUrlMessage('CacheCleared', Message::TYPE_SUCCESS);
            $response->setRedirectRelative(CmnCnst::PATH_SITE_SETTINGS_ENVIRONMENT);
        }
        else {
            $response->addMessage(Message::dangerI18n(
                    'settings.cache.clear.failure',
                    'settings.cache.clear.failure.details',
                    $this->getTranslator()));
            $this->renderTemplate('t_sitesettings_environment', [
                'form' => $request->getAllParams(HttpRequest::PARAM_FORM)
            ]);
        }
    }
    
    protected function getRequiresLogin() : int {
        return self::REQUIRE_LOGIN_SADMIN;
    }

    public function modifyConfig(SiteSettingsEnvironmentModel $model) : array {
        $errors = [];
        
        if (!$this->modifyDoctrineProxyPath($model, $errors)) {
            return $errors;
        }

        $conf = $this->getContext()->getConfiguration();
        $env = $conf->getCurrentEnvironment();
        $sec = $conf->getSecurity();
        
        $sec->setHttpOnly($model->getHttpOnly());
        $sec->setRememberMeTimeout($model->getRememberTimeout());
        $sec->setSameSite($model->getSameSitePolicy());
        $sec->setSessionSecure($model->getHttpsOnly());
                
        $conf->setPathLocalServer($model->getLocalServerAddress());
        $conf->setPathPublicServer($model->getPublicServerAddress());
        $env->setLogFile($model->getLogfilePath());    
        
        return $errors;
    }

    public function modifyDoctrineProxyPath(SiteSettingsEnvironmentModel $model, array & $errors) : bool {
        $conf = $this->getContext()->getConfiguration();
        $newPath = $model->getDoctrineProxyPath();
        $oldPath = $conf->getPathDoctrineProxy();
        if ($newPath !== $oldPath) {
            // Regenerate the proxies
            $em = $this->getContext()->getEm();
            $metas = $em->getMetadataFactory()->getAllMetadata();
            try {
                $em->getProxyFactory()->generateProxyClasses($metas, $newPath);
            }
            catch (Throwable $e) {
                $this->getContext()->getLogger()->error($e, 'Failed to generate doctrine proxies');
                $errors []= Message::dangerI18n('settings.paths.docproxy.failure', $e->getMessage(), $this->getTranslator());
                return false;
            }
        }
        $conf->setPathDoctrineProxy($newPath);
        return true;
    }

}