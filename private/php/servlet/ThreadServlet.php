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

use Moose\Dao\Dao;
use Moose\Entity\Thread;
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
class ThreadServlet extends AbstractEntityServlet {
    protected function patchRename(RestResponseInterface $response, RestRequestInterface $request) {
        /* @var $thread Thread */
        /* @var $dbThread Thread */
        $dao = Dao::thread($this->getEm());
        $entities = $this->getEntities(Thread::class, ['id', 'name' => ['emptieable' => false]]);
        if (\sizeof($entities) < 1) {
            return;
        }
        $count = 0;
        $errors = [];
        foreach ($entities as $thread) {
            $dbThread = $dao->findOneById($thread->getId());
            if ($dbThread->getName() !== $thread->getName()) {
                PermissionsUtil::assertThreadForUser($dbThread, $this->getContext()->getUser(), PermissionsUtil::PERMISSION_WRITE);
                $dbThread->setName($thread->getName());
                ++$count;
            }
            if (!$dao->validateEntity($dbThread, $this->getTranslator(), $errors)) {
                $response->setError(HttpResponse::HTTP_NOT_ACCEPTABLE, $errors[0]);
                $this->getEm()->clear();
                return;
            }
        }
        $response->setKey("rowsAffected", $count);
        $response->setKey("success", true);
    }
    
    protected final function deleteSingle(RestResponseInterface $response, RestRequestInterface $request) {
        /* @var $thread Thread */
        /* @var $dbThread Thread */
        $daoThread = Dao::thread($this->getEm());
        $daoPost = Dao::post($this->getEm());
        $entities = $this->getEntities(Thread::class, ['id']);
        if (\sizeof($entities) < 1) {
            return;
        }
        $count = 0;
        foreach ($entities as $thread) {
            $dbThread = $daoThread->findOneById($thread->getId());
            PermissionsUtil::assertThreadForUser($dbThread, $this->getContext()->getUser(), PermissionsUtil::PERMISSION_WRITE);        
            foreach ($dbThread->getPostList() as $dbPost) {
                $daoPost->remove($dbPost);
            }
            $daoThread->remove($dbThread);
            ++$count;
        }
        $response->setKey("rowsAffected", $count);
        $response->setKey("success", true);
    }


    public static function getRoutingPath(): string {
        return CmnCnst::SERVLET_THREAD;
    }
}