<?php

/* The 3-Clause BSD License
 * 
 * SPDX short identifier: BSD-3-Clause
 *
 * Note: This license has also been called the "New BSD License" or "Modified
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

namespace Moose\Servlet;

use Doctrine\DBAL\Types\ProtectedString;
use Moose\Dao\Dao;
use Moose\Entity\User;
use Moose\Extension\CampusDual\CampusDualException;
use Moose\Extension\CampusDual\CampusDualLoader;
use Moose\Util\CmnCnst;
use Moose\Util\PermissionsUtil;
use Moose\ViewModel\Message;
use Moose\ViewModel\UserPermissionFacet;
use Moose\Web\HttpResponse;
use Moose\Web\RequestException;
use Moose\Web\RequestWithPaginable;
use Moose\Web\RequestWithUserTrait;
use Moose\Web\RestRequestInterface;
use Moose\Web\RestResponseInterface;

/**
 * For manipulating (forum) threads.
 *
 * @author madgaksha
 */
class UserServlet extends AbstractEntityServlet {
    
    use RequestWithPaginable;
    use RequestWithUserTrait;   

    const FIELDS_LIST_SORT = ['regDate', 'firstName', 'lastName', 'studentId', 'tutorialGroup'];
    const FIELDS_LIST_SEARCH = [
        'firstName' => 'like',
        'lastName' => 'like',
        'studentId' => 'like',
        'tutorialGroup' => '='
    ];
    const FIELDS_LIST_ACCESS = ['registrationDate', 'firstName', 'lastName', 'tutorialGroup', 'avatar', 'studentId', 'id'];

    protected function patchChangeMail(RestResponseInterface $response, RestRequestInterface $request) {
        /* @var $user User */
        /* @var $dbUser User*/
        $entities = $this->getEntities(User::class, ['id', 'mail']);
        if (\sizeof($entities) < 1) {
            return;
        }
        $dao = Dao::user($this->getEm());
        $count = 0;
        $errors = [];
        foreach ($entities as $user) {
            $dbUser = $dao->findOneById($user->getId());
            PermissionsUtil::assertUserForUser($dbUser , $this->getContext()->getUser());
            if ($dbUser->getMail() !== $user->getMail()) {
                $dbUser->setMail($user->getMail());
                ++$count;
            }
            if (!$dao->validateEntity($dbUser , $this->getTranslator(), $errors)) {
                $this->getEm()->clear();
                throw new RequestException(HttpResponse::HTTP_NOT_ACCEPTABLE, $errors[0]);
            }
        }
        $response->setKey("rowsAffected", $count);
        $response->setKey("success", true);
    }
    
    protected function patchRemovePwcd(RestResponseInterface $response, RestRequestInterface $request) {
        /* @var $user User */
        /* @var $dbUser User */        
        $entities = $this->getEntities(User::class, ['id']);
        if (\sizeof($entities) < 1) {
            return;
        }
        $count = 0;        
        $dao = Dao::user($this->getEm());
        foreach ($entities as $user) {
            $dbUser = $dao->findOneById($user->getId());
            PermissionsUtil::assertUserForUser($dbUser , $this->getContext()->getUser());
            if ($dbUser->getPasswordCampusDual() !== null) {
                $dbUser->setPasswordCampusDual(null);
                ++$count;
            }
        }
        $response->setKey("rowsAffected", $count);
        $response->setKey("success", true);
    }
    
    protected function patchChangePwcd(RestResponseInterface $response, RestRequestInterface $request) {
        /* @var $user User */
        /* @var $dbUser User */
        /* @var $loader CampusDualLoader */
        $entities = $this->getEntities(User::class, ['id', 'passwordCampusDual']);
        if (\sizeof($entities) < 1) {
            return;
        }
        $dao = Dao::user($this->getEm());
        $count = 0;
        $errors = [];
        foreach ($entities as $user) {
            $dbUser = $dao->findOneById($user->getId());
            PermissionsUtil::assertUserForUser($dbUser , $this->getContext()->getUser());
            if ($dbUser->getPasswordCampusDual() === null || $dbUser->getPasswordCampusDual()->getString() !== $user->getPasswordCampusDual()->getString()) {
                $message = $this->checkNewPasswordCampusDual($dbUser->getStudentId(), $user->getPasswordCampusDual());
                if ($message === null) {
                    $dbUser->setPasswordCampusDual($user->getPasswordCampusDual());
                    ++$count;
                }
                else {
                    $errors []= $message;
                }
            }          
            $dao->validateEntity($dbUser , $this->getTranslator(), $errors);
            if (\sizeof($errors) > 0) {
                $this->getEm()->clear();
                throw new RequestException(HttpResponse::HTTP_NOT_ACCEPTABLE, $errors[0]);
            }
        }
        $response->setKey("rowsAffected", $count);
        $response->setKey("success", true);
    }
    
