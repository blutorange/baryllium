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

namespace Moose\Tasks;

use Doctrine\DBAL\Types\ProtectedString;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Proxy\Proxy;
use Moose\Context\Context;
use Moose\Dao\Dao;
use Moose\Dao\ExamDao;
use Moose\Dao\LessonDao;
use Moose\Entity\Exam;
use Moose\Entity\Lesson;
use Moose\Entity\TutorialGroup;
use Moose\Entity\User;
use Moose\Extension\CampusDual\CampusDualException;
use Moose\Extension\CampusDual\CampusDualLoader;
use Moose\Extension\CampusDual\CampusDualUtil;
use Moose\Log\Logger;
use Moose\Util\PlaceholderTranslator;
use Throwable;


/**
 * Updates all exams and lessons for the users.
 * @author madgaksha
 */
class CampusDualEvent extends AbstractDbEvent implements EventInterface {

    const LESSON_PAST = 7*24*60*60; // A week
    const LESSON_FUTURE = 120*24*60*60; // A semester

    /** @var Logger */
    private $logger;
    
    private $translator;
    
    /** @var array Which tutorial groups already had their lessons updated. */
    private $tutorialGroupLesson;
    
    /** @var int */
    private $lessonStart;
    
    /** @var int */
    private $lessonEnd;

    public function __construct() {
        $this->tutorialGroupLesson = [];
        $this->translator = Context::getInstance()->getSessionHandler()->getTranslator();
        $this->logger = Context::getInstance()->getLogger();
        $this->lessonStart = \time() - self::LESSON_PAST;
        $this->lessonEnd = \time() + self::LESSON_FUTURE;
    }
    
    public function run(array $options = null) {
        /* @var $userFieldList User[] */
        /* @var $tutorialGroupProxy TutorialGroup|Proxy */
        /* @var $userProxy User|Proxy */
        // Get list of students to process, ie. those who agreed to store their
        // Campus Dual password in the system.
        $userFieldList = $this->withEm(function(EntityManagerInterface $em) {
            return Dao::user($em)->findAllActiveWithCampusDualLogin(['id', 'studentId', 'passwordCampusDual', 'tutorialGroup' => 'identity']);
        });
        foreach ($userFieldList as $userField) {
            $this->logger->info($userField['id'], 'Processing user');
            try {
                $this->withEm(function(EntityManagerInterface $em) use ($userField) {
                    $this->handleUser($em, $userField);
                });
            }
            catch (Throwable $e) {
                $this->logger->error($e, 'Failed to process user');
            }
            \sleep(1);
        }
    }

    public function getName(PlaceholderTranslator $translator) {
        return $translator->gettext('task.campusdual.exam');
    }

    private function handleUser(EntityManagerInterface $em, array $userField) {
        $tutorialGroupId = $userField['tutorialGroup'];
        $userProxy = $em->getReference(User::class, $userField['id']);
        $tutorialGroupProxy = $tutorialGroupId === null ? null : $em->getReference(TutorialGroup::class, $tutorialGroupId);
        CampusDualUtil::processUser($userProxy, $tutorialGroupProxy,
                $userField['studentId'], $userField['passwordCampusDual'],
                $em, $this->translator, $this->tutorialGroupLesson,
                $this->lessonStart, $this->lessonEnd, $this->logger,
                true, true);
    }
}