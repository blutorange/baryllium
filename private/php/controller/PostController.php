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
use Entity\Post;
use Entity\Thread;
use Ui\Message;
//use Util\DebugUtil;
use Util\PermissionsUtil;

/**
 * Shows a list of posts for a given thread.
 *
 * @author Philipp
 * @author Andre Wachsmuth
 */
class PostController extends AbstractForumController {
  
    const PARAM_THREAD_ID = 'tid';
    const PARAM_OFFSET = 'off';
    const PARAM_COUNT = 'cnt';

    private $user;
    
    public function doGet(HttpResponseInterface $response, HttpRequestInterface $request) {
        $thread = $this->getThread($response, $request);
        $postList = $this->retrievePostList($thread);
        $this->renderTemplate('t_postlist', ['postList' => $postList]);
    }

    public function doPost(HttpResponseInterface $response, HttpRequestInterface $request) {
        $thread = $this->getThread($response, $request);
        $postList = $this->retrievePostList($thread);
        if ($thread !== null) {
            $post = $this->makeNewPost($thread, $this->user);
            if ($post !== null) {
                array_push($postList, $post);
                // Make sure we get a valid ID.
                $this->getEm()->flush();
            }
        }
        $this->renderTemplate('t_postlist', ['postList' => $postList]);
    }
    
    /** @return Thread */
    private function getThread(HttpResponseInterface $response, HttpRequestInterface $request) {
        $tid = $request->getParam(self::PARAM_THREAD_ID);
        if ($tid === null) {
            $this->addInvalidMessage($response);
            return null;
        }
        $user = $this->getSessionHandler()->getUser();
        $thread = AbstractDao::thread($this->getEm())->findOneById($tid);
        if ($thread === null) {
            $this->addInvalidMessage($response);
            return null;
        }
        PermissionsUtil::assertThreadForUser($thread, $user);
        $this->user = $user;
        return $thread;
    }
    
    private function addInvalidMessage(HttpResponseInterface $response) {
        $response->addMessage(Message::infoI18n('thread.id.invalid.message',
                'thread.id.invalid.detail', $this->getTranslator()));
    }

    /**
     * @return Post[]
     */
    private function retrievePostList(Thread $thread = null) : array {
        if ($thread === null) {
            return [];
        }
        $offset = $this->getRequest()->getParamInt(self::PARAM_OFFSET, 0);
        $count = $this->getRequest()->getParamInt(self::PARAM_COUNT, 10);
        return AbstractDao::post($this->getEm())->findNPostsByThread($thread,
                        $offset, $count);
    }
}
