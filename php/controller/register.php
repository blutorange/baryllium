<?php

namespace Controller;

use Controller\AbstractController;

require_once '../../bootstrap.php';

/**
 * Description of Register
 *
 * @author madgaksha
 */
class Register extends AbstractController {
    public function doGet() {
        // Render form.
        echo $this->getEngine()->render('register', ['action' => $_SERVER['PHP_SELF']]);
    }
    
    public function doPost() {
        $user = new \Entity\User();
        $user->setUsername($this->getParam('username'));
        $user->setPassword($this->getParam('password'));
        $errors = $user->persist($this->getEm(), $this->getSessionHandler()->getLang());
        if (sizeof($errors) === 0) {
            // Show confirmation
            $this->getEm()->flush();
            echo $this->getEngine()->render('register_success');
        }
        else {
            // Render form.
            echo $this->getEngine()->render('register', ['action' => $_SERVER['PHP_SELF'], 'errors' => $errors]);
        }
    }
}

(new Register())->process();
?>