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

namespace Moose\Extension\CampusDual;

use Doctrine\DBAL\Types\ProtectedString;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Handler\Proxy;
use Moose\Context\Context;
use Moose\Dao\Dao;
use Moose\Dao\ExamDao;
use Moose\Dao\LessonDao;
use Moose\Entity\Exam;
use Moose\Entity\Lesson;
use Moose\Entity\TutorialGroup;
use Moose\Entity\User;
use Moose\Log\Logger;
use Moose\Util\PlaceholderTranslator;

/**
 * Description of CampusDualUtil
 *
 * @author madgaksha
 */
class CampusDualUtil {
    const LESSON_PAST = 7*24*60*60; // A week
    const LESSON_FUTURE = 120*24*60*60; // A semester

    private function __construct() {}
    
    /**
     * 
     * @param User|Proxy $userProxy
     * @param PlaceholderTranslator $translator
     * @throws CampusDualException
     */
    public static function updateForUser(User $userProxy,
            EntityManagerInterface $em, Logger $logger,
            PlaceholderTranslator $translator) {
        $tutorialGroupLesson = [];
        $tutorialGroupProxy = $userProxy->getTutorialGroup();
        if ($tutorialGroupProxy === null) {
            throw new CampusDualException('Cannot update Campus Dual for user, does not belong to a tutorial group.');
        }
        $studentId = $userProxy->getStudentId();
        $password = $userProxy->getPasswordCampusDual();
        if ($studentId === null || $password === null) {
            throw new CampusDualException("Cannot update Campus Dual for user, no Campus Dual credentials exist.");
        }
        self::processUser($userProxy, $tutorialGroupProxy, $studentId,
            $password, $em, $translator, $tutorialGroupLesson,
            \time() - self::LESSON_PAST, \time() + self::LESSON_FUTURE,
            $logger, true, true);
    }
    
    public static function updateScheduleForUser(User $user,
            EntityManagerInterface $em, Logger $logger,
            PlaceholderTranslator $translator) {
        $tutorialGroupLesson = [];
        $tutorialGroup = $user->getTutorialGroup();
        if ($tutorialGroup === null) {
            throw new CampusDualException('Cannot update Campus Dual schedule for user, user does not belong to a tutorial group.');
        }
        $userProxy = Dao::user($em)->findOneActiveWithCampusDualLoginForTutorialGroup($tutorialGroup);
        if ($userProxy === null) {
            throw new CampusDualException('Cannot update Campus Dual schedule for user, no user with credentials for the user\'s tutorial group exists.');
        }
        $tutorialGroupProxy = $userProxy->getTutorialGroup();
        self::processUser($userProxy, $tutorialGroupProxy, $userProxy->getStudentId(),
                $userProxy->getPasswordCampusDual(), $em, $translator,
                $tutorialGroupLesson,  \time() - self::LESSON_PAST,
                \time() + self::LESSON_FUTURE, $logger, true, false);
    }
    
    public static function updateExamForUser(User $user,
            EntityManagerInterface $em, Logger $logger,
            PlaceholderTranslator $translator) {
        $tutorialGroupLesson = [];
        $studentId = $user->getStudentId();
        $password = $user->getPasswordCampusDual();
        if ($studentId === null || $password === null) {
            throw new CampusDualException("Cannot update Campus Dual for user, no Campus Dual credentials exist.");
        }
        self::processUser($user, null, $studentId, $password, $em, $translator,
                $tutorialGroupLesson, \time() - self::LESSON_PAST,
                \time() + self::LESSON_FUTURE, $logger, false, true);
    }
    
    /**
     * @param User|Proxy $userProxy
     * @param string $studentId
     * @param ProtectedString $passwordCampusDual
     * @param TutorialGroup|Proxy $tutorialGroupProxy
     * @param EntityManagerInterface $em
     */
    public static function processUser(User $userProxy,
            $tutorialGroupProxy, string $studentId,
            ProtectedString $passwordCampusDual, EntityManagerInterface $em,
            PlaceholderTranslator $translator, array & $tutorialGroupLesson,
            int $lessonStart, int $lessonEnd, Logger $logger,
            bool $updateLesson, bool $updateExam) {
        $updateLesson = $updateLesson && $tutorialGroupProxy !== null && !isset($tutorialGroupLesson[$tutorialGroupProxy->getId()]);
        try {
            $data = CampusDualLoader::perform($studentId, $passwordCampusDual,
                    function(CampusDualLoader $loader) use ($updateLesson, $updateExam, $lessonStart, $lessonEnd) {
                /* @var $loader CampusDualLoader */
                $data = [];
                if ($updateLesson) {
                    $data['lessons'] = $loader->getTimeTable($lessonStart, $lessonEnd);
                }
                if ($updateExam) {
                    $exams = [];
                    $loader->getExamSubscriptions($exams);
                    $loader->getExamResults($exams);
                    $data['exams'] = $exams;
                }
                return $data;
            });
        }
        catch (CampusDualException $e) {
            $uid = $userProxy->getId();
            $logger->error($e, "Failed to update Campus Dual for user $uid");
            // If the password is wrong, delete the password so that we do not
            // make more than one wrong attempt and prevent their account getting
            // locked.            
            if ($e->is(CampusDualException::FLAG_ACCESS_DENIED)) {
                $userProxy->setPasswordCampusDual(null);
                $em->flush($userProxy);
            }
            throw $e;
        }
        if ($updateExam) {
            $logger->debug($userProxy->getId(), 'Syncing exams...');
            self::updateExam($userProxy, $data['exams'], Dao::exam($em), $translator);
        }
        if ($updateLesson) {
            $logger->debug($tutorialGroupProxy->getId(), 'Syncing lessons...');
            self::syncLesson($tutorialGroupProxy, $data['lessons'],
                    Dao::lesson($em), $logger, $lessonStart, $lessonEnd, $translator);
            $tutorialGroupLesson[$tutorialGroupProxy->getId()] = true;
        }
    }

