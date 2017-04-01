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

namespace Moose\Controller;

use Dao\AbstractDao;
use Entity\Course;
use Entity\FieldOfStudy;
use Entity\Forum;
use Keboola\Csv\CsvFile;
use Moose\Controller\AbstractController;
use Moose\Web\HttpRequestInterface;
use Moose\Web\HttpResponseInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Ui\Message;
use Util\CmnCnst;
use Util\CollectionUtil;

class SetupImportController extends BaseController {
    
    public function doGet(HttpResponseInterface $response, HttpRequestInterface $request) {
        $this->renderUi();
    }
    
    private function renderUi(array & $additionalFoslist = null) {
        $foslist = AbstractDao::fieldOfStudy($this->getEm(CmnCnst::ENTITY_MANAGER_CUSTOM_1))->findAll();
        if ($additionalFoslist !== null) {
            $foslist = \array_values(\array_merge($additionalFoslist, $foslist));
        }
        $foslist = CollectionUtil::sortByField($foslist, 'shortName');
        $this->renderTemplate("t_setup_import", ['foslist' => $foslist]);
    }

    public function doPost(HttpResponseInterface $response, HttpRequestInterface $request) {
        /* @var $files UploadedFile[] */
        $files = $request->getFiles('importcss');
        //$file = @$_FILES["importcss"];
        $success = false;
        $foslist = [];
        if (sizeof($files) === 1) {
            $csv = new CsvFile($files[0]->getRealPath());
            if ($csv !== null) {
                $success = $this->processImport($csv, $foslist);
            }
        }
        if ($success) {
            $this->renderUi($foslist);
        }
        else {
            $this->renderTemplate("t_setup_import", []);
        }
    }

    public function processImport(CsvFile $csv, array & $foslist) {
        $clist = array();
        $em1 = $this->getEm(CmnCnst::ENTITY_MANAGER_CUSTOM_1);
        $em2 = $this->getEm(CmnCnst::ENTITY_MANAGER_CUSTOM_2);
        $dao = AbstractDao::generic($em1);
        $changeCount = 0;
        foreach ($csv as $row) {
            $short = $row[0];
            $dis = \trim($row[1]);
            $subdis = \trim($row[2]);
            $foskey = "$dis:::$subdis";
            $idCourse = \trim($row[3]);
            $nameCourse = \trim($row[4]);
            $coursekey = "$idCourse:::$nameCourse";
            $fosCreated = false;
            $courseCreated = false;
            // Get field of study
            if (\array_key_exists($foskey, $foslist)) {
                $fos = $foslist[$foskey];
            }
            else {
                $fos = AbstractDao::fieldOfStudy($em1)->findOneByDisciplineAndSub($dis, $subdis);
                if ($fos === null) {
                    $fos = new FieldOfStudy();
                    ++$changeCount;
                    $fos->setDiscipline($dis);
                    $fos->setSubDiscipline($subdis);
                    $fos->setShortName($short);
                    $dao->queue($fos);
                    $fosCreated = true;
                }
                else {
                    
                }
                $foslist[$foskey] = $fos;
            }
            
            // Get course
            if (\array_key_exists($coursekey, $clist)) {
                $course = $clist[$coursekey];
            }
            else {
                $course = null;
                if (!$fosCreated) {
                    $course = AbstractDao::course($em2)->findOneByFieldOfStudyWithName($fos, $nameCourse);
                    if ($course !== null) {
                        $course = AbstractDao::course($em1)->findOneById($course->getId());
                    }
                }
                if ($course === null) {
                    $course = new Course();
                    $forum = new Forum();
                    ++$changeCount;
                    $course->setName($nameCourse);
                    $forum->setName($nameCourse);
                    $course->setForum($forum);
                    $dao->queue($forum);
                    $dao->queue($course);
                    $courseCreated = true;
                }
                $clist[$coursekey] = $course;
            }
            
            // Associate the two
            if ($courseCreated || $fosCreated) {
                $fos->addCourse($course);
            }
        }
        $errors = $dao->persistQueue($this->getTranslator());
        $this->getResponse()->addMessages($errors);
        if (\sizeof($errors) === 0) {
            $this->getResponse()->addMessage(Message::infoI18n('setup.import.complete', "setup.import.complete.details", $this->getTranslator(), ['count' => $changeCount]));
        }
        return \sizeof($errors) === 0;
    }
    
    protected function getRequiresLogin() : int {
        return self::REQUIRE_LOGIN_SADMIN;
    }
}