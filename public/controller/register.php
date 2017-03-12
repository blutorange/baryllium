<?php

namespace Controller;

use Controller\AbstractController;
use \Entity\User;

require_once '../../private/bootstrap.php';

/**
 * Description of Register
 *
 * @author madgaksha
 */
class Register extends AbstractController {
    public function doGet() {
        // Render form.
        $this->renderPortal('register');
    }
    
    public function doPost() {
        $user = new User();
        $user->setUsername($this->getParam('username'));
        $user->setPassword($this->getParam('password'));
        $errors = $user->persist($this->getEm(), $this->getSessionHandler()->getLang());
        if (sizeof($errors) === 0) {
            // Show confirmation
            $this->getEm()->flush();
            $this->renderPortal('register_success');
        }
        else {
            // Render form.
            $this->addMessages($errors);
            $this->renderPortal('register', ['action' => $_SERVER['PHP_SELF']]);
        }
    }
}

(new Register())->process();
?>