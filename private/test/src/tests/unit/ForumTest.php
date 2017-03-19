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

namespace UnitTest;

use \Entity\Forum;
use \Dao\ForumDao;

/**
 * Description of ForumTest
 *
 * @author Philipp
 */
class ForumTest extends AbstractEntityTest {

    /**
     * @test
     * @group entity
     * @group unit
     * @group forum
     */
    public function testPersist() {
        $dao = new ForumDao($this->getEm());
        $forum = new Forum();
        $forum->setName("3MI15-1");
        $semesterOne = new Forum();
        $semesterOne->setName("1. Semester");
        $semesterOne->setParentForum($forum);
        $semesterTwo = new Forum();
        $semesterTwo->setName("2. Semester");
        $semesterTwo->setParentForum($forum);
        $moduleMath = new Forum();
        $moduleMath->setName("Mathematik");
        $moduleMath->setParentForum($semesterOne);
        
        //write values into database
        $dao->persist($forum, $this->getTranslator(), false);
        $dao->persist($semesterOne, $this->getTranslator(), false);
        $dao->persist($semesterTwo, $this->getTranslator(), false);
        $dao->persist($moduleMath, $this->getTranslator(), true);    //true, because of saving values finally

        //read from database
        $loadedForum = $dao->findOneById($forum->getId());
        $loadedSemesterOne = $dao->findOneById($semesterOne->getId());
        $loadedSemesterTwo = $dao->findOneById($semesterTwo->getId());
        $loadedModuleMath = $dao->findOneById($moduleMath->getId());

        //testing values
        $this->assertEquals($loadedForum->getId(), $forum->getId());
        $this->assertEquals($loadedSemesterOne->getId(), $semesterOne->getId());
        $this->assertEquals($loadedSemesterTwo->getId(), $semesterTwo->getId());
        $this->assertEquals($loadedModuleMath->getId(), $moduleMath->getId());
        $this->assertTrue($loadedSemesterOne->getParentForum() === $loadedForum);
        $this->assertTrue($loadedSemesterTwo->getParentForum() === $loadedForum);
        $this->assertTrue($loadedModuleMath->getParentForum() === $loadedSemesterOne);
        $this->assertTrue($loadedForum->getSubForum()->get(0) === $loadedSemesterOne);
        $this->assertTrue($loadedForum->getSubForum()->get(1) === $loadedSemesterTwo);
        $this->assertTrue($loadedSemesterOne->getSubForum()->get(0) === $loadedModuleMath);
        $this->assertFalse($loadedForum->getSubForum()->get(0) === $loadedSemesterTwo);
        $this->assertFalse($loadedForum->getSubForum()->get(1) === $loadedSemesterOne);
        $this->assertFalse($loadedSemesterOne->getSubForum()->get(1) === $loadedModuleMath);
    }

}
