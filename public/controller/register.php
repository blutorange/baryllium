<?php

namespace Controller;

require_once '../../private/bootstrap.php';

use Controller\AbstractController;
use \Entity\User;

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
        $user->setAGB($this->getParam('agb'));
        if ($user->getAGB()) {
        $user->setFirstname($this->getParam('firstname'));
        $user->setLastname($this->getParam('lastname'));
        $user->setUsername($this->getParam('username'));
        $user->setActivatedate("");
        $user->setActivatetoken();
        $user->setAvatar("");
        $user->setIsActivated(FALSE);
        $user->setRegdate(time());
        $user->setMail($this->getParam('mail'));
        $user->setPassword($this->getParam('password'));
        $errors = $user->getDao($this->getEm())->persist($user, $this->getTranslator(), true);
       // array_push($errors, "AGB:" . $user->getAGB());
        if (sizeof($errors) === 0) {
            // Show confirmation
            $this->renderTemplate('register_success');
        }
        else {
            // Render registration form again.
            $this->addMessages($errors);
            $this->renderTemplate('register', ['action' => $_SERVER['PHP_SELF']]);
        }
        }else{
            // Need help from Andre.
            // I want to redirect to register.php with the massage, that the AGB´s mussted be checked.
            // $this->addMessages(array("HAllo." , "Du"));
            $this->renderTemplate('register');
        }
    }
}

(new Register())->process();