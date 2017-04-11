<?php

namespace Moose\Seed;

use Moose\Dao\AbstractDao;
use Moose\Entity\Post;
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
class PostSeed extends DormantSeed {
    /**
     * @param int $count
     * @param bool $addToThread
     * @return Post[]
     */
    public function & seedRandom(int $count = 10, bool $addToThread = true) : array {
        /* @var $thread Thread */
        $threadList = $addToThread ? AbstractDao::thread($this->em())->findAll() : [];
        $userList = AbstractDao::user($this->em())->findAll();
        if (\sizeof($userList) === 0) {
            \error_log("No users found, creating some.");
            $userList = (new UserSeed($this->em()))->seedRandom();
        }
        $postList = [];
        $count = MathUtil::max(1, $count);
        for ($i = 0; $i < $count; ++$i) {
            $creationTime = $this->time(rand(2000,2020), rand(1,12), rand(1,28), rand(0,23), rand(0,59), rand(0,59));
            $editTime = clone $creationTime;
            $editTime = rand(1, 2) === 1 ? $editTime->modify("+1 day") : null;            
            $thread = \sizeof($threadList) > 0 ? $threadList[\array_rand($threadList)] : null;
            $this->em()->persist($post = Post::create()
                    ->setContent($this->sentences(rand(1, 20)))
                    ->setCreationTime($creationTime)
                    ->setEditTime($editTime)
                    ->setUser($userList[\array_rand($userList)])
            );
            if ($thread !== null) {
                $thread->addPost($post);
            }
            $postList []= $post;
        }
        return $postList;
    }
    
    public function & seedDeterministic(int $count = 10, bool $addToThread = true) : array {
        /* @var $thread Thread */
        $threadList = $addToThread ? AbstractDao::thread($this->em())->findAll() : [];
        $userList = AbstractDao::user($this->em())->findAll();
        if (\sizeof($userList) === 0) {
            \error_log("No users found, creating some.");
            $userList = (new UserSeed($this->em()))->seedDeterministic();
        }
        $postList = [];
        $count = MathUtil::max(1, $count);
        for ($i = 0; $i < $count; ++$i) {
            $creationTime = $this->time(2000+$i%20, 1+$i%12, 1+$i%28, $i%23, $i%59, $i%59);
            $editTime = clone $creationTime;
            $editTime = $i%2 === 1 ? $editTime->modify("+1 day") : null;            
            $thread = \sizeof($threadList) > 0 ? $threadList[$i%\sizeof($threadList)] : null;
            $this->em()->persist($post = Post::create()
                    ->setContent("Some content for post $i.")
                    ->setCreationTime($creationTime)
                    ->setEditTime($editTime)
                    ->setUser($userList[$i%\sizeof($userList)])
            );
            if ($thread !== null) {
                $thread->addPost($post);
            }
            $postList []= $post;
        }
        return $postList;
    }
}

