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

namespace Moose\Test\Unit;

use Moose\Dao\ForumDao;
use Moose\Dao\ThreadDao;
use Moose\Entity\Forum;
use Moose\Entity\Thread;

/**
 * Description of ForumTest
 *
 * @author Philipp
 */
class ThreadTest extends AbstractEntityTest {

    /**
     * @test
     * @group entity
     * @group unit
     * @group thread
     */
    public function testPersist() {
        $daoThread = new ThreadDao($this->getEm());
        $daoForum = new ForumDao($this->getEm());
        $forum = new Forum();
        $forum->setName("3MI15-1");
        $thread = new Thread();
        $thread->setName("News März");
        $forum->addThread($thread);
        
        //write values into database
        $daoForum->persist($forum, $this->getTranslator(), false);
        $daoThread->persist($thread, $this->getTranslator(), true);

        //read from database
        $loadedForum = $daoForum->findOneById($forum->getId());
        $loadedThread = $daoThread->findOneById($thread->getId());

        //testing values
        $this->assertEquals($loadedForum->getId(), $forum->getId());
        $this->assertEquals($loadedThread->getId(), $thread->getId());
        
        $this->assertTrue($loadedForum->getThreadList()->get(0) === $loadedThread);
        
        $this->assertFalse($loadedForum->getThreadList()->get(1) === $loadedThread);
    }

}
