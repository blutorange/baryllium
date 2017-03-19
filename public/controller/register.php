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

namespace Controller;

require_once '../../private/bootstrap.php';

use Controller\AbstractController;
use DateTime;
use Doctrine\DBAL\Types\ProtectedString;
use Entity\Mail;
use Entity\User;
use Nette\Mail\SendException;
use Nette\Mail\SendmailMailer;
use Ui\Message;

/**
 * Performs registration for a normal user account.
 *
 * @author madgaksha
 */
class Register extends AbstractController {

    public function doGet() {
        // Render form.
        $this->renderTemplate('t_register');
    }

    public function doPost() {
        $agb = $this->getParamBool('agb');
        if (!$agb) {
            // Terms and conditions not accepted, render registration form again.
            $this->addMessage(Message::infoI18n('error.validation',
                            'register.agb.declined', $this->getTranslator()));
            $this->renderTemplate('t_register');
            return;
        }

        $user = $this->makeUser();
        $errorsUser = $user->getDao($this->getEm())->persist($user,
                $this->getTranslator(), false);

        if (sizeof($errorsUser) > 0) {
            // Render registration form again.
            $this->addMessages($errorsUser);
            $this->renderTemplate('t_register');
            return;
        }

        // Create mail and check.
        $mail = $this->makeMail($user);
        $errorsMail = $mail->getDao($this->getEm())->persist($mail,
                $this->getTranslator(), false);
        if (sizeof($errorsMail) > 0) {
            // Render registration form again.
            $this->addMessages($errorsMail);
            $this->renderTemplate('t_register');
            return;
        }

        // Send mail
        $mailer = new SendmailMailer();
        try {
            $mailer->send($mail->toNetteMail());
            $mail->setIsSent(true);
            $this->getEm()->persist($mail);
        }
        catch (SendException $e) {
            error_log('Failed to send mail: ' . $e);
            $this->addMessage(Message::infoI18n('register.mail.failed.message', 'register.mail.failed.detail', $this->getTranslator()));
        }

        // Show confirmation
        $this->renderTemplate('t_register_success');
    }

    private function makeUser(): User {
        $user = new User();
        $user->setActivationDate(null);
        $user->generateIdenticonFromUsername();
        $user->setIsActivated(false);
        $user->setRegDate(new DateTime());
        $user->setPassword(new ProtectedString($this->getParam('password')));
        return $user;
    }

    private function makeMail(User $user): Mail {
        $mail = new Mail();
        $mail->setMailFrom($this->getContext()->getSystemMailAddress());
        $mail->setIsHtml(false);
        $mail->setIsSent(false);
        $mail->setMailTo($user->getMail());
        $mail->setMailFrom();
        $mail->setSentDate($user->getRegDate());
        $mail->setSubject($this->getTranslator()->gettext('mail.register.subject'));
        $mail->setContent($this->getTranslator()->gettextVar('mail.register.content',
                        ['firstName' => $user->getFirstName(), 'lastName' => $user->getLastName()]));
        return $mail;
    }

}

(new Register())->process();