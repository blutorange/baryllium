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

use Moose\Dao\AbstractDao;
use Moose\Entity\Forum;
use Moose\Web\HttpRequestInterface;
use Moose\Web\HttpResponseInterface;
use Moose\Web\RequestWithForumTrait;
use Moose\Util\CmnCnst;
use Moose\Util\PermissionsUtil;
use Moose\ViewModel\Paginable;
use Moose\ViewModel\PaginableInterface;

/**
 * For displaying a list of threads for a forum.
 *
 * @author Philipp
 * @author Andre Wachsmuth
 */
class ForumController extends AbstractForumController {

    use RequestWithForumTrait;
    use \Moose\Web\RequestWithCountAndOffsetTrait;
   
    public function doGet(HttpResponseInterface $response, HttpRequestInterface $request) {
        $forum = $this->retrieveForumIfAuthorized(
                PermissionsUtil::PERMISSION_READWRITE, $response, $request,
                $this, $this, $this->getSessionHandler()->getUser());
        $paginable = $this->retrieveThreadPaginable($forum);
        $this->renderTemplate('t_threadlist', [
            'forum' => $forum,
            'threadPaginable' => $paginable
        ]);
    }

    public function doPost(HttpResponseInterface $response, HttpRequestInterface $request) {
        $user = $this->getSessionHandler()->getUser();
        $forum = $this->retrieveForumIfAuthorized(
                PermissionsUtil::PERMISSION_READWRITE, $response, $request,
                $this, $this, $this->getSessionHandler()->getUser());
        if ($forum !== null) {
            $thread = $this->makeNewThread($forum);
            $post = $thread !== null ? $this->makeNewPost($thread, $user) : null;
            if ($post !== null) {
                // Make sure we get a valid ID.
                $this->getEm()->flush();
            }
        }
        $paginable = $this->retrieveThreadPaginable($forum);
        $this->renderTemplate('t_threadlist', [
            'forum' => $forum,
            'threadPaginable' => $paginable
        ]);
    }
          
    /**
     * @param $forum Forum
     * @return PaginableInterface
     */
    private function retrieveThreadPaginable(Forum $forum = null) : PaginableInterface {
        if ($forum === null) {
            return Paginable::ofEmpty();
        }
        $offset = $offset = $this->retrieveOffset($this->getRequest());
        $count = $this->retrieveCount($this->getRequest());
        
        $dao = AbstractDao::thread($this->getEm());
        $threadList = $dao->findNByForum($forum, $offset, $count);
        $total = $dao->countByForum($forum);
        $urlPattern = \strtr($this->getContext()->getServerPath(
                CmnCnst::PATH_FORUM_THREAD),
                ['{%fid%}' => (string)$forum->getId()]);
               
        return Paginable::fromOffsetAndCount($urlPattern, $total, $offset,
                        $count, $threadList);
    }
}