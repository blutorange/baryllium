<?php

namespace Controller;

use Controller\AbstractController;
use DateTime;
use Doctrine\DBAL\Types\ProtectedString;
use Entity\User;
use Ui\Message;

require_once '../../bootstrap.php';

class SetupUserController extends AbstractController {
    
    public function doGet() {
        if (!file_exists($this->getPhinxPath())) {
            $this->renderTemplate('t_setup');
            return;
        }
        $this->renderTemplate('t_register', ['registerFormTitle' => 'setup.admin.account']);
    }

    public function doPost() {
        if (!file_exists($this->getPhinxPath())) {
            $this->renderTemplate('t_setup');
            return;
        }
        $agb = $this->getParamBool('agb');
        if (!$agb) {
            // Terms and conditions not accepted, render registration form again.
            $this->addMessage(Message::infoI18n('error.validation',
                            'register.agb.declined', $this->getTranslator()));
            $this->renderTemplate('t_register');
            return;
        }
        $admin = new User();
        $admin->setIsFieldOfStudyAdmin(true);
        $admin->setFirstName($this->getParam('firstname'));
        $admin->setLastName($this->getParam('lastname'));
        $admin->setUserName($this->getParam('username'));
        $admin->setActivationDate(new DateTime());
        $admin->setRole($this->getParam('role'));
        $admin->generateIdenticonFromUsername();
        $admin->setIsActivated(true);
        $admin->setRegDate(new DateTime());
        $admin->setMail($this->getParam('mail'));
        $admin->setPassword(new ProtectedString($this->getParam('password')));
        $errors = $admin->getDao($this->getEm())->persist($admin, $this->getTranslator());
        if (sizeof($errors) > 0) {
            $this->renderTemplate('t_register', ['registerFormTitle' => 'setup.admin.account']);
            return;
        }
        $file = dirname(__FILE__, 4) . DIRECTORY_SEPARATOR . 'FIRST_INSTALL';
        if (!unlink($file)) {
            $this->addMessage(Message::infoI18n('setup.unlink.message', 'setup.unlink.details', $this->getTranslator(), ['name' => $file]));
        }
        $this->renderTemplate('t_register_success');
    }
    
    public function getPhinxPath() {
        return dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'config/phinx.yml';
    }

}
$file = dirname(__FILE__, 4) . DIRECTORY_SEPARATOR . 'FIRST_INSTALL';

if (file_exists($file)) {
    (new SetupUserController())->process();
}
else {
    echo "Create file $file to run the setup guide.";
}