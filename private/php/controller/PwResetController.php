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
use Moose\Dao\AbstractDao;
use Moose\Entity\User;
use Moose\Util\CmnCnst;
use Moose\ViewModel\Message;
use Moose\Web\HttpRequestInterface;
use Moose\Web\HttpResponseInterface;
use Moose\Web\RequestWithStudentIdTrait;

/**
 * Performs registration for a normal user account.
 *
 * @author madgaksha
 */
class PwResetController extends BaseController {

    use RequestWithStudentIdTrait;
    
    public function doGet(HttpResponseInterface $response, HttpRequestInterface $request) {
        // Render form.
        $this->renderTemplate('t_pwreset');
    }

    public function doPost(HttpResponseInterface $response, HttpRequestInterface $request) {
        $token = $request->getParam(CmnCnst::URL_PARAM_TOKEN);
        $password = $request->getParam(CmnCnst::URL_PARAM_PASSWORD);
        $passwordRepeat = $request->getParam(CmnCnst::URL_PARAM_PASSWORD_REPEAT);
        
        if ($token === null) {
            $response->addMessage(Message::warningI18n('error.validation',
                    'pwreset.no.token', $this->getTranslator()));
            $this->renderTemplate('t_pwreset');
            return;
        }
        
        if ($password === null || $passwordRepeat === null || $password !== $passwordRepeat) {
            $response->addMessage(Message::warningI18n('error.validation',
                    'pwreset.no.pass', $this->getTranslator()));
            $this->renderTemplate('t_pwreset');
            return;
        }
        
        $expireToken =AbstractDao::expireToken($this->getEm())->findOneByToken($token);
        if ($expireToken === null) {
            // Token does not exist (anymore).
            $response->addMessage(Message::warningI18n('error.validation',
                    'pwreset.token.notfound', $this->getTranslator()));    
            $this->renderTemplate('t_pwreset');
            return;
        }
        
        if (!$expireToken->checkAndInvalidate($this->getEm())) {
            // Token expired.
            $response->addMessage(Message::warningI18n('error.validation',
                    'pwreset.token.expired', $this->getTranslator()));            
            $this->renderTemplate('t_pwreset');
            return;
        }
        
        /* @var $user User */
        $user = $expireToken->getDataEntity($this->getEm(), User::class);
        
        if ($user === null) {
            // Something went wrong, error when generating the token?. Bad request?
            $response->addMessage(Message::warningI18n('error.validation',
                    'pwreset.token.invalid', $this->getTranslator()));
            $this->renderTemplate('t_pwreset');
            return;
        }
        
        $user->setPassword(new ProtectedString($password));
        AbstractDao::generic($this->getEm())->persist($user, $this->getTranslator());
        
        $response->setRedirect($this->getContext()->getServerPath(CmnCnst::PATH_LOGIN_PAGE) . '?' . http_build_query([
                CmnCnst::URL_PARAM_SYSTEM_MESSAGE => 'PwresetComplete:success'
        ]));
    }

    public function getRequiresLogin() : int {
        return self::REQUIRE_LOGIN_NEVER;
    }
}