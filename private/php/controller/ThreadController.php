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

namespace Controller;

use Dao\AbstractDao;
use Entity\Forum;
use Entity\Thread;
use Entity\User;
use Ui\Message;
use Util\PermissionsUtil;

/**
 * For displaying a list of threads for a forum.
 *
 * @author Philipp
 * @author Andre Wachsmuth
 */
class ThreadController extends AbstractForumController {
    
    const PARAM_FORUM_ID = "fid";
    const PARAM_OFFSET = 'off';
    const PARAM_COUNT = 'cnt';
    
    /** @var User */
    private $user;
    
    public function doGet(HttpResponseInterface $response, HttpRequestInterface $request) {
        $forum = $this->getForum($response, $request);
        $threadList = $this->retrieveThreadList($forum, $request);
        $this->renderTemplate('t_threadlist', ['threadList' => $threadList]);
    }

    public function doPost(HttpResponseInterface $response, HttpRequestInterface $request) {
        $forum = $this->getForum($response);
        $threadList = $this->retrieveThreadList($forum);
        if ($forum !== null) {
            $thread = $this->makeNewThread($forum);
            $post = $this->makeNewPost($thread, $this->user);
            array_push($threadList, $thread);
            // Make sure we get a valid ID.
            $this->getEm()->flush();
        }
        $this->renderTemplate('t_threadlist', ['threadList' => $threadList]);
    }
       
    /**
     * @return Forum
     */
    private function getForum(HttpResponseInterface $response, HttpRequestInterface $request) {
        $fid = $request->getParam(self::PARAM_FORUM_ID);
        if ($fid === null) {
            $this->addInvalidMessage($response);
            return null;
        }
        $user = $this->getSessionHandler()->getUser();
        
        $forum = AbstractDao::forum($this->getEm())->findOneById($fid);
        if ($forum === null) {
            $this->addInvalidMessage($response);
            return null;
        }
        PermissionsUtil::assertForumForUser($forum, $user);
        $this->user = $user;
        return $forum;
    }
    
    private function addInvalidMessage(HttpResponseInterface $response) {
         $response->addMessage(Message::infoI18n('forum.id.invalid.message',
                'forum.id.invalid.detail', $this->getTranslator()));
    }

    /**
     * @return Thread[]
     */
    private function retrieveThreadList(Forum $forum = null, HttpRequestInterface $request) : array {
        $offset = $this->getParamInt(self::PARAM_OFFSET, 0);
        $count = $this->getParamInt(self::PARAM_COUNT, 10);
        if ($forum === null) {
            return [];
        }
        return AbstractDao::thread($this->getEm())->findNThreadsByForum($forum, $offset, $count);
    }
}