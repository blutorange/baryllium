<?php

namespace Servlet;

require_once '../../bootstrap.php';

use \Dao\UserDao;

class CheckUsername extends AbstractRestServlet {
    public function rest(array & $requestData, array & $responseData) : int {
        $code = 200;
        $username = $requestData['username'];
        $exists = false;
        if (isset($username)) {
            $exists = (new UserDao($this->getEm()))->findOneByField('username', $username) != null;
            $code = $exists ? 412 : 200;
        }
        else {
            $this->setError($responseData, 'Illegal request', 'No username given.');
            $code = 400;
        }
        $responseData['exists'] = $exists;
        $responseData['username'] = $username;
        return $code;
    }
}

(new CheckUsername())->process();