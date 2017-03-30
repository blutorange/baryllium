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

use Entity\Course;
use Entity\Document;
use Entity\Forum;
use Entity\Post;
use Entity\Thread;
use Entity\User;
use Moose\Controller\PermissionsException;

/**
 * Utility functions for working with collections.
 *
 * @author madgaksha
 */
class PermissionsUtil {  
    private function __construct() {}
    
    const PERMISSION_READ = 1;
    const PERMISSION_WRITE = 2;
    const PERMISSION_READWRITE = self::PERMISSION_READ | self::PERMISSION_WRITE;
    
    /**
     * @param Forum $forum Forum to check.
     * @param User $user User whose permissions are checked.
     * @param bool $throw Whether an error is thrown when the use is not authorized.
     * @return Whether the user is authorized.
     * @throws PermissionsException When <code>$throw</code> is set to <code>true</code> and the user is not authorized.
     */
    public static function assertForumForUser(Forum $forum,
            User $user = null,
            int $permType = self::PERMISSION_READWRITE,
            bool $throw = true) : bool {
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
        $exists = $tutGroup->getFieldOfStudy()->getCourseList()->exists(function($_, Course $course = null) use ($forum) {
            return $course->getForum()->getId() === $forum->getId();
        });
        if (!$exists) {
            if ($throw) {
                throw new PermissionsException();
            }
            return false;
        }

        return true;
    }

    /**
     * 
     * @param Thread $thread Thread to check.
     * @param User $user User whose permissions are checked.
     * @param bool $throw Whether an error is thrown when the use is not authorized.
     * @return Whether the user is authorized.
     * @throws PermissionsException When <code>$throw</code> is set to <code>true</code> and the user is not authorized.
     */
    public static function assertThreadForUser(Thread $thread, User $user,
            int $permType = self::PERMISSION_READWRITE, bool $throw = true) {
        return self::assertForumForUser($thread->getForum(), $user, $permType, $throw);
    }
   
    /**
     * @param Post $post Post to check.
     * @param User $user User whose permissions are checked.
     * @param int $permType Type of permission to check. One of
     * <code>PermissionUtil::PERMISSION_READ</code>,
     * <code>PermissionUtil::PERMISSION_WRITE</code>, or
     * <code>PermissionUtil::PERMISSION_READWRITE</code>.
     * @param bool $throw Whether an error is thrown when the use is not authorized.
     * @throws PermissionsException When <code>$throw</code> is set to <code>true</code> and the user is not authorized.
     * @return bool Whether the user is authorized.
     */
    public static function assertPostForUser(Post $post, User $user,
            int $permType = self::PERMISSION_READWRITE, bool $throw = true) : bool {
        if ($user === null) {
            if ($throw) {
                throw new PermissionsException();
            }
            return false;
        }
        
        $authed = true;
        if ($permType & self::PERMISSION_READ !== 0) {
            $authed = $authed && static::assertPostForUserRead($post, $user, $throw);
        }
        if ($permType & self::PERMISSION_READ !== 0) {
            $authed = $authed && static::assertPostForUserWrite($post, $user, $throw);
        }
        return $authed;
    }
    
    /**
     * @param Document $post Document to check.
     * @param User $user User whose permissions are checked.
     * @param int $permType Type of permission to check. One of
     * <code>PermissionUtil::PERMISSION_READ</code>,
     * <code>PermissionUtil::PERMISSION_WRITE</code>, or
     * <code>PermissionUtil::PERMISSION_READWRITE</code>.
     * @param bool $throw Whether an error is thrown when the user is not
     * authorized.
     * @throws PermissionsException When <code>$throw</code> is set to
     * <code>true</code> and the user is not authorized.
     * @return bool Whether the user is authorized.
     */
    public static function assertDocumentForUser(Document $document, User $user,
            int $permType = self::PERMISSION_READWRITE, bool $throw = true) : bool {
        return self::assertCourseForUser($document->getCourse(), $user,
                $permType, $throw);
    }
    
    public static function assertCourseForUser(Course $course, User $user,
            int $permType = self::PERMISSION_READWRITE, bool $throw = true) : bool {
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
        
        $exists = $tutGroup->getFieldOfStudy()->getCourseList()->exists(function($_, Course $c) use ($course) {
            return $course->getId() === $c->getId();
        });
        if (!$exists) {
            if ($throw) {
                throw new PermissionsException();
            }
            return false;
        }

        return true;
    }
    
    
    private static function assertPostForUserWrite(Post $post, User $user, bool $throw = true) : bool {
        $postUser = $post->getUser();
        if ($postUser === null || $user->getId() === $postUser->getId() || $user->getIsSiteAdmin()) {
            return true;
        }
        if ($throw) {
            throw new PermissionsException();
        }
        return false;
    }
    
    private static function assertPostForUserRead(Post $post, User $user, bool $throw = true) : bool {
        return $this->assertForumForUser($post->getThread()->getForum(), $user, static::PERMISSION_READWRITE, $throw);
    }
}