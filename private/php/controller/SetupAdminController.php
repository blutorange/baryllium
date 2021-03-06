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

use DateTime;
use Doctrine\DBAL\Types\ProtectedString;
use Moose\Dao\Dao;
use Moose\Entity\User;
use Moose\ViewModel\Message;
use Moose\Web\HttpRequestInterface;
use Moose\Web\HttpResponseInterface;

class SetupAdminController extends BaseController {
    
    public function doGet(HttpResponseInterface $response, HttpRequestInterface $request) {
        if (!file_exists($this->getPhinxPath())) {
            $response->setRedirect("./setup.php");
            return;
        }
        if (Dao::user($this->getEm())->findOneSiteAdmin() !== null) {
            $response->addMessage(Message::warningI18n('setup.sadmin.exists.message', 'setup.sadmin.exists.details', $this->getTranslator()));
        }
        $this->renderTemplate('t_setup_admin', ['formTitle' => 'setup.admin.account']);
    }

    public function doPost(HttpResponseInterface $response, HttpRequestInterface $request) {
        if (!file_exists($this->getPhinxPath())) {
            $response->setRedirect("./setup.php");
            return;
        }
        if (Dao::user($this->getEm())->findOneSiteAdmin() !== null) {
            $response->addMessage(Message::warningI18n('setup.sadmin.exists.message', 'setup.sadmin.exists.details', $this->getTranslator()));
            $this->renderTemplate('t_setup_admin', ['formTitle' => 'setup.admin.account']);
            return;
        }
        $admin = User::create()
            ->setIsSiteAdmin(true)
            ->setFirstName($request->getParam('firstname'))
            ->setLastName($request->getParam('lastname'))
            ->setRegDate(new DateTime())
            ->setActivationDate(new DateTime())
            ->generateIdenticon()
            ->setIsActivated(true)
            ->setMail($request->getParam('mail'))
            ->setPassword(new ProtectedString($request->getParam('password')));
        $errors = Dao::generic($this->getEm())
                ->queue($admin)
                ->queue($admin->getUserOption())
                ->persistQueue($this->getTranslator());
        if (sizeof($errors) > 0) {
            $response->addMessages($errors);
            $this->renderTemplate('t_setup_admin', [
                'formTitle' => 'setup.admin.account'
            ]);
            return;
        }
        $response->addMessage(Message::successI18n('setup.admin.sucess.message', 'setup.admin.sucess.detail', $this->getTranslator()));
        $response->setRedirect(\Moose\Context\Context::getInstance()->getServerPath(\Moose\Util\CmnCnst::PATH_IMPORT_FOS));
    }
    
    public function getPhinxPath() {
        return dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'config/phinx.yml';
    }
    
    protected function getRequiresLogin() : int {
        return self::REQUIRE_LOGIN_NEVER;
    }

}