<?php

namespace Servlet;

require_once '../../private/bootstrap.php';

use \Dao\UserDao;

class CheckStudentId extends AbstractRestServlet {
    public function rest(array & $requestData, array & $responseData) : int {
        $code = 200;
        $raw = $requestData['studentid'];
        $exists = false;
        $match = [];
        $studentId = null;
        if (preg_match("/(\d{7})/u", $raw, $match) === 1) {
            $studentId = $match[1];
        }
        if (isset($studentId)) {
            $exists = (new UserDao($this->getEm()))->existsStudentId($studentId);
            $code = $exists ? 412 : 200;
        }
        else {
            $this->setError($responseData, 'Illegal request', 'No student id given.');
            $code = 400;
        }
        $responseData['exists'] = $exists;
        $responseData['studentid'] = $studentId;
        return $code;
    }
    
    protected function getRequiresLogin() : int {
        return self::REQUIRE_LOGIN_NEVER;
    }

}

(new CheckStudentId())->process();