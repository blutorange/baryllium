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

use Moose\Dao\AbstractDao;
use Moose\Entity\Thread;
use Moose\Entity\User;
use Moose\Util\CmnCnst;
use Moose\Util\PermissionsUtil;
use Moose\Web\HttpResponse;
use Moose\Web\RestRequestInterface;
use Moose\Web\RestResponseInterface;

/**
 * For manipulating (forum) threads.
 *
 * @author madgaksha
 */
class UserServlet extends AbstractEntityServlet {
    protected function patchChangeMail(RestResponseInterface $response, RestRequestInterface $request) {
        /* @var $user User */
        /* @var $dbUser User*/
        $entities = $this->getEntities(User::class, ['id', 'mail']);
        if (\sizeof($entities) < 1) {
            return;
        }
        $dao = AbstractDao::user($this->getEm());
        $count = 0;
        $errors = [];
        foreach ($entities as $user) {
            $dbUser = $dao->findOneById($user->getId());
            PermissionsUtil::assertSameUser($dbUser , $this->getContext()->getSessionHandler()->getUser());
            if ($dbUser->getMail() !== $user->getMail()) {
                $dbUser->setMail($user->getMail());
                ++$count;
            }
            if (!$dao->validateEntity($dbUser , $this->getTranslator(), $errors)) {
                $response->setError(HttpResponse::HTTP_NOT_ACCEPTABLE, $errors[0]);
                $this->getEm()->clear();
                return;
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
        $dao = AbstractDao::user($this->getEm());
        $count = 0;
        $errors = [];
        foreach ($entities as $user) {
            $dbUser = $dao->findOneById($user->getId());
            PermissionsUtil::assertSameUser($dbUser , $this->getContext()->getSessionHandler()->getUser());
            if ($dbUser->getAvatar() !== $user->getAvatar()) {
                $dbUser->setAvatar($user->getAvatar());
                ++$count;
            }
            if (!$dao->validateEntity($dbUser , $this->getTranslator(), $errors)) {
                $response->setError(HttpResponse::HTTP_NOT_ACCEPTABLE, $errors[0]);
                $this->getEm()->clear();
                return;
            }
        }
        $response->setKey("rowsAffected", $count);
        $response->setKey("success", true);
    }

    public static function getRoutingPath(): string {
        return CmnCnst::SERVLET_THREAD;
    }
}