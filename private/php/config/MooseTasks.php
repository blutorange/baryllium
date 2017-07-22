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

namespace Moose\Context;

use Moose\Extension\DiningHall\DiningHallLoaderInterface;

/**
 * Description of MooseTasks
 *
 * @author madgaksha
 */
class MooseTasks {
    
    private $halls;
    
    public function __construct(array $tasks) {
        $top = $this->assertTop($tasks);
        $this->halls = $top['halls'] ?? [];
    }
    
    public static function makeFromArray(array $tasks) : MooseTasks {
        return new MooseTasks($tasks);
    }
    
    public function convertToArray() : array {
        $base = [
            'halls' => $this->halls,
        ];
        return $base;
    }

    /**
     * @return string[]|DiningHallLoaderInterface[]
     */
    public function getDiningHalls() : array {
        return \array_keys($this->halls);
    }
    
    public function getIsDiningHallActivated(string $className) : bool {
        return $this->getHall($className)['activated'] ?? false;
    }
    
    public function getDiningHallSchedule(string $className) : float {
        return $this->getHall($className)['schedule'] ?? 60;
    }
    
    private function & getHall(string $key) : array {
        $hall = & $this->halls[$key] ?? null;
        if ($hall === null) {
            $hall = $this->halls[$key] = [
                'activated' => false,
                'schedule' => 60
            ];
        }
        return $hall;
    }
    
    public function setDiningHallIsActivated(string $className, bool $isActivated) : MooseTasks {
        $this->getHall($className)['activated'] = $isActivated;
        return $this;
    }
    
    public function setDiningHallSchedule(string $className, float $schedule) : MooseTasks {
        $this->getHall($className)['schedule'] = $schedule;
        return $this;
    }
    
    private function & assertTop(array & $top) : array {
        if (!isset($top['diningHall'])) {
            $top['diningHall'] = [];
        }
        return $top;
    }
}
