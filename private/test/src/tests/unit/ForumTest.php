<?php

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
