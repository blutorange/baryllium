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
use Doctrine\Common\Collections\ArrayCollection;
use Entity\Post;
use Entity\Thread;
use Traits\NewPostTrait;
use Ui\Message;
use Util\PermissionsUtil;

/**
 * Shows a list of posts for a given thread.
 *
 * @author Philipp
 * @author Andre Wachsmuth
 */
class PostController extends AbstractController {

    use NewPostTrait { makeNewPost as private; } 
    
    const PARAM_THREAD_ID = 'tid';
    const PARAM_OFFSET = 'off';
    const PARAM_COUNT = 'cnt';

    private $user;
    
    public function doGet() {
        $thread = $this->getThread();
        $postList = $this->retrievePostList($thread);
        $this->renderTemplate('t_postlist', ['postList' => $postList]);
    }

    public function doPost() {
        $thread = $this->getThread();
        $postList = $this->retrievePostList($thread);
        if ($thread !== null) {
            $post = $this->makeNewPost($this, $thread, $this->user);
            array_push($postList, $post);
        }
        $this->renderTemplate('t_postlist', ['postList' => $postList]);
    }
    
    /** @return Thread */
    private function getThread() {
        $tid = $this->getParam(self::PARAM_THREAD_ID);
        if ($tid === null) {
            return null;
        }
        $user = $this->getSessionHandler()->getUser();
        $thread = AbstractDao::thread($this->getEm())->findOneById($tid);
        if ($thread !== null && PermissionsUtil::threadForUser($thread, $user)) {
            $this->user = $user;
            return $thread;
        }
        else {
            $this->addMessage(Message::infoI18n('thread.id.invalid.message',
                'thread.id.invalid.detail', $this->getTranslator()));
            return null;
        }
    }

    /**
     * @return Post[]
     */
    private function retrievePostList(Thread $thread = null) : array {
        $offset = $this->getParamInteger(self::PARAM_OFFSET, 0);
        $count = $this->getParamInteger(self::PARAM_COUNT, 10);
        if ($thread === null) {
            return [];
        }
        return AbstractDao::post($this->getEm())->findNPostsByThread($thread, $offset, $count);
    }
}