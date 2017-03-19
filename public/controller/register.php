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
use Entity\FieldOfStudy;
use Entity\TutorialGroup;
use Entity\User;
use Extension\CampusDual\CampusDualLoader;
use Ui\Message;

/**
 * Performs registration for a normal user account.
 *
 * @author madgaksha
 */
class Register extends AbstractController {

    public function doGet() {
        // Render form.
        $this->renderTemplate('t_register');
    }

    public function doPost() {
        $agb = $this->getParamBool('agb');
        if (!$agb) {
            // Terms and conditions not accepted, render registration form again.
            $this->addMessage(Message::infoI18n('error.validation',
                            'register.agb.declined', $this->getTranslator()));
            $this->renderTemplate('t_register');
            return;
        }

        $sid = User::extractStudentId($this->getParam('studentid'));
        $passcdual = new ProtectedString($this->getParam('passwordcdual'));
        if (empty($sid) || empty($passcdual->getString())) {
            $this->addMessage(Message::infoI18n('error.validation',
                            'register.cdual.missing', $this->getTranslator()));
            $this->renderTemplate('t_register');
            return;
        }

        $password = $this->getParam('password');
        if (empty($password)) {
            $this->addMessage(Message::infoI18n('error.validation',
                            'register.password.missing', $this->getTranslator()));
            $this->renderTemplate('t_register');
            return;            
        }
        
        $user = $this->makeDebugUser();//$this->getDataFromCampusDual($sid, $passcdual);
        
        if ($this->persistUser($user, new ProtectedString($password), $passcdual)) {
            $this->redirect('./login.php');
            $this->renderTemplate('t_register_success');
        }
        else {
            $this->renderTemplate('t_register');
        }
    }

    public function getDataFromCampusDual(string $studentId, string $password) {
        $user = CampusDualLoader::perform($studentId, $password, function(CampusDualLoader $loader) {
            return $loader->getUser();
        });
        return $user;
    }

    public function persistUser(User $user, ProtectedString $password, ProtectedString $passCDual) : bool {
        $dao = AbstractDao::generic($this->getEm());
        $tut = $user->getTutorialGroup();
        $fos = $tut->getFieldOfStudy();

        $fosReal = AbstractDao::fieldOfStudy($this->getEm())->findByDisciplineAndSub($fos->getDiscipline(), $fos->getSubDiscipline());
        if ($fosReal === null) {
            $this->addMessage(Message::warningI18n('register.fos.notfound.message', 'register.fos.notfound.detail', $this->getTranslator()));
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
        $user->setPasswordCampusDual($passCDual);
       
        $errors = $dao->persistQueue($this->getTranslator());
        $this->addMessages($errors);
        
        return sizeof($errors) === 0;
        
    }

    public function makeDebugUser() : User {
        $user = new User();
        $user->setFirstName("Thomas");
        $user->setLastName("Eden"); 
        $user->setStudentId("1111111");
        $fos = new FieldOfStudy();
        $fos->setDiscipline("Medieninformatik");
        $fos->setSubDiscipline("Medieninformatik");
        $tut = new TutorialGroup();
        $tut->setIndex(1);
        $tut->setYear(2015);
        $tut->setUniversity(3);
        $tut->setFieldOfStudy($fos);
        $user->setTutorialGroup($tut);
        return $user;
    }

}

(new Register())->process();