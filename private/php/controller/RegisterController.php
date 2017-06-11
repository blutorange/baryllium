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
use LogicException;
use Moose\Context\Context;
use Moose\Context\MooseConfig;
use Moose\Dao\Dao;
use Moose\Entity\TutorialGroup;
use Moose\Entity\University;
use Moose\Entity\User;
use Moose\Extension\CampusDual\CampusDualException;
use Moose\Extension\CampusDual\CampusDualLoader;
use Moose\Util\CmnCnst;
use Moose\ViewModel\Message;
use Moose\Web\HttpRequestInterface;
use Moose\Web\HttpResponseInterface;
use Moose\Web\RequestWithStudentIdTrait;

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

        // For testing, we do not want to check with Campus Dual all the time.
        if ($request->getParam(CmnCnst::URL_PARAM_REGISTER_SKIP_CHECK) !== null && Context::getInstance()->getConfiguration()->isNotEnvironment(MooseConfig::ENVIRONMENT_PRODUCTION)) {
            $user = $this->makeTestUser($sid);
        }
        else {
            try {
                $user = $this->getDataFromCampusDual($sid, $passcdual);
            }
            catch (CampusDualException $e) {
                \error_log("Failed to fetch user from Campus Dual.");
                \error_log($e);
                $response->addMessage(Message::infoI18n('error.validation',
                    'register.campusdual.error', $this->getTranslator()));
                $this->renderTemplate('t_register');
                return;
            }
        }
               
        if ($this->persistUser($response, $user, new ProtectedString($password), $passcdual, $savePassCDual)) {
            $response->setRedirectRelative(CmnCnst::PATH_LOGIN_PAGE);
            $response->addRedirectUrlMessage('RegisterComplete', Message::TYPE_SUCCESS);
            $this->renderTemplate('t_register_success');
        }
        else {
            $this->renderTemplate('t_register');
        }
    }

    public function getDataFromCampusDual(string $studentId, ProtectedString $password) {
        /* @var $user User */
        $user = CampusDualLoader::perform($studentId, $password, function(CampusDualLoader $loader) {
            return $loader->getUser();
        });
        return $user;
    }

    public function getRequiresLogin() : int {
        return self::REQUIRE_LOGIN_NEVER;
    }
    
    private function persistUser(HttpResponseInterface $response, User $user,
            ProtectedString $password, ProtectedString $passCDual,
            bool $savePassCDual): bool {
        $dao = Dao::generic($this->getEm());
        $tut = $user->getTutorialGroup();
        $fos = $tut->getFieldOfStudy();
        
        // Check whether the field of study exists in the database.
        $fosReal = Dao::fieldOfStudy($this->getEm())->findOneByDisciplineAndSub($fos->getDiscipline(), $fos->getSubDiscipline());
        if ($fosReal === null) {
            $response->addMessage(Message::warningI18n(
                    'register.fos.notfound.message',
                    'register.fos.notfound.detail',
                    $this->getTranslator(),[
                        'discipline' => $fos->getDiscipline(),
                        'subdiscipline' => $fos->getSubDiscipline()
                    ]));
            return false;
        }
        
        // Check whether the university exists in the database.
        $universityReal = Dao::university($this->getEm())->findOneByIdentifier($tut->getUniversityIdentifier());
        if ($universityReal === null) {
            $response->addMessage(Message::warningI18n(
                'register.university.notfound.message',
                'register.university.notfound.detail',
                $this->getTranslator(), [
                    'identifier' => $tut->getUniversityIdentifier()
                ]
            ));
            return false;
        }

        // Check whether the tutorial group exists in the database, or create it.
        $tutReal = Dao::tutorialGroup($this->getEm())->findByAll($universityReal->getId(), $tut->getYear(), $tut->getIndex(), $fosReal);        
        // Create new tutorial group if necessary.
        if ($tutReal === null) {
            $tutReal = $tut;
        }
        
        // Update the associations with the real entities.
        $user->setTutorialGroup($tutReal);
        $tutReal->setFieldOfStudy($fosReal);
        $tutReal->setUniversity($universityReal);

        // Save all the users's data.
        $dao->queue($tutReal);
        $dao->queue($fosReal);
        $dao->queue($user);
        
        // Update all user data.
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
        
        // Validate the user.
        $errors = $dao->persistQueue($this->getTranslator());
        $response->addMessages($errors);
        
        return \sizeof($errors) === 0;
    }

    private function makeTestUser(string $studentId) {
        /* @var $uniList University[] */
        $user = new User();
        $tutGroup = new TutorialGroup();
        $fosList = Dao::fieldOfStudy($this->getEm())->findAll();
        $uniList = Dao::university($this->getEm())->findAll();
        if (\sizeof($fosList) < 1) {
            throw new LogicException('Cannot acquire field of study, there are none.');
        }
        if (\sizeof($uniList) < 1) {
            throw new LogicException('Cannot acquire university, there are none.');
        }
        $fos = $fosList[\rand(0, \sizeof($fosList)-1)];
        $user->setFirstName('Test');
        $user->setLastName('User ' . (string)rand(0,999));
        $user->setStudentId($studentId);
        $user->setTutorialGroup($tutGroup);
        $tutGroup->setIndex(rand(1, 9));
        $tutGroup->setUniversityIdentifier($uniList[\array_rand($uniList)]->getIdentifier());
        $tutGroup->setYear(rand(2000,2020));
        $tutGroup->setFieldOfStudy($fos);
        return $user;
    }

}
