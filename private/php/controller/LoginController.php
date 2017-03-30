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
use Moose\Controller\AbstractController;
use Moose\Web\HttpRequestInterface;
use Moose\Web\HttpResponseInterface;
use Moose\Web\RequestWithStudentIdTrait;
use Ui\Message;
use Util\CmnCnst;

/**
 * Performs registration for a normal user account.
 *
 * @author madgaksha
 */
class LoginController extends AbstractController {

    use RequestWithStudentIdTrait;
    
    public function doGet(HttpResponseInterface $response, HttpRequestInterface $request) {
        // Just render the login form.
        $this->renderTemplate('t_login');
    }

    public function doPost(HttpResponseInterface $response, HttpRequestInterface $request) {
        $password = new ProtectedString($request->getParam(CmnCnst::URL_PARAM_LOGIN_PASSWORD));
        if (empty($password)) {
            $response->addMessage(Message::warningI18n('login.failure', 'login.userorpass.missing', $this->getTranslator()));
            $this->renderTemplate('t_login');
            return;
        }
        $user = $this->retrieveUser($response, $request, $this, $this);
        if ($user === null || !$user->verifyPassword($password)) {
            $response->addMessage(Message::warningI18n('login.failure', 'login.userorpass.invalid', $this->getTranslator()));
            $this->renderTemplate('t_login');
            return;
        }
        // Now the user is authenticated.
        $this->getSessionHandler()->newSession($user);
        $redirectUrl = $request->getParam(CmnCnst::URL_PARAM_REDIRECT_URL,
                $this->getContext()->getServerPath(CmnCnst::PATH_DASHBOARD));
        $response->setRedirect($redirectUrl);
        $this->renderTemplate('t_login_success');
    }
    
    protected function getRequiresLogin() : int {
        return self::REQUIRE_LOGIN_NEVER;
    }
}