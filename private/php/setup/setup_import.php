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

namespace Controller;

use Controller\AbstractController;
use Dao\AbstractDao;
use Entity\Course;
use Entity\FieldOfStudy;
use Entity\Forum;
use Keboola\Csv\CsvFile;

require_once '../../bootstrap.php';

class SetupImportController extends AbstractController {
    
    public function doGet() {
        $dao = AbstractDao::fieldOfStudy($this->getEm());
        $foslist = $dao->findAll();
        $this->renderTemplate("t_setup_import", ['foslist' => $foslist]);
    }

    public function doPost() {
        $file = @$_FILES["importcss"];
        if ($file !== null && array_key_exists('tmp_name', $file)) {
            $csv = new CsvFile($file['tmp_name']);
            if ($csv !== null) {
                $this->processImport($csv);
            }
        }
        $this->redirect('./setup_import.php');
    }

    public function processImport(CsvFile $csv) {
        $foslist = array();
        $clist = array();
        $dao = AbstractDao::generic($this->getEm());
        foreach ($csv as $row) {
            $short = $row[0];
            $dis = $row[1];
            $subdis = $row[2];
            $foskey = "$dis:::$subdis";
            $idCourse = trim($row[3]);
            $nameCourse = $row[4];
            $coursekey = "$idCourse--$nameCourse";
            // Get course
            if (array_key_exists($coursekey, $clist)) {
                $course = $clist[$coursekey];
            }
            else {
                $course = new Course();
                $forum = new Forum();
                $course->setName($nameCourse);
                $forum->setName($nameCourse);
                $course->setForum($forum);
                $dao->queue($forum);
                $dao->queue($course);
                $clist[$coursekey] = $course;
            }
            // Get field of study
            if (array_key_exists($foskey, $foslist)) {
                $fos = $foslist[$foskey];
            }
            else {
                $fos = new FieldOfStudy();
                $fos->setDiscipline($dis);
                $fos->setSubDiscipline($subdis);
                $fos->setShortName($short);
                $dao->queue($fos);
                $foslist[$foskey] = $fos;
            }
            // Associate the two
            $fos->addCourse($course);
        }
        $dao->persistQueue($this->getTranslator());
    }
}

(new SetupImportController())->process();
