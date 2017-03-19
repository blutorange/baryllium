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

require_once '../../private/bootstrap.php';

use Controller\AbstractController;
use Dao\AbstractDao;
use DateTime;
use Doctrine\DBAL\Types\ProtectedString;
use Entity\User;
use Extension\CampusDual\CampusDualLoader;
use Ui\Message;

/**
 * Performs registration for a normal user account.
 *
 * @author madgaksha
 */
class Login extends AbstractController {

    public function doGet() {
        // Render form.
        $this->renderTemplate('t_login');
    }

    public function doPost() {
        $this->getSessionHandler()->ensureSessionClosed();
        $studentId = User::extractStudentId($this->getParam('studentid'));
        $password = new ProtectedString($this->getParam('password'));
        if (empty($studentId) || empty($password)) {
            $this->addMessage(Message::warningI18n('login.failure', 'login.userorpass.missing', $this->getTranslator()));
            $this->renderTemplate('t_login');
            return;
        }
        $user = AbstractDao::user($this->getEm())->findOneByStudentId($studentId);
        if ($user === null || !$user->verifyPassword($password)) {
            $this->addMessage(Message::warningI18n('login.failure', 'login.userorpass.invalid', $this->getTranslator()));
            $this->renderTemplate('t_login');
            return;
        }
        // Authenticated!!!
        $this->getSessionHandler()->newSession($user, $lang);
        $this->redirect('./userprofile.php');
        $this->renderTemplate('t_login_success');
    }
}

(new Login())->process();