    protected function patchChangeAvatar(RestResponseInterface $response, RestRequestInterface $request) {
        /* @var $user User */
        /* @var $dbUser User*/
        $entities = $this->getEntities(User::class, ['id', 'avatar']);
        if (\sizeof($entities) < 1) {
            return;
        }
        $dao = Dao::user($this->getEm());
        $count = 0;
        $errors = [];
        foreach ($entities as $user) {
            $dbUser = $dao->findOneById($user->getId());
            PermissionsUtil::assertUserForUser($dbUser , $this->getContext()->getUser());
            if ($dbUser->getAvatar() !== $user->getAvatar()) {
                $dbUser->setAvatar($user->getAvatar());
                ++$count;
            }
            if (!$dao->validateEntity($dbUser , $this->getTranslator(), $errors)) {
                $this->getEm()->clear();
                throw new RequestException(HttpResponse::HTTP_NOT_ACCEPTABLE, $errors[0]);
            }
        }
        $response->setKey("rowsAffected", $count);
        $response->setKey("success", true);
    }
    
    protected function getList(RestResponseInterface $response, RestRequestInterface $request) {
        /* @var $userList User[] */
        $data = $this->retrieveAll($request->getHttpRequest(), self::FIELDS_LIST_SORT, self::FIELDS_LIST_SEARCH);
        $user = $this->retrieveUser($response, $request->getHttpRequest(), $this, $this, true);
        $currentUser = $this->getContext()->getUser();
        if ($user === null) {
            return;
        }        
        PermissionsUtil::assertUserForUser($user, $currentUser, true);
        
        $dao = Dao::user($this->getEm());
        if ($user->getIsSiteAdmin()) {
            $userList = $dao->findN($data->sort, $data->sortDirection, $data->count, $data->offset, $data->search);
            $totalFiltered = $dao->countAll($data->search);
            $total = $dao->countAll();
        }
        else if ($user->getTutorialGroup() === null) {
            $userList = [];
            $totalFiltered = 0;
        }
        else {
            $fos = $user->getTutorialGroup()->getFieldOfStudy();
            $userList = $dao->findNByFieldOfStudy($fos, $data->sort, $data->sortDirection, $data->offset, $data->count, $data->search, $currentUser);
            $totalFiltered = $dao->countByFieldOfStudy($fos, $data->sort, $data->search, $currentUser);
            $empty = [];
            $total = $dao->countByFieldOfStudy($fos, $data->sort, $empty, $currentUser);
        }
        $viewList = \array_map(function(User $someUser) use ($currentUser) {
            return new UserPermissionFacet($someUser, $currentUser);
        }, $userList);
        $response->setKey('success', 'true');
        $response->setKey('countTotal', $total);
        $response->setKey('countFiltered', $totalFiltered);
        $response->setKey('entity', $this->mapObjects2Json($viewList, self::FIELDS_LIST_ACCESS, true));
    }

    public static function getRoutingPath(): string {
        return CmnCnst::SERVLET_USER;
    }

    /**
     * @param string $studentId
     * @param ProtectedString $password
     * @return Message
     */
    private function checkNewPasswordCampusDual(string $studentId, ProtectedString $password) {
        try {
            return CampusDualLoader::perform($studentId, $password, function(CampusDualLoader $loader){
                $loader->assertValidity();
                return null;
            });
        }
        catch (CampusDualException $e) {
            if ($e->is(CampusDualException::FLAG_ACCESS_DENIED)) {
                return Message::dangerI18n('request.illegal', 'servlet.user.pwcd.wrong', $this->getTranslator());
            }
            else {
                \error_log("Could not validate new password: $e");
                return Message::dangerI18n('error.internal', 'servlet.user.pwcd.error', $this->getTranslator());
            }
        }                
        catch (\Throwable $t) {
            \error_log("Unexpected error, could not validate new password: $t");
            return Message::dangerI18n('error.internal', 'servlet.user.pwcd.error', $this->getTranslator());
        }
    }

}