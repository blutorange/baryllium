<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Servlet;

use \Controller\AbstractController;

/**
 * Description of AbstractServlet
 *
 * @author madgaksha
 */
abstract class AbstractRestServlet extends AbstractController {
    
    public final function doGet() {
        $requestData = $this->getData();
        $responseData = array();
        $code = 500;
        try {
            $code = $this->rest($requestData, $responseData);
        }
        catch (\Throwable $e) {
            error_log('Unhandled rest servlet error: ' . $e);
            $this->setError($responseData, "Internal server error", $e->getMessage());
            $code = 500;
        }
        if ($code === NULL) {
            $code = 500;    
        }
        // When there is an error, delete all other data.
        if (isset($responseData['error'])) {
            $responseData = ['error' => $responseData['error']];
        }
        http_response_code($code);
        echo json_encode($responseData);
        error_log("code");
        error_log($code);
    }

    protected function setError(array & $reponseData, string $message, string $details) {
        $reponseData['error'] = ['message' => $message, 'details' => $details];
    }

    public final function doPost() {
        doGet();   
    }
    
    protected abstract function rest(array & $requestData, array & $responseData) : int;
}