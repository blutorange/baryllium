<?php

namespace Servlet;

require_once '../../private/bootstrap.php';

use \Dao\UserDao;

class CheckUserMail extends AbstractRestServlet {
    public function rest(array & $requestData, array & $responseData) : int {
        $code = 200;
        $mail = $requestData['mail'];
        $exists = false;
        if (isset($mail)) {
            $exists = (new UserDao($this->getEm()))->findOneByField('mail', $mail) != null;
            $code = $exists ? 412 : 200;
        }
        else {
            $this->setError($responseData, 'Illegal request', 'No email given.');
            $code = 400;
        }
        $responseData['exists'] = $exists;
        $responseData['mail'] = $mail;
        return $code;
    }
}

(new CheckUserMail())->process();