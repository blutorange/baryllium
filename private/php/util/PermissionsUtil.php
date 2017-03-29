<?php

/* Note: This license has also been called the "New BSD License" or "Modified
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

namespace Util;

use Controller\PermissionsException;
use Entity\Course;
use Entity\Forum;
use Entity\Post;
use Entity\Thread;
use Entity\User;

/**
 * Utility functions for working with collections.
 *
 * @author madgaksha
 */
class PermissionsUtil {  
    private function __construct() {}
    
    /**
     * @param Forum $forum
     * @param User $user
     * @throws PermissionsException
     */
    public static function assertForumForUser(Forum $forum, User $user = null, bool $throw = true) {
        if ($user === null) {
            if ($throw) {
                throw new PermissionsException();
            }
            return false;
        }
        if ($user->getIsSiteAdmin()) {
            return true;
        }
        $tutGroup = $user->getTutorialGroup();
        if ($tutGroup === null) {
            if ($throw) {
                throw new PermissionsException();
            }
            return false;
        }
        if ($tutGroup->getFieldOfStudy()
                ->getCourseList()
                ->filter(function(Course $course = null) use ($forum) {
            return $course->getForum()->getId() === $forum->getId();
        })->isEmpty()) {
            if ($throw) {
                throw new PermissionsException();
            }
            return false;
        }
    }

    /**
     * 
     * @param Thread $thread
     * @param User $user
     * @throws PermissionsException
     */
    public static function assertThreadForUser(Thread $thread, User $user) {
        return self::assertForumForUser($thread->getForum(), $user);
    }

    public static function assertEditPostForUser(Post $post, User $user, bool $throw = true) : bool {
        $postUser = $post->getUser();
        if ($postUser === null || $user->getId() === $postUser->getId() || $user->getIsSiteAdmin()) {
            return true;
        }
        if ($throw) {
            throw new PermissionsException();
        }
        return false;
    }

}