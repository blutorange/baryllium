<?php

namespace Controller;

require_once '../../private/bootstrap.php';

use Controller\AbstractController;
use DateTime;
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
        $user->setFirstName($this->getParam('firstname'));
        $user->setLastName($this->getParam('lastname'));
        $user->setUserName($this->getParam('username'));
        $user->setActivationDate(null);
        $user->setRole($this->getParam('role'));
        $user->generateIdenticonFromUsername();
        $user->setIsActivated(false);
        $user->setRegDate(new DateTime());
        $user->setMail($this->getParam('mail'));
        $user->setPassword($this->getParam('password'));
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