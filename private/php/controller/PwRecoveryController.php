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
use Moose\Dao\Dao;
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
        $dao = Dao::generic($this->getEm());
        $expireToken = ExpireToken::create(CmnCnst::LIFETIME_PWCHANGE)->setDataEntity($user, "PWREC");
        $mailList = $this->makeMail($user, $expireToken,$expireToken->withChallenge());
        if (empty($mailList)) {
            $response->addMessage(Message::warningI18n('request.illegal', 'pwrecover.no.mail', $this->getTranslator()));
            $this->renderTemplate('t_pwrecovery');
            return;
        }
        foreach ($mailList as $mail) {
            $errors1 = MailUtil::queueMail($mail);
            $response->addMessages($errors1);
            if (\sizeof($errors1) > 0) {
                $this->renderTemplate('t_pwrecovery');
                return;
            }
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

    /** @return Mail[] */
    private function makeMail(User $user, ExpireToken $token, ProtectedString $challenge) : array {
        //TODO Add a configuration option OUTWARD_SERVER in phinx.yml and use that.
        $resetLink =  $this->getRequest()->getScheme() . '://'
                . $this->getRequest()->getHttpHost()
                . $this->getContext()->getServerPath(CmnCnst::PATH_PWRESET)
                . '?' . CmnCnst::URL_PARAM_TOKEN . '=' . $token->fetch()
                . '&' .CmnCnst::URL_PARAM_CHALLENGE . '=' . $challenge->getString();
        $from = $this->getContext()->getConfiguration()->getSystemMailAddress();
        $subject = $this->getTranslator()->gettext('pwrecover.mail.subject');
        $content = $this->getTranslator()->gettextVar('pwrecover.mail.content', [
            'link' => $resetLink
        ]);
        return \array_map(function(string $mail) use ($subject, $content, $from){
            return Mail::make()
            ->setMailTo($mail)
            ->setMailFrom($from)
            ->setSubject($subject)
            ->setContent($content);
        }, $user->getAllAvailableMail());
    }
}
