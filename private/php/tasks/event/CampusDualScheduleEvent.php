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

use Doctrine\ORM\EntityManagerInterface;
use Moose\Context\Context;
use Moose\Dao\Dao;
use Moose\Entity\TutorialGroup;
use Moose\Entity\User;
use Moose\Extension\CampusDual\CampusDualUtil;
use Moose\Log\Logger;
use Moose\Util\PlaceholderTranslator;
use Throwable;

/**
 * Updates all exams and lessons for the users.
 * @author madgaksha
 */
class CampusDualScheduleEvent extends AbstractDbEvent implements EventInterface {
    const LESSON_PAST = 7*24*60*60; // A week
    const LESSON_FUTURE = 120*24*60*60; // A semester

    /** @var Logger */
    private $logger;

    /** @var PlaceholderTranslator */    
    private $translator;

    public function __construct() {
        $this->translator = Context::getInstance()->getSessionHandler()->getTranslator();
        $this->logger = Context::getInstance()->getLogger();
    }
    
    public function run(array $options = null) {
        /* @var $tutorialGroups TutorialGroup[] */
        /* @var $user User */
        $tutorialGroups = $this->withEm(function(EntityManagerInterface $em) {
            return Dao::tutorialGroup($em)->findAll();
        });
        foreach ($tutorialGroups as $tutorialGroup) {
            $tutName = $tutorialGroup->getCompleteName();
            $this->logger->info($tutName, 'Processing tutorial group');
            try {
                $this->withEm(function(EntityManagerInterface $em) use ($tutorialGroup) {
                    CampusDualUtil::updateScheduleForTutorialGroup(
                            $tutorialGroup, $em, $this->logger,
                            $this->translator);
                });
            }
            catch (Throwable $e) {
                $this->logger->error($e, "Failed to process schedule for tutorial group $tutName");
            }
        }
    }

    public function getName(PlaceholderTranslator $translator) {
        return $translator->gettext('task.campusdual.schedule');
    }
}