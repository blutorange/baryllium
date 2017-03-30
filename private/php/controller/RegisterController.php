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

use Dao\AbstractDao;
use DateTime;
use Doctrine\DBAL\Types\ProtectedString;
use Entity\User;
use Extension\CampusDual\CampusDualLoader;
use Moose\Controller\AbstractController;
use Moose\Web\HttpRequestInterface;
use Moose\Web\HttpResponseInterface;
use Moose\Web\RequestWithStudentIdTrait;
use Ui\Message;

/**
 * Performs registration for a normal user account.
 *
 * @author madgaksha
 */
class RegisterController extends BaseController {

    use RequestWithStudentIdTrait;
    
    public function doGet(HttpResponseInterface $response, HttpRequestInterface $request) {
        // Render form.
        $this->renderTemplate('t_register');
    }

    public function doPost(HttpResponseInterface $response, HttpRequestInterface $request) {
        $agb = $request->getParamBool('agb');
        if (!$agb) {
            // Terms and conditions not accepted, render registration form again.
            $response->addMessage(Message::infoI18n('error.validation',
                            'register.agb.declined', $this->getTranslator()));
            $this->renderTemplate('t_register');
            return;
        }

        $savePassCDual = $request->getParamBool('savecd');
        $sid = $this->retrieveStudentId($response, $request, $this, false);
        $passcdual = new ProtectedString($request->getParam('passwordcdual'));
        if ($sid === null || empty($passcdual->getString())) {
            $response->addMessage(Message::infoI18n('error.validation',
                            'register.cdual.missing', $this->getTranslator()));
            $this->renderTemplate('t_register');
            return;
        }

        $password = $request->getParam('password');
        if (empty($password)) {
            $response->addMessage(Message::infoI18n('error.validation',
                            'register.password.missing', $this->getTranslator()));
            $this->renderTemplate('t_register');
            return;            
        }
        
        try {
            $user = $this->getDataFromCampusDual($sid, $passcdual);
        }
        catch (\Extension\CampusDualException $e) {
            $response->addMessage(Message::infoI18n('error.validation',
                'register.campusdual.error', $this->getTranslator()));
            $this->renderTemplate('t_register');
            return;
        }
        
        if ($this->persistUser($response, $user, new ProtectedString($password), $passcdual, $savePassCDual)) {
            $response->setRedirect('./login.php');
            $this->renderTemplate('t_register_success');
        }
        else {
            $this->renderTemplate('t_register');
        }
    }

    public function getDataFromCampusDual(string $studentId, ProtectedString $password) {
        $user = CampusDualLoader::perform($studentId, $password, function(CampusDualLoader $loader) {
            return $loader->getUser();
        });
        return $user;
    }

    public function getRequiresLogin() : int {
        return self::REQUIRE_LOGIN_NEVER;
    }
    
    public function persistUser(HttpResponseInterface $response, User $user,
            ProtectedString $password, ProtectedString $passCDual,
            bool $savePassCDual): bool {
        $dao = AbstractDao::generic($this->getEm());
        $tut = $user->getTutorialGroup();
        $fos = $tut->getFieldOfStudy();

        $fosReal = AbstractDao::fieldOfStudy($this->getEm())->findOneByDisciplineAndSub($fos->getDiscipline(), $fos->getSubDiscipline());
        if ($fosReal === null) {
            $response->addMessage(Message::warningI18n('register.fos.notfound.message', 'register.fos.notfound.detail', $this->getTranslator()));
            return false;
        }

        $tutReal = AbstractDao::tutorialGroup($this->getEm())->findByAll($tut->getUniversity(), $tut->getYear(), $tut->getIndex(), $fosReal);        
        if ($tutReal === null) {
            $tutReal = $tut;
        }
        
        $user->setTutorialGroup($tutReal);
        $tutReal->setFieldOfStudy($fosReal);
        $dao->queue($tutReal);
        $dao->queue($fosReal);
        $dao->queue($user);
        
        $user->generateIdenticon();
        $user->setIsActivated(true);
        $user->setIsSiteAdmin(false);
        $user->setIsFieldOfStudyAdmin(false);
        $user->setPassword($password);
        $user->setRegDate(new DateTime());
        $user->setActivationDate(new DateTime());
        if ($savePassCDual) {
            $user->setPasswordCampusDual($passCDual);
        }
        
        $errors = $dao->persistQueue($this->getTranslator());
        $response->addMessages($errors);
        
        return \sizeof($errors) === 0;
    }
}