    /**
     * 
     * @param User|Proxy $userProxy
     * @param Exam[] $exams
     * @param ExamDao $examDao
     */
    private static function updateExam(User $userProxy, array $exams, ExamDao $examDao, PlaceholderTranslator $translator) {
        $examDao->removeAllByUserId($userProxy->getId());
        foreach ($exams as $exam) {
            $exam->setUser($userProxy);
            $examDao->queue($exam);
        }
        $errors = $examDao->persistQueue($translator);
        if (\sizeof($errors) > 0) {
            Context::getInstance()->getLogger()->log("Failed to update exams.");
            foreach ($errors as $error) {
                Context::getInstance()->getLogger()->log($error);
            }
        }
    }
    
    /**
     * Update lessons when their details have changed, delete them otherwise.
     * Two lessons considered the same when their start time matches.
     * @param TutorialGroup|Proxy $tutorialGroupProxy Tutorial group for the
     * lessons.
     * @param Lesson[] $newLessons Lessons retrieved that need to synced with the
     * database.
     * @param LessonDao $dao For interacting with the database.
     */
    private static function syncLesson(TutorialGroup $tutorialGroupProxy,
            array $newLessons, LessonDao $dao, Logger $logger, int $lessonStart,
            int $lessonEnd, PlaceholderTranslator $translator) {
        $oldLessonsIndex = self::createOldLessonIndex($tutorialGroupProxy,
                $dao, $lessonStart, $lessonEnd);
        foreach ($newLessons as $newLesson) {
            /* @var $oldLesson Lesson */
            $oldLesson = self::findAndRemoveLesson($oldLessonsIndex, $newLesson);
            if ($oldLesson === null) {
                // No old lesson to update, create a new entity.
                $newLesson->setTutorialGroup($tutorialGroupProxy);
                $dao->queue($newLesson);
            }
            else {
                // Old lesson exists, we simply update its details.
                self::updateLesson($oldLesson, $newLesson);
            }            
        }
        // Remove old lessons that were deleted.
        foreach ($oldLessonsIndex as $oldLessons) {
            $dao->removeAll($oldLessons);
        }
        self::logEmErrors($dao->persistQueue($translator), $logger);
    }
    
    /**
     * 
     * @param TutorialGroup $tutorialGroupProxy
     * @param LessonDao $dao
     * @return array array&lt;int,Lesson[]&gt;
     */
    private static function createOldLessonIndex(TutorialGroup $tutorialGroupProxy,
            LessonDao $dao, int $lessonStart, int $lessonEnd) : array {
        /* @var $oldLessons Lesson[] */
        $oldLessons = $dao->findAllByTutorialGroupAndRangeTimestamp(
                $tutorialGroupProxy, $lessonStart, $lessonEnd);
        $oldLessonsIndex = [];
        foreach ($oldLessons as $lesson) {
            $timestamp = $lesson->getStart()->getTimestamp();
            if (!isset($oldLessonsIndex)) {
                $oldLessonsIndex = [$timestamp => $lesson];
            }
            else {
                $oldLessonsIndex[$timestamp] []= $lesson;
            }
        }
        return $oldLessonsIndex;
    }
    
    private static function findAndRemoveLesson(array & $oldLessonsIndex, Lesson $newLesson) {
        $timestamp = $newLesson->getStart()->getTimestamp();
        $oldLessons = $oldLessonsIndex[$timestamp] ?? null;
        if (empty($oldLessons)) {
            return null;
        }
        // Most common case is just one lesson in a timeslot.
        if (\sizeof($oldLessons) === 1) {
            $oldLesson = $oldLessons[0];
            unset($oldLessonsIndex[$timestamp]);
            return $oldLesson;
        }
        $found = 0;
        foreach ($oldLessons as $index => $oldLesson) {
            if ($oldLesson->getTitle() === $newLesson->getTitle()) {
                $found = $index;
                break;
            }
        }
        $oldLesson = $oldLessons[$found];
        unset($oldLessonsIndex[$timestamp][$found]);
        return $oldLesson;
    }
    
    private static function updateLesson(Lesson $oldLesson, Lesson $newLesson) {
        if ($newLesson->getEnd()->getTimestamp() !== $oldLesson->getEnd()->getTimestamp()) {
            $oldLesson->setEnd($newLesson->getEnd());
        }
        $oldLesson->setDescription($newLesson->getDescription())
                ->setInstructor($newLesson->getInstructor())
                ->setRemarks($newLesson->getRemarks())
                ->setRoom($newLesson->getRoom())
                ->setSecondInstructor($newLesson->getSecondInstructor())
                ->setTitle($newLesson->getTitle());
    }

    private static function logEmErrors(array $errors, Logger $logger) {
        if (\sizeof($errors) > 0) {
            $logger->error("Failed to update lessons.");
            foreach ($errors as $error) {
                $logger->error($error);
            }
        }
    }
}
