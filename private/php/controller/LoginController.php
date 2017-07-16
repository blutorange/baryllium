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

namespace Moose\Controller;

use Doctrine\DBAL\Types\ProtectedString;
use Moose\Util\CmnCnst;
use Moose\ViewModel\Message;
use Moose\Web\HttpRequest;
use Moose\Web\HttpRequestInterface;
use Moose\Web\HttpResponseInterface;
use Moose\Web\RequestWithStudentIdTrait;
use Moose\Web\RequestWithUserTrait;

/**
 * Performs registration for a normal user account.
 *
 * @author madgaksha
 */
class LoginController extends BaseController {

    use RequestWithStudentIdTrait;
    use RequestWithUserTrait;
    
    public function doGet(HttpResponseInterface $response,
            HttpRequestInterface $request) {
        // Just render the login form.
        $this->getContext()->expireRememberCookie($response);
        $this->renderTemplate('t_login');
    }

    public function doPost(HttpResponseInterface $response, HttpRequestInterface $request) {
        $password = new ProtectedString($request->getParam(CmnCnst::URL_PARAM_LOGIN_PASSWORD));
        if (empty($password)) {
            $response->addMessage(Message::warningI18n('login.failure', 'login.userorpass.missing', $this->getTranslator()));
            $this->renderTemplate('t_login');
            return;
        }
        $user = $this->retrieveUserFromStudentId($response, $request, $this, $this);
        if ($user === null || !$user->verifyPassword($password)) {
            $response->addMessage(Message::warningI18n('login.failure', 'login.userorpass.invalid', $this->getTranslator()));
            $this->renderTemplate('t_login');
            return;
        }
        // Store user in session.
        $this->getSessionHandler()->newSession($user);
        if ($user->isTemporarySadmin()) {
            $redirectUrl = $this->getContext()->getServerPath(CmnCnst::PATH_SITE_SETTINGS_DATABASE);
        }
        else {
            $redirectUrl = $request->getParam(CmnCnst::URL_PARAM_REDIRECT_URL,
                $this->getContext()->getServerPath(CmnCnst::PATH_DASHBOARD));
        }
        // Now the user is authenticated.
        // Remember credentials when asked to.
        if ($request->getParamBool(CmnCnst::URL_PARAM_REMEMBERME)) {
            if ($user->getIsSiteAdmin()) {
                $response->addRedirectUrlMessage('RememberSadmin', Message::TYPE_WARNING);
            }
            else {
                // Do not create a new token when the user has got one already.
                if (empty($request->getParam(CmnCnst::COOKIE_REMEMBERME, null, HttpRequest::PARAM_COOKIE))) {
                    $this->createCookieAuth($response,
                            $this->getContext()->getConfiguration()->getSecurity(),
                            $this->getEm(), $this->getTranslator(), $user);
                }
            }
        }
        // Inform the user all went well and redirect him to the requested page.
        $this->getResponse()->addMessage(Message::successI18n('login.success', 'login.success.details', $this->getTranslator()));
        $response->setRedirect($redirectUrl);
        $this->renderTemplate('t_login_success', ['redirectUrl' => $redirectUrl]);
    }
    
    protected function getRequiresLogin() : int {
        return self::REQUIRE_LOGIN_NEVER;
    }
}