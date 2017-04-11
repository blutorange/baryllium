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

namespace Moose\Dao;

use Moose\Entity\Forum;
use Moose\Entity\Thread;

/**
 * Methods for interacting with Thread objects and the database.
 *
 * @author madgaksha
 */
class ThreadDao extends AbstractDao {
    protected function getEntityClass(): string {
        return Thread::class;
    }
    
        /**
     * 
     * @param Forum $forum
     * @param int $offset
     * @param int $count
     * @return Thread[]
     */    
    public function findNByForum(Forum $forum, int $offset = 0, int $count = null) : array {
        return $this->findNByForumId($forum->getId(), $offset, $count);
    }
    
    /**
     * @param int $forumId
     * @param int $offset
     * @param int $count
     * @return Thread[]
     */
    public function findNByForumId(int $forumId, int $offset = 0, int $count = null) : array {
        return $this->findAllByField('forum', $forumId, 'creationTime', true,
                        $count ?? CmnCnst::MIN_PAGINABLE_COUNT, $offset);
    }

    /**
     * @param Forum $forum
     * @return int
     */
    public function countByForum(Forum $forum) {
        return $this->countByField('forum', $forum->getId());
    }
}