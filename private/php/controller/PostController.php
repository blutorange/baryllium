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

namespace Moose\Controller;

use Dao\AbstractDao;
use Entity\Post;
use Entity\Thread;
use Moose\Web\HttpRequestInterface;
use Moose\Web\HttpResponseInterface;
use Moose\Web\RequestWithThreadTrait;
use Util\CmnCnst;
use Util\PermissionsUtil;
use ViewModel\Paginable;
use ViewModel\PaginableInterface;

/**
 * Shows a list of posts for a given thread.
 *
 * @author Philipp
 * @author Andre Wachsmuth
 */
class PostController extends AbstractForumController {
     
    use RequestWithThreadTrait;
    
    public function doGet(HttpResponseInterface $response, HttpRequestInterface $request) {
        $user = $this->getSessionHandler()->getUser();
        $thread = $this->retrieveThreadIfAuthorized(
                PermissionsUtil::PERMISSION_WRITE, $response, $request,
                $this, $this, $user);
        $paginable = $this->retrievePostPaginable($thread);
        $this->renderTemplate('t_postlist', [
            'postPaginable' => $paginable]);
    }

    public function doPost(HttpResponseInterface $response, HttpRequestInterface $request) {
        $user = $this->getSessionHandler()->getUser();
        $thread = $this->retrieveThreadIfAuthorized(
                PermissionsUtil::PERMISSION_WRITE, $response, $request,
                $this, $this, $user);
        if ($thread !== null) {
            $post = $this->makeNewPost($thread, $user);
            if ($post !== null) {
                // Make sure we get a valid ID.
                $this->getEm()->flush();
            }
        }
        $paginable = $this->retrievePostPaginable($thread, $post);
        $this->renderTemplate('t_postlist', [
            'postPaginable' => $paginable
        ]);
    }

    /**
     * @return PaginableInterface
     */
    private function retrievePostPaginable(Thread $thread = null) : PaginableInterface {
        if ($thread === null) {
            return Paginable::ofEmpty();
        }

        $offset = $this->getRequest()->getParamInt(CmnCnst::URL_PARAM_OFFSET, 0);
        $count = $this->getRequest()->getParamInt(CmnCnst::URL_PARAM_COUNT, 10);
        $dao = AbstractDao::post($this->getEm());
        $postList = $dao->findNByThread($thread, $offset, $count);
        $total = $dao->countByThread($thread);
        $urlPattern = \strtr($this->getContext()->getServerPath(
                CmnCnst::PATH_FORUM_POST),
                ['{%tid%}' => (string)$thread->getId()]);
        
        return Paginable::fromOffsetAndCount($urlPattern, $total, $offset,
                        $count, $postList);
    }
}