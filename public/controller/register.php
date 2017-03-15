<?php

namespace Controller;

require_once '../../private/bootstrap.php';

use Controller\AbstractController;
use Entity\User;
use Entity\Mail;
use Ui\Message;

/**
 * Description of Register
 *
 * @author madgaksha
 */
class Register extends AbstractController {

    public function doGet() {
        // Render form.
        $this->renderTemplate('register');
    }

    public function doPost() {
        $agb = $this->getParamBool('agb');
        if (!$agb) {
            // Terms and conditions not accepted, render registration form again.
            $this->addMessage(Message::infoI18n('error.validation',
                            'register.agb.declined', $this->getTranslator()));
            $this->renderTemplate('register');
            return;
        }

        $user = $this->makeUser();
        $errorsUser = $user->getDao($this->getEm())->persist($user,
                $this->getTranslator(), false);

        if (sizeof($errorsUser) > 0) {
            // Render registration form again.
            $this->addMessages($errorsUser);
            $this->renderTemplate('register');
            return;
        }

        $mail = $this->makeMail($user);
        $errorsMail = $mail->getDao($this->getEm())->persist($mail,
                $this->getTranslator(), false);
        if (sizeof($errorsMail) > 0) {
            // Render registration form again.
            $this->addMessages($errorsMail);
            $this->renderTemplate('register');
            return;
        }

        // Send mail
        // TODO: eine Tabelle in der Datenbank anlegen, in der Configurationne hinterlegt sind
        // So zum Beispiel auch die Absender-EMailAdresse des Systems (vielleicht die E-Mail des Sideadmins?)
        // $sFrom = "s3002549@rz.ba-dresden.de";
        $successMail = mail($mail->getMailTo(), $mail->getSubject(),
                $mail->getContent(), "me@example.com");

        if ($successMail) {
            $mail->setIsSent(true);
            $this->getEm()->persist($mail);
        }

        // TODO what to do when the mail could not be sent??? Probably a send mail again / change mail page after login.
        // Show confirmation                    
        $this->renderTemplate('register_success');
    }

    private function makeUser(): User {
        $user = new User();
        $user->setFirstName($this->getParam('firstname'));
        $user->setLastName($this->getParam('lastname'));
        $user->setUserName($this->getParam('username'));
        $user->setActivationDate(null);
        $user->setRole($this->getParam('role'));
        $user->generateActivateToken();
        $user->generateIdenticonFromUsername();
        $user->setIsActivated(false);
        $user->setRegDate(new \DateTime());
        $user->setMail($this->getParam('mail'));
        $user->setPassword($this->getParam('password'));
        return $user;
    }

    private function makeMail(User $user): Mail {
        $mail = new Mail();
        // TODO:
        // in productive system send EMail and set this var to true. 
        $mail->setIsSent(false);
        $mail->setMailTo($user->getMail());
        $mail->setSentDate($user->getRegDate());
        $mail->setSubject($this->getTranslator()->gettext('mail.register.subject'));
        $mail->setContent($this->getTranslator()->gettextVar('mail.register.content',
                        ['userName' => $user->getUserName(), 'activationToken' => $user->getActivationToken()]));
        return $mail;
    }
}

(new Register())->process();
