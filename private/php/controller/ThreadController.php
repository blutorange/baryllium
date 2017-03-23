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
use Entity\Forum;
use Entity\Thread;
use Ui\Message;
use Util\PermissionsUtil;

/**
 * For displaying a list of threads for a forum.
 *
 * @author Philipp
 */
class ThreadController extends AbstractController {
    const PARAM_FORUM_ID = "fid";
    const PARAM_ACTION = "action";
    
    public function doGet() {
        $forum = $this->getForum();
        $threadList = $forum !== null ? $forum->getThreadList() : new ArrayCollection();
        $this->renderTemplate('t_threadlist', ['threadList' => $threadList]);
    }

    public function doPost() {
        $forum = $this->getForum();
        if ($forum !== null) {
            $this->newThread($forum);
        }
        $threadList = $forum !== null ? $forum->getThreadList() : new ArrayCollection();
        $this->renderTemplate('t_threadlist', ['threadList' => $threadList]);
    }
    
    private function newThread(Forum $forum) {
        $thread = new Thread;
        $name = $this->getParam('title');
        $thread->setName($name);
        $forum->addThread($thread);
        $errors = AbstractDao::generic($this->getEm())
                ->queue($thread)
                ->queue($forum)
                ->persistQueue($this->getTranslator());
        $this->addMessages($errors);
    }
    
    /**
     * @return Forum
     */
    private function getForum() {
        $fid = $this->getParam(self::PARAM_FORUM_ID);
        if ($fid === null) {
            return null;
        }
        $user = $this->getSessionHandler()->getUser();
        
        $forum = AbstractDao::forum($this->getEm())->findOneById($fid);
        if ($forum !== null && PermissionsUtil::forumForUser($forum, $user)) {
            return $forum;
        }
        else {
            $this->addMessage(Message::infoI18n('forum.id.invalid.message',
                'forum.id.invalid.detail', $this->getTranslator()));
            return null;
        }
    }
}