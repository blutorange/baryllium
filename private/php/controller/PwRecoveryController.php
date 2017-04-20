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

use Moose\Dao\AbstractDao;
use Moose\Entity\ExpireToken;
use Moose\Entity\Mail;
use Moose\Entity\User;
use Moose\Util\CmnCnst;
use Moose\Util\MailUtil;
use Moose\ViewModel\Message;
use Moose\Web\HttpRequestInterface;
use Moose\Web\HttpResponseInterface;
use Moose\Web\RequestWithStudentIdTrait;

/**
 * Performs registration for a normal user account.
 *
 * @author madgaksha
 */
class PwRecoveryController extends BaseController {

    use RequestWithStudentIdTrait;
    
    public function doGet(HttpResponseInterface $response, HttpRequestInterface $request) {
        // Render form.
        $this->renderTemplate('t_pwrecovery');
    }

    public function doPost(HttpResponseInterface $response, HttpRequestInterface $request) {
        $user = $this->retrieveUser($response, $request, $this, $this);
        if ($user === null) {
            $response->addMessage(Message::warningI18n('error.validation', 'pwrecover.no.user', $this->getTranslator()));
            $this->renderTemplate('t_pwrecovery');
            return;
        }
        $dao = AbstractDao::generic($this->getEm());
        $expireToken = new ExpireToken(CmnCnst::LIFETIME_PWCHANGE);
        $expireToken->setDataEntity($user, "PWREC");
        $errors1 = MailUtil::queueMail($this->makeMail($user, $expireToken));
        $response->addMessages($errors1);
        if (\sizeof($errors1) > 0) {
            $this->renderTemplate('t_pwrecovery');
            return;
        }
        $errors2 = $dao->persist($expireToken, $this->getTranslator());
        $response->addMessages($errors2);
        if (\sizeof($errors2) > 0) {
            $this->renderTemplate('t_pwrecovery');
            return;
        }
        $response->addMessage(Message::successI18n('pwrecover.mail.success', 'pwrecover.mail.success.details', $this->getTranslator()));
        $this->renderTemplate('t_pwrecovery');
    }

    public function getRequiresLogin() : int {
        return self::REQUIRE_LOGIN_NEVER;
    }

    public function makeMail(User $user, ExpireToken $token) {
        //TODO Add a configuration option OUTWARD_SERVER in phinx.yml and use that.
        $resetLink =  $this->getRequest()->getHttpHost() . $this->getContext()->getServerPath(CmnCnst::PATH_PWRESET) . '?token=' . $token->fetch();
        $mail = (new Mail())
            ->setMailTo($user->getOtherOrStudentMail())
            ->setMailFrom($this->getContext()->getConfiguration()->getSystemMailAddress())
            ->setSubject($this->getTranslator()->gettext('pwrecover.mail.subject'))
            ->setContent($this->getTranslator()->gettextVar('pwrecover.mail.content', [
                'link' => $resetLink
            ]))
        ;
        return $mail;
    }
}
