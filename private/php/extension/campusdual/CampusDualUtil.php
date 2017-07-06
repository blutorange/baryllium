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
use Moose\Dao\Dao;
use Moose\Dao\ExamDao;
use Moose\Dao\LessonDao;
use Moose\Entity\Exam;
use Moose\Entity\Lesson;
use Moose\Entity\TutorialGroup;
use Moose\Entity\User;
use Moose\Util\DebugUtil;
use Moose\Util\PlaceholderTranslator;

/**
 * Description of CampusDualUtil
 *
 * @author madgaksha
 */
class CampusDualUtil {
    private function __construct() {}
    
    /**
     * 
     * @param User|Proxy $userProxy
     * @param PlaceholderTranslator $translator
     * @throws CampusDualException
     */
    public static function updateForUser(User $userProxy, EntityManagerInterface $em, PlaceholderTranslator $translator) {
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
        try {
            self::processUser($userProxy, $tutorialGroupProxy, $studentId,
                $password, $em, $translator, $tutorialGroupLesson, true, true);
        }
        catch (CampusDualException $e) {
            if ($e->is(CampusDualException::FLAG_ACCESS_DENIED)) {
                $userProxy->setPasswordCampusDual(null);
            }
            throw $e;
        }
    }
    
    public static function updateScheduleForUser(User $user, EntityManagerInterface $em, PlaceholderTranslator $translator) {
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
                $tutorialGroupLesson, true, false);
    }
    
    public static function updateExamForUser(User $userProxy, EntityManagerInterface $em, PlaceholderTranslator $translator) {
        $tutorialGroupLesson = [];
        $studentId = $userProxy->getStudentId();
        $password = $userProxy->getPasswordCampusDual();
        if ($studentId === null || $password === null) {
            throw new CampusDualException("Cannot update Campus Dual for user, no Campus Dual credentials exist.");
        }
        self::processUser($userProxy, null, $studentId, $password, $em,
                $translator, $tutorialGroupLesson, false, true);
    }
    
    /**
     * @param User|Proxy $userProxy
     * @param string $studentId
     * @param ProtectedString $passwordCampusDual
     * @param TutorialGroup|Proxy $tutorialGroupProxy
     * @param EntityManagerInterface $em
     */
    private static function processUser(User $userProxy,
            $tutorialGroupProxy, string $studentId,
            ProtectedString $passwordCampusDual, EntityManagerInterface $em,
            PlaceholderTranslator $translator, array & $tutorialGroupLesson,
            bool $updateLesson = true, bool $updateExam = true) {
        $data = CampusDualLoader::perform($studentId, $passwordCampusDual, function(CampusDualLoader $loader) use ($updateLesson, $updateExam) {
            /* @var $loader CampusDualLoader */
            $data = [];
            if ($updateLesson) {
                $data['lessons'] = $loader->getTimeTable();
            }
            if ($updateExam) {
                $data['exams'] = $loader->getExamResults();
            }
            return $data;
        });
        if ($updateExam) {
            self::updateExam($userProxy, $data['exams'], Dao::exam($em), $translator);
        }
        if ($updateLesson && $tutorialGroupProxy !== null && !isset($tutorialGroupLesson[$tutorialGroupProxy->getId()])) {
            self::updateLesson($tutorialGroupProxy, $data['lessons'], Dao::lesson($em), $translator);
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
     * 
     * @param TutorialGroup|Proxy $tutorialGroupProxy Tutorial group proxy.
     * @param Lesson[] $lessons
     * @param LessonDao $lessonDao
     */
    private static function updateLesson(TutorialGroup $tutorialGroupProxy, array $lessons, LessonDao $lessonDao, PlaceholderTranslator $translator) {
        $lessonDao->removeAllByTutorialGroupId($tutorialGroupProxy->getId());
        foreach ($lessons as $lesson) {
            $lesson->setTutorialGroup($tutorialGroupProxy);
            $lessonDao->queue($lesson);
        }
        $errors = $lessonDao->persistQueue($translator);
        if (\sizeof($errors) > 0) {
            Context::getInstance()->getLogger()->log("Failed to update lessons.");
            foreach ($errors as $error) {
                Context::getInstance()->getLogger()->log($error);
            }
        }
    }
}
