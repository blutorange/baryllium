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
use Symfony\Component\Validator\Exception\ValidatorException;

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
            PlaceholderTranslator $translator) : array {
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
        return self::processUser($userProxy, $tutorialGroupProxy, $studentId,
            $password, $em, $translator, $tutorialGroupLesson,
            \time() - self::LESSON_PAST, \time() + self::LESSON_FUTURE,
            $logger, true, true);
    }
    
    public static function updateScheduleForUser(User $user,
            EntityManagerInterface $em, Logger $logger,
            PlaceholderTranslator $translator, int $timeStart = null,
            int $timeEnd = null) : array {
        $tutorialGroup = $user->getTutorialGroup();
        if ($tutorialGroup === null) {
            throw new CampusDualException('Cannot update Campus Dual schedule for user, user does not belong to a tutorial group.');
        }
        return self::updateScheduleForTutorialGroup($tutorialGroup, $em,
                $logger, $translator, $timeStart, $timeEnd);
    }
    
    public static function updateScheduleForTutorialGroup(
            TutorialGroup $tutorialGroup, EntityManagerInterface $em,
            Logger $logger, PlaceholderTranslator $translator,
            int $timeStart = null, int $timeEnd = null) : array {
        $tutorialGroupLesson = [];
        $userProxy = Dao::user($em)->findOneActiveWithCampusDualLoginByTutorialGroup($tutorialGroup);
        if ($userProxy === null) {
            throw new CampusDualException('Cannot update Campus Dual schedule for user, no user with credentials for the user\'s tutorial group exists.');
        }
        $tutorialGroupProxy = $userProxy->getTutorialGroup();
        return self::processUser($userProxy, $tutorialGroupProxy, $userProxy->getStudentId(),
                $userProxy->getPasswordCampusDual(), $em, $translator,
                $tutorialGroupLesson,
                $timeStart ?? (\time() - self::LESSON_PAST),
                $timeEnd ?? (\time() + self::LESSON_FUTURE),
                $logger, true, false);
    }
    
    public static function updateExamForUser(User $user,
            EntityManagerInterface $em, Logger $logger,
            PlaceholderTranslator $translator) : array {
        $tutorialGroupLesson = [];
        $studentId = $user->getStudentId();
        $password = $user->getPasswordCampusDual();
        if ($studentId === null || $password === null) {
            throw new CampusDualException("Cannot update Campus Dual for user, no Campus Dual credentials exist.");
        }
        return self::processUser($user, null, $studentId, $password, $em, $translator,
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
            bool $updateLesson, bool $updateExam) : array {
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
        $errors = [];
        if ($updateExam) {
            $logger->debug($userProxy->getId(), 'Syncing exams...');
            self::syncExam($userProxy, $data['exams'], Dao::exam($em), $errors,
                    $translator);
        }
        if ($updateLesson) {
            $logger->debug($tutorialGroupProxy->getId(), 'Syncing lessons...');
            self::syncLesson($tutorialGroupProxy, $data['lessons'],
                    Dao::lesson($em), $errors, $lessonStart, $lessonEnd, $translator);
            $tutorialGroupLesson[$tutorialGroupProxy->getId()] = true;
        }
        self::logEmErrors($errors, $logger);
        return $errors;
    }

    /**
     * @param User|Proxy $userProxy
     * @param Exam[] $newExams
     * @param ExamDao $dao
     * @param Logger $logger
     * @param PlaceholderTranslator $translator
     */
    private static function syncExam(User $userProxy, array $newExams,
            ExamDao $dao, array & $errors, PlaceholderTranslator $translator) {
        // Get all existing exams.
        $oldExamsIndex = self::createOldExamsIndex($userProxy, $dao);
        // Check which exams are new and which need to be updated.
        foreach ($newExams as $newExam) {
            $oldExam = self::findAndRemoveExam($oldExamsIndex, $newExam);
            if ($oldExam === null) {
                // No old exam to update, create a new entity.
                $newExam->setUser($userProxy);
                $dao->queue($newExam);
            }
            else {
                // Old exam exists, update its details.
                self::updateExam($oldExam, $newExam);
            }
        }
        // Remove old lessons that were deleted.
        foreach ($oldExamsIndex as $oldExams) {
            $dao->removeAll($oldExams);
        }
        // Persist entities and check for errrors.
        $dao->persistQueue($translator, false, $errors);
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
            array $newLessons, LessonDao $dao, array & $errors, int $lessonStart,
            int $lessonEnd, PlaceholderTranslator $translator) {
        // Get all existing lessons.
        $oldLessonsIndex = self::createOldLessonsIndex($tutorialGroupProxy,
                $dao, $lessonStart, $lessonEnd);
        // Check which lessons are new and which have changed.
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
        $dao->persistQueue($translator, false, $errors);
    }
    
    private static function createOldExamsIndex(User $userProxy, ExamDao $dao) : array {
        /* @var $oldExams Exam[] */
        $oldExams = $dao->findAllByUserId($userProxy->getId());
        // Most likely the exam ID is unique for each user, but I cannot be
        // certain.
        $oldExamsIndex = [];
        foreach ($oldExams as $oldExam) {
            $oldExamsIndex[$oldExam->getExamId()] []= $oldExam;
        }
        return $oldExamsIndex;
    }   
    
    /**
     * 
     * @param TutorialGroup $tutorialGroupProxy
     * @param LessonDao $dao
     * @return array array&lt;int,Lesson[]&gt;
     */
    private static function createOldLessonsIndex(TutorialGroup $tutorialGroupProxy,
            LessonDao $dao, int $lessonStart, int $lessonEnd) : array {
        /* @var $oldLessons Lesson[] */
        $oldLessons = $dao->findAllByTutorialGroupAndRangeTimestamp(
                $tutorialGroupProxy, $lessonStart, $lessonEnd);
        $oldLessonsIndex = [];
        foreach ($oldLessons as $lesson) {
            $timestamp = $lesson->getStart()->getTimestamp();
            $oldLessonsIndex[$timestamp] []= $lesson;
        }
        return $oldLessonsIndex;
    }
    
    private static function findAndRemoveExam(array & $oldExamsIndex, Exam $newExam) {
        $id = $newExam->getExamId();
        $oldExams = $oldExamsIndex[$id] ?? null;
        if (empty($oldExams)) {
            return null;
        }
        // PHP arrays don't behave like arrays: unset index 0 of array [0,1]
        // and we are left with an associative array [1=>1]. So we need to get
        // some valid index.
        foreach ($oldExams as $key => $oldExam) {
            unset($oldExamsIndex[$id][$key]);
            return $oldExam;
        }
    }
    
    private static function findAndRemoveLesson(array & $oldLessonsIndex, Lesson $newLesson) {
        $timestamp = $newLesson->getStart()->getTimestamp();
        $oldLessons = $oldLessonsIndex[$timestamp] ?? null;
        if (empty($oldLessons)) {
            return null;
        }
        // PHP arrays don't behave like arrays: unset index 0 of array [0,1]
        // and we are left with an associative array [1=>1]. So we need to get
        // some valid index.
        foreach ($oldLessons as $index => $oldLesson) {
            if ($oldLesson->getTitle() === $newLesson->getTitle()) {
                $found = $index;
                break;
            }
            $found = $index;
        }
        $oldLesson = $oldLessons[$found];
        unset($oldLessonsIndex[$timestamp][$found]);
        return $oldLesson;
    }

    private static function updateExam(Exam $oldExam, Exam $newExam) {
        if ($oldExam->getAnnounced() === null) {
            $oldExam->setAnnounced($newExam->getAnnounced());
        }
        else if ($oldExam->getAnnounced()->getTimestamp() !== $newExam->getAnnounced()->getTimestamp()) {
            $oldExam->setAnnounced($newExam->getAnnounced());
        }
        if($oldExam->getMarked() === null) {
            $oldExam->setMarked($newExam->getMarked());
        }
        else if ($oldExam->getMarked()->getTimestamp() !== $newExam->getMarked()->getTimestamp()) {
            $oldExam->setMarked($newExam->getMarked());
        }
        $oldExam->setIsSubscribed($newExam->getIsSubscribed())
                ->setMark($newExam->getMark())
                ->setTitle($newExam->getTitle());
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
