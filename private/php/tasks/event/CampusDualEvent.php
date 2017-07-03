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
use Moose\Util\DebugUtil;
use Moose\Util\PlaceholderTranslator;


/**
 * Updates all exams and lessons for the users.
 * @author madgaksha
 */
class CampusDualEvent extends AbstractDbEvent implements EventInterface {

    private $translator;
    /** @var array Which tutorial groups already had their lessons updated. */
    private $tutorialGroupLesson;
    
    public function process(Context $context, array &$options = null) {
        $this->tutorialGroupLesson = [];
        $this->translator = Context::getInstance()->getSessionHandler()->getTranslatorFor('en');
        /* @var $users User[] */
        /* @var $tutorialGroupProxy TutorialGroup|Proxy */
        /* @var $userProxy User|Proxy */
        $userFieldList = $this->withEm(function(EntityManagerInterface $em) {
            return Dao::user($em)->findAllActiveWithCampusDualLogin(['id', 'studentId', 'passwordCampusDual', 'tutorialGroup' => 'identity']);
        });
        foreach ($userFieldList as $userField) {
            $tutorialGroupId = $userField['tutorialGroup'];
            $this->withEm(function(EntityManagerInterface $em) use ($userField, $tutorialGroupId) {
                $userProxy = $em->getReference(User::class, $userField['id']);
                $tutorialGroupProxy = $tutorialGroupId === null ? null : $em->getReference(TutorialGroup::class, $tutorialGroupId);
                try {
                    $this->processUser($userProxy, $tutorialGroupProxy, $userField['studentId'], $userField['passwordCampusDual'], $em);
                }
                catch (CampusDualException $exception) {
                    DebugUtil::log("Failed to update Campus Dual for user ${$userProxy->getId()}): $exception");
                    if ($exception->is(CampusDualException::FLAG_ACCESS_DENIED)) {
                        $userProxy->setPasswordCampusDual(null);
                    }
                }
                catch (Exception $other) {
                    DebugUtil::log("Failed to update Campus Dual for user ${$userProxy->getId()}): $other");
                }
            });
            $this->tutorialGroupLesson[$tutorialGroupId] = true;
            \sleep(1);
        }
    }

    public function getName(PlaceholderTranslator $translator) {
        return $translator->gettext('task.campusdual.exam');
    }

    /**
     * @param User|Proxy $userProxy
     * @param string $studentId
     * @param ProtectedString $passwordCampusDual
     * @param EntityManagerInterface $em
     */
    private function processUser(User $userProxy,
            TutorialGroup $tutorialGroupProxy, string $studentId,
            ProtectedString $passwordCampusDual, EntityManagerInterface $em) {
        $data = CampusDualLoader::perform($studentId, $passwordCampusDual, function(CampusDualLoader $loader) {
            /* @var $loader CampusDualLoader */
            return [
                'lessons' => $loader->getTimeTable(),
                'exams' => $loader->getExamResults()
            ];
        });
        $this->processExam($userProxy, $data['exams'], Dao::exam($em));
        if ($tutorialGroupProxy !== null && !isset($this->tutorialGroupLesson[$tutorialGroupProxy->getId()])) {
            $this->processLesson($tutorialGroupProxy, $data['lessons'], Dao::lesson($em));
        }
    }

    /**
     * 
     * @param User|Proxy $userProxy
     * @param Exam[] $exams
     * @param ExamDao $examDao
     */
    public function processExam(User $userProxy, array $exams, ExamDao $examDao) {
        $examDao->removeAllByUserId($userProxy->getId());
        foreach ($exams as $exam) {
            $exam->setUser($userProxy);
            $examDao->queue($exam);
        }
        $errors = $examDao->persistQueue($this->translator);
        if (\sizeof($errors) > 0) {
            DebugUtil::log("Failed to update exams.");
            foreach ($errors as $error) {
                DebugUtil::log($error);
            }
        }
    }
    
    /**
     * 
     * @param TutorialGroup|Proxy $tutorialGroupProxy Tutorial group proxy.
     * @param Lesson[] $lessons
     * @param LessonDao $lessonDao
     */
    public function processLesson(TutorialGroup $tutorialGroupProxy, array $lessons, LessonDao $lessonDao) {
        $lessonDao->removeAllByTutorialGroupId($tutorialGroupProxy->getId());
        foreach ($lessons as $lesson) {
            $lesson->setTutorialGroup($tutorialGroupProxy);
            $lessonDao->queue($lesson);
        }
        $errors = $lessonDao->persistQueue($this->translator);
        if (\sizeof($errors) > 0) {
            DebugUtil::log("Failed to update lessons.");
            foreach ($errors as $error) {
                DebugUtil::log($error);
            }
        }
    }
}