<?php

namespace Controller;

require_once '../../private/bootstrap.php';

use Controller\AbstractController;
use Entity\User;
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
            $user->setActivateDate(new \DateTime());
            $user->generateActivateToken();
            $user->setAvatar(null);
            $user->setIsActivated(false);
            $user->setRegDate(new \DateTime());
            $user->setMail($this->getParam('mail'));
            $user->setPassword($this->getParam('password'));
            $errors = $user->getDao($this->getEm())->persist($user, $this->getTranslator(), true);
            if (sizeof($errors) === 0) {
                // Show confirmation
                $this->renderTemplate('register_success');
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
