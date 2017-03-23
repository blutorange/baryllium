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

namespace Controller;

use Dao\AbstractDao;
use Entity\Course;
use Entity\Post;

require_once '../../private/bootstrap.php';

/**
 * Description of forum
 *
 * @author Philipp
 */
class PostController extends AbstractController {

    public function doGet() {
        // TODO handle anonymous user who hasn't got a tutorial group

        $threadId = $this->getParam('thread');
        // TODO This is the real code to be used.
//        $user = $this->getSessionHandler()->getUser();
        $user = AbstractDao::user($this->getEm())->findOneById(3);
        $courseList = $user->getTutorialGroup()->getFieldOfStudy()->getCourseList();
        $courseArray = $courseList->toArray();
        usort($courseArray, Course::getComparatorByNameAsc());
        $bPostExists = false;
        $threadList = array();
        $postList = array();

        foreach ($courseList as $course) {
            if ($course->getForum()->getId() == $threadId) {
                $threadList = $course->getForum()->getThreadList();
            }
        }

        foreach ($threadList as $thread) {
            if ($thread->getId() == $threadId) {
                $postList = $thread->getPostList();
                $bPostExists = true;
            }
        }

//        krsort($courseList);
//        $forumList = array();
//        
//        foreach($courseList as $fos){
//            array_push($forumList, $fos->getForum()->getName().";".$fos->getForum()->getId());
//        }
        if ($user !== null && $bPostExists == true) {
            $this->renderTemplate('t_postlist', ['postList' => $postList]);
        }
        else {
            $this->renderTemplate('t_forumlist', ['courseList' => $courseArray]);
        }
    }

    public function doPost() {
        $post = new Post();
        
        $threadId = $this->getQueryParam('thread');
        $thread = AbstractDao::thread($this->getEm())->findOneById($threadId);

        $user = AbstractDao::user($this->getEm())->findOneById(3);

        $userId = $user->getId();
        $title = $this->getParam('title');
        $content = $this->getParam('content');

        $post->setUser($user);
        $post->setTitle($title);
        $post->setContent($content);
        $thread->addPost($post);
        
        $dao = AbstractDao::generic($this->getEm());
//        $dao->persist($post, $this->getTranslator());
//        $dao->persist($thread, $this->getTranslator());
        
        if ($dao->persist($post, $this->getTranslator()) && $dao->persist($thread, $this->getTranslator())) {
            $this->redirect('./post.php?thread='.$threadId);
            $this->renderTemplate('t_postlist');
        }
    }

}

(new PostController())->process();
