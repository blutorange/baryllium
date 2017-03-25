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

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Servlet;

use Controller\AbstractController;
use Controller\HttpResponseInterface;
use Throwable;

/**
 * Description of AbstractServlet
 *
 * @author madgaksha
 */
abstract class AbstractRestServlet extends AbstractController {
    
    public final function doGet(HttpResponseInterface $response) {
        $requestData = $this->getData();
        $responseData = array();
        $code = 500;
        try {
            $code = $this->rest($requestData, $responseData);
        }
        catch (Throwable $e) {
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

    public final function doPost(HttpResponseInterface $response) {
        doGet();   
    }
    
    protected abstract function rest(array & $requestData, array & $responseData) : int;
}