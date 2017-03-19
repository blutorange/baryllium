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