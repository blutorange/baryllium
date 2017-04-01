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

namespace Moose\Controller;

use Moose\Dao\AbstractDao;
use DateTime;
use Doctrine\DBAL\Types\ProtectedString;
use Moose\Entity\User;
use Moose\Web\HttpRequestInterface;
use Moose\Web\HttpResponseInterface;
use Moose\ViewModel\MessageInterface;

class SetupAdminController extends BaseController {
    
    public function doGet(HttpResponseInterface $response, HttpRequestInterface $request) {
        if (!file_exists($this->getPhinxPath())) {
            $response->setRedirect("./setup.php");
            return;
        }
        if (AbstractDao::user($this->getEm())->findOneSiteAdmin() !== null) {
            $response->addMessage(MessageInterface::warningI18n('setup.sadmin.exists.message', 'setup.sadmin.exists.details', $this->getTranslator()));
        }
        $this->renderTemplate('t_setup_admin', ['formTitle' => 'setup.admin.account']);
    }

    public function doPost(HttpResponseInterface $response, HttpRequestInterface $request) {
        if (!file_exists($this->getPhinxPath())) {
            $response->setRedirect("./setup.php");
            return;
        }
        if (AbstractDao::user($this->getEm())->findOneSiteAdmin() !== null) {
            $response->addMessage(MessageInterface::warningI18n('setup.sadmin.exists.message', 'setup.sadmin.exists.details', $this->getTranslator()));
            $this->renderTemplate('t_setup_admin', ['formTitle' => 'setup.admin.account']);
            return;
        }
        $admin = new User();
        $admin->setIsSiteAdmin(true);
        $admin->setFirstName($request->getParam('firstname'));
        $admin->setLastName($request->getParam('lastname'));
        $admin->setRegDate(new DateTime());
        $admin->setActivationDate(new DateTime());
        $admin->generateIdenticon();
        $admin->setIsActivated(true);
        $admin->setMail($request->getParam('mail'));
        $admin->setPassword(new ProtectedString($request->getParam('password')));
        $errors = AbstractDao::generic($this->getEm())->persist($admin, $this->getTranslator());
        if (sizeof($errors) > 0) {
            $this->renderTemplate('t_setup_admin', ['formTitle' => 'setup.admin.account']);
            return;
        }
        $file = dirname(__FILE__, 4) . DIRECTORY_SEPARATOR . 'FIRST_INSTALL';
        if (!unlink($file)) {
            $response->addMessage(MessageInterface::infoI18n('setup.unlink.message', 'setup.unlink.details', $this->getTranslator(), ['name' => $file]));
        }
        $response->addMessage(MessageInterface::successI18n('setup.admin.sucess.message', 'setup.admin.sucess.detail', $this->getTranslator()));
        $response->setRedirect('./setup_import.php');
    }
    
    public function getPhinxPath() {
        return dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'config/phinx.yml';
    }
    
    protected function getRequiresLogin() : int {
        return self::REQUIRE_LOGIN_NEVER;
    }

}