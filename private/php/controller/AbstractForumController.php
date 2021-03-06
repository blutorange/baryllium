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

use Moose\Dao\Dao;
use Moose\Entity\Forum;
use Moose\Entity\Post;
use Moose\Entity\Thread;
use Moose\Entity\User;
use Moose\Util\CmnCnst;

/**
 * Some convenience methods for creating threads and posts etc.
 *
 * @author madgaksha
 */
abstract class AbstractForumController extends BaseController {
    /**
     * @param Forum $forum
     * @return Thread
     */
    protected function makeNewThread(Forum $forum, bool $persist = true) {
        $thread = new Thread;
        $name = $this->getRequest()->getParam(CmnCnst::URL_PARAM_NEW_THREAD_TITLE);
        $thread->setName($name);
        $forum->addThread($thread);
        if ($persist) {
            $errors = Dao::generic($this->getEm())
                    ->queue($thread)
                    ->queue($forum)
                    ->persistQueue($this->getTranslator());
            $this->getResponse()->addMessages($errors);

            if (\sizeof($errors) > 0) {
                return null;
            }
        }
        
        return $thread;
    }

    /**
     * 
     * @param Thread $thread
     * @param User $user
     * @return Post
     */
    protected function makeNewPost(Thread $thread, User $user, bool $persist = true) {
        $content = $this->getRequest()->getParam(CmnCnst::URL_PARAM_NEW_POST_CONTENT);

        $post = new Post();
        $post->setUser($user);
        $post->setContent($content);
        $thread->addPost($post);

        if ($persist) {
            $errors = Dao::generic($this->getEm())
                    ->queue($post)
                    ->queue($thread)
                    ->persistQueue($this->getTranslator());
            $this->getResponse()->addMessages($errors);

            if (\sizeof($errors) > 0) {
                return null;
            }
        }
        
        return $post;
    }    
}
