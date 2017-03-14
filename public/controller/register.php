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

        $user = new User();
        $user->setAGB($this->getParamBool('agb'));
        if ($user->getAGB()) {
            $user->setFirstName($this->getParam('firstname'));
            $user->setLastName($this->getParam('lastname'));
            $user->setUserName($this->getParam('username'));
            $user->setActivateDate(null);
            $user->generateActivateToken();
            $user->generateIdenticonFromUsername();
            $user->setIsActivated(false);
            $user->setRegDate(new \DateTime());
            $user->setMail($this->getParam('mail'));
            $user->setPassword($this->getParam('password'));
            $errors = $user->getDao($this->getEm())->persist($user, $this->getTranslator(), true);
            if (sizeof($errors) === 0) {
                $mail = new Mail();
                // TODO:
                // in productiv-system: Send EMail and set this var to true. 
                $mail->setIsSent(false);
                $sMail = $mail->setReceiverMail($user->getMail());
                $mail->setSentDate($user->getRegDate());
                $sSubject = $mail->setSubject(gettext('mail.register.subject'));
                $sContent = $mail->setContent(gettext('mail.register.content'));
                $errors = $mail->getDao($this->getEm())->persist($mail, $this->getTranslator(), true);
                if (sizeof($errors) === 0) {
                    // Send mail
                    // TODO: eine Tabelle in der Datenbank anlegen, in der Configurationne hinterlegt sind
                    // So zum Beispiel auch die Absender-EMailAdresse des Systems (vielleicht die E-Mail des Sideadmins?)
                    // $sFrom = "s3002549@rz.ba-dresden.de";
                    // mail($sMail, $sSubject, $sContent, $sFrom);
                    
                    // Show confirmation                    
                    $this->renderTemplate('register_success');
                } else {
                    // Render registration form again.
                    $this->addMessages($errors);
                    $this->renderTemplate('register');
                }
            } else {
                // Render registration form again.
                $this->addMessages($errors);
                $this->renderTemplate('register');
            }
        } else {
            // TODO i18n
            // Render registration form again.
            $this->addMessage(Message::infoI18n('error.validation', 'register.agb.declined', $this->getTranslator()));
            $this->renderTemplate('register');
        }
    }

}

(new Register())->process();
