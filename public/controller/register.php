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
        $user->setUsername($this->getParam('username'));
        $user->setPassword($this->getParam('password'));
        $user->setMail($this->getParam('mail'));
        $user->setRole($this->getParam('role'));
        $errors = $user->getDao($this->getEm())->persist($user, $this->getTranslator(), false);
        if (sizeof($errors) === 0) {
            // Show confirmation
            $this->renderTemplate('register_success');
        }
        else {
            // Render registration form again.
            $this->addMessages($errors);
            $this->renderTemplate('register', ['action' => $_SERVER['PHP_SELF']]);
        }
    }
}

(new Register())->process();