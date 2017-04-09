<?php

namespace Moose\Seed;

use Moose\Dao\AbstractDao;
use Moose\Entity\Forum;
use Moose\Entity\Thread;
use Moose\Seed\DormantSeed;
use Moose\Util\MathUtil;


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

/**
 * @author madgaksha
 */
class ThreadSeed extends DormantSeed {
    /**
     * @param int $count
     * @param bool $addToForum
     * @return Thread[]
     */
    protected function & seedRandom(int $count = 10, bool $addToForum = true) : array {
        /* @var $forum Forum */
        $forumList = $addToForum ? AbstractDao::forum($this->em())->findAll() : [];
        $threadList = [];
        $count = MathUtil::max(1, $count);
        for ($i = 0; $i < $count; ++$i) {
            $forum = \sizeof($forumList) > 0 ? $forumList[array_rand($forumList)] : null;
            $post = (new PostSeed($this->em()))->seedRandom(1, false)[0];
            $this->em()->persist($thread = Thread::create()
                    ->setName($this->name())
                    ->setCreationTime($this->time(rand(2000,2020), rand(1,12), rand(1,28), rand(0,23), rand(0,59), rand(0,59)))
                    ->addPost($post)
            );
            if ($forum !== null) {
                $forum->addThread($thread);
            }
            $threadList []= $thread;
        }
        return $threadList;
    }
}