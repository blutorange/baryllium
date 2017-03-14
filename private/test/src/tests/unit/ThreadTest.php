<?php

namespace UnitTest;

use Dao\ForumDao;
use Dao\ThreadDao;
use Entity\Forum;
use Entity\Thread;

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
