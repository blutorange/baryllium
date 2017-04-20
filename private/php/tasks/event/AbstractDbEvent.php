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

use Closure;
use Moose\Context\Context;
use Moose\Dao\AbstractDao;
use Doctrine\ORM\EntityManager;
use Moose\Entity\ScheduledEvent;
use Throwable;


/**
 * Updates all meals from the configured dining halls.
 * @author madgaksha
 */
abstract class AbstractDbEvent implements EventInterface {
    
    public final function run(array & $options = null) {
        $this->process(Context::getInstance(), $options);
    }

    protected abstract function process(Context $context, array & $options = null);

    /**
     * @param Closure $callback function(EntityManager $em, GenericDao $dao){}
     */
    protected function withEm(Closure $callback) {
        $em = Context::getInstance()->getEm();
        $dao = AbstractDao::generic($em);
        $translator = Context::getInstance()->getSessionHandler()->getTranslatorFor('en');
        try {
            $callback($em, $dao);
            $dao->persistQueue($translator);
        }
        catch (Throwable $e) {
            $this->handleError($em, $e);
        }
        finally {
            $this->handleFinally($em);
        }        
    }

    /**
     * Try and rollback all changes to the database.
     * @param ScheduledEvent $event
     * @param EntityManager $em
     * @param Throwable $e
     */
    private function handleError(EntityManager $em, Throwable $e) {
        \error_log("Error occured while processing event: $e");
        try {
            if ($em->isOpen()) {
                $em->rollback();
            }
        }
        catch (Throwable $e) {
            \error_log("Could not rollback em: $e");
        }
    }

    /**
     * Flush and close the EntityManager.
     * @param EntityManager $em
     */
    private function handleFinally(EntityManager $em) {
        try {
            if ($em->isOpen()) {
                $em->flush();
            }
        }
        catch (Throwable $e) {
            \error_log("Could not flush em: $e");
        }
        try {
            if ($em->isOpen()) {
                $em->close();
            }
        }
        catch (Throwable $e) {
            \error_log("Could not close em: $e");
        }
    }
